<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessReportInsightsJob;
use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Services\QuickBooksCSVExportService;
use App\Services\QuickBooksService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InsightsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Insights';
    }

    public function index()
    {
        $user      = auth()->user();
        $companyId = $user->company_id;

        $this->qbConnection = QuickBooksConnection::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        $this->qbConfigured = $this->resolveCredentials($companyId) !== null;

        $this->reports = Report::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('insights.index', $this->data);
    }

    public function generate(Request $request)
    {
        $user      = auth()->user();
        $companyId = $user->company_id;

        $connection = QuickBooksConnection::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if (!$connection) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No active QuickBooks connection. Please connect QuickBooks first.',
            ], 422);
        }

        $credentials = $this->resolveCredentials($companyId);
        if (!$credentials) {
            return response()->json([
                'status'  => 'error',
                'message' => 'QuickBooks credentials are not configured. Please contact your administrator.',
            ], 422);
        }

        // Check for an already-processing report
        $processing = Report::where('user_id', $user->id)
            ->whereIn('status', ['Uploading', 'Analyzing'])
            ->exists();

        if ($processing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insight generation is already in progress. Please wait for it to complete.',
            ], 422);
        }

        $endDate   = $request->get('end_date',   Carbon::now()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subMonths(11)->startOfMonth()->format('Y-m-d'));

        try {
            // Refresh QB token if needed
            (new QuickBooksService($connection, $credentials))->refreshTokenIfNeeded();
            $connection->refresh();

            // Generate CSV files from QB
            $csvFiles = QuickBooksCSVExportService::generateAllCSVFiles(
                $connection, $credentials, $startDate, $endDate
            );

            if (empty($csvFiles)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to generate data files from QuickBooks.',
                ], 500);
            }

            DB::beginTransaction();
            try {
                $report = Report::create([
                    'user_id'         => $user->id,
                    'report_name'     => 'QuickBooks Insight — ' . Carbon::now()->format('M j, Y g:i A'),
                    'upload_date'     => now()->toDateString(),
                    'total_file_size' => 0,
                    'status'          => 'Uploading',
                ]);

                $totalSize = 0;
                $apiFiles  = [];

                foreach ($csvFiles as $fieldName => $fileInfo) {
                    if (empty($fileInfo['path']) || !file_exists($fileInfo['path'])) continue;

                    $storedName  = time() . '_' . Str::random(10) . '.csv';
                    $storagePath = 'reports/' . $storedName;
                    $content     = file_get_contents($fileInfo['path']);

                    Storage::disk('public')->put($storagePath, $content);

                    $fileSize = strlen($content);
                    ReportAttachment::create([
                        'report_id'         => $report->id,
                        'attachment'        => $storedName,
                        'file_size'         => $fileSize,
                        'file_type'         => 'csv',
                        'original_filename' => $fileInfo['name'],
                    ]);

                    $totalSize           += $fileSize;
                    $apiFiles[$fieldName] = ['path' => $fileInfo['path'], 'name' => $fileInfo['name']];
                }

                $report->update(['total_file_size' => $totalSize]);
                DB::commit();

                $style    = config('dashboard_graph_settings.default_style', 'plain');
                $industry = $user->business_context ?? 'General Business';

                ProcessReportInsightsJob::dispatch($report->id, $apiFiles, $style, $industry)
                    ->onConnection('sync');

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Insight generation complete!',
                    'report_id' => $report->id,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                QuickBooksCSVExportService::cleanupExportFiles($csvFiles);
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('InsightsController::generate error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to generate insights: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkStatus(int $reportId)
    {
        $report = Report::where('id', $reportId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$report) {
            return response()->json(['status' => 'error', 'message' => 'Report not found'], 404);
        }

        return response()->json([
            'status' => 'success',
            'report' => [
                'id'           => $report->id,
                'encrypted_id' => $report->encrypted_id,
                'status'       => $report->status,
                'status_label' => $report->status_label,
                'has_insights' => $report->isReady(),
                'is_failed'    => strtolower($report->status) === 'failed',
            ],
        ]);
    }

    public function view(string $encryptedId)
    {
        $reportId = Report::decryptId($encryptedId);

        if (!$reportId) {
            abort(404);
        }

        $this->report = Report::with('attachments')
            ->where('id', $reportId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->pageTitle = 'Insights';
        return view('insights.detail', $this->data);
    }

    public function getData(int $reportId)
    {
        $report = Report::where('id', $reportId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$report) {
            return response()->json(['status' => 'error', 'message' => 'Report not found'], 404);
        }

        if (empty($report->insight_json_data)) {
            return response()->json(['status' => 'error', 'message' => 'No insight data available yet.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $report->insight_json_data,
        ]);
    }

    public function download(int $reportId)
    {
        $report = Report::with('attachments')
            ->where('id', $reportId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $attachments = $report->attachments;

        if ($attachments->isEmpty()) {
            abort(404, 'No files attached to this report.');
        }

        $zipName = Str::slug($report->report_name) . '.zip';
        $tempZip = storage_path('app/temp/' . time() . '_' . Str::random(8) . '.zip');

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        foreach ($attachments as $attachment) {
            $filePath = Storage::disk('public')->path('reports/' . $attachment->attachment);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $attachment->original_filename ?? $attachment->attachment);
            }
        }
        $zip->close();

        return response()->download($tempZip, $zipName)->deleteFileAfterSend(true);
    }

    public function exportPdf(int $reportId)
    {
        $report = Report::with('user.company')
            ->where('id', $reportId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if (empty($report->insight_json_data)) {
            abort(404, 'No insight data available.');
        }

        // Company name
        $companyName = trim($report->user?->company?->company_name ?? '');

        // Analysis number (position of this report among all reports for this user)
        $allIds = Report::withTrashed()
            ->where('user_id', $report->user_id)
            ->orderBy('created_at')
            ->pluck('id')
            ->toArray();
        $analysisNumber = (array_search($report->id, $allIds) !== false)
            ? array_search($report->id, $allIds) + 1
            : 1;

        // Full title for cover page
        $fullTitle = $companyName
            ? $companyName . ' — CFO Insights (Analysis ' . $analysisNumber . ')'
            : 'CFO Insights (Analysis ' . $analysisNumber . ')';

        // Extract report text
        $insightData = $report->insight_json_data;
        $reportText  = '';
        if (is_string($insightData)) {
            $reportText = $insightData;
        } elseif (is_array($insightData)) {
            if (isset($insightData['report'])) {
                $reportText = $insightData['report'];
            } elseif (isset($insightData['insight_json_data']['report'])) {
                $reportText = $insightData['insight_json_data']['report'];
            } else {
                $longest = '';
                foreach ($insightData as $value) {
                    if (is_string($value) && strlen($value) > strlen($longest)) $longest = $value;
                }
                $reportText = $longest;
            }
        }

        if (empty($reportText)) {
            abort(404, 'No report content found.');
        }

        $sections = $this->parseInsightSectionsForPdf($reportText);
        foreach ($sections as &$section) {
            $section['formatted_content'] = $this->formatContentForPdf($section['content']);
        }

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'Arial');
        $pdf->setOption('fontHeightRatio', 1.1);
        $pdf->setOption('show_warnings', false);

        $pdf->loadView('insights.pdf', [
            'sections'   => $sections,
            'fullTitle'  => $fullTitle,
            'reportDate' => $report->created_at->format('M d, Y'),
        ]);

        // Filename: "CompanyName - CFO Insights (Analysis N)_MMDDYYHHMMSS.pdf"
        $timestamp = $report->created_at->format('mdyHis');
        $base      = $companyName
            ? $companyName . ' - CFO Insights (Analysis ' . $analysisNumber . ')_' . $timestamp
            : 'CFO Insights (Analysis ' . $analysisNumber . ')_' . $timestamp;

        return $pdf->download($base . '.pdf');
    }

    public function destroy(int $reportId)
    {
        $report = Report::where('id', $reportId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $report->delete();

        return response()->json(['status' => 'success', 'message' => 'Insight deleted.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function resolveCredentials(int $companyId): ?array
    {
        $setting = CompanyQuickbooksSetting::where('company_id', $companyId)->first();

        if (!$setting) {
            $parentId = Company::withoutGlobalScopes()
                ->where('id', $companyId)
                ->value('created_by');

            if ($parentId) {
                $setting = CompanyQuickbooksSetting::where('company_id', $parentId)->first();
            }
        }

        if (!$setting) return null;

        return [
            'client_id'     => $setting->client_id,
            'client_secret' => $setting->client_secret,
            'redirect_uri'  => $setting->redirect_uri,
            'base_url'      => $setting->base_url,
        ];
    }

    // ── PDF helpers ───────────────────────────────────────────────────────────

    private const CANONICAL_SECTIONS = [
        'Executive Summary', 'Financial Snapshot', 'Key Trends & Drivers',
        'Wins & Opportunities', 'Risks & Concerns', 'Forensic Observations',
        'Actionable Recommendations',
    ];

    private function normalizeTitle(string $t): string
    {
        return strtolower(preg_replace('/\s+/', ' ', trim($t)));
    }

    private function parseInsightSectionsForPdf(string $text): array
    {
        $sections = [];
        $escaped  = array_map(fn($t) => preg_quote($t, '/'), self::CANONICAL_SECTIONS);
        $pattern  = '/(?:^|\n)(' . implode('|', $escaped) . ')\s*\n/';

        if (preg_match_all($pattern, $text, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[1] as $i => $match) {
                $start   = $m[0][$i][1] + strlen($m[0][$i][0]);
                $end     = isset($m[0][$i + 1]) ? $m[0][$i + 1][1] : strlen($text);
                $sections[] = ['title' => $match[0], 'content' => trim(substr($text, $start, $end - $start))];
            }
            if (!empty($sections)) return $sections;
        }

        // Separator lines (═ or =)
        $parts = preg_split('/\n\s*[\x{2550}\x{2500}=]{10,}\s*\n/u', $text);
        if (count($parts) > 1) {
            $map = [];
            $normToCanon = array_combine(array_map([$this, 'normalizeTitle'], self::CANONICAL_SECTIONS), self::CANONICAL_SECTIONS);
            foreach ($parts as $part) {
                $part = trim($part);
                if (!$part) continue;
                $nl    = strpos($part, "\n");
                $title = $nl !== false ? trim(substr($part, 0, $nl)) : $part;
                $body  = $nl !== false ? trim(substr($part, $nl + 1)) : '';
                if (strlen($title) > 2) {
                    foreach ($this->expandPartByCanonical($title, $body, $normToCanon) as $t => $c) {
                        $map[$t] = $c;
                    }
                }
            }
            foreach (self::CANONICAL_SECTIONS as $canon) {
                $key = $this->normalizeTitle($canon);
                if (!empty($map[$canon])) $sections[] = ['title' => $canon, 'content' => $map[$canon]];
                elseif (!empty($map[$key]))  $sections[] = ['title' => $canon, 'content' => $map[$key]];
            }
            if (!empty($sections)) return $sections;
        }

        // Markdown ## headers
        if (preg_match_all('/^##\s+(?:\d+\.\s*)?(.+)$/m', $text, $m, PREG_OFFSET_CAPTURE)) {
            $list = [];
            foreach ($m[1] as $i => $match) {
                $start = $m[0][$i][1] + strlen($m[0][$i][0]);
                $end   = isset($m[0][$i + 1]) ? $m[0][$i + 1][1] : strlen($text);
                $title = preg_replace('/^\d+\.\s*/', '', trim($match[0]));
                $body  = trim(substr($text, $start, $end - $start));
                if (strlen($title) > 2 && $body) $list[] = ['title' => $title, 'content' => $body];
            }
            $byNorm = [];
            foreach ($list as $s) $byNorm[$this->normalizeTitle($s['title'])] = $s;
            foreach (self::CANONICAL_SECTIONS as $canon) {
                $key = $this->normalizeTitle($canon);
                if (isset($byNorm[$key])) $sections[] = $byNorm[$key];
            }
            foreach ($list as $s) {
                if (!in_array($this->normalizeTitle($s['title']), array_map([$this, 'normalizeTitle'], self::CANONICAL_SECTIONS))) {
                    $sections[] = $s;
                }
            }
            if (!empty($sections)) return $sections;
        }

        return [['title' => 'Financial Insights', 'content' => $text]];
    }

    private function expandPartByCanonical(string $partTitle, string $partContent, array $normToCanon): array
    {
        $result = [];
        $lines  = explode("\n", $partContent);
        $current = $partTitle;
        $buffer  = [];
        foreach ($lines as $line) {
            $norm = $this->normalizeTitle(trim($line));
            if (trim($line) !== '' && isset($normToCanon[$norm])) {
                if ($current !== '') $result[$current] = trim(implode("\n", $buffer));
                $current = $normToCanon[$norm];
                $buffer  = [];
            } else {
                $buffer[] = $line;
            }
        }
        if ($current !== '') $result[$current] = trim(implode("\n", $buffer));
        return $result;
    }

    private function formatContentForPdf(string $content): string
    {
        if (empty($content)) return '';

        $content = preg_replace('/^[\s\x{2550}\x{2500}=]{3,}$/um', '', $content);
        $content = preg_replace('/^[\s_*]{3,}$/m', '', $content);
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        $content = trim($content);
        $content = $this->convertMarkdownTableToPdf($content);
        $content = preg_replace('/^###\s+(.+)$/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^####\s+(.+)$/m', '<h4>$1</h4>', $content);
        $content = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $content);
        $content = preg_replace('/(?<!\*)\*([^*]+?)\*(?!\*)/s', '<em>$1</em>', $content);

        $lines = explode("\n", $content);
        $inList = false; $listType = null; $listItems = []; $processed = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (preg_match('/^<(table|thead|tbody|tr|th|td|h[1-6]|ul|ol)/i', $trimmed)) {
                if ($inList && $listItems) {
                    $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>";
                    $listItems = []; $inList = false;
                }
                $processed[] = $line; continue;
            }
            if (preg_match('/^[\-\*\+]\s+(.+)$/', $trimmed, $m)) {
                if ($inList && $listType !== 'ul') { $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>"; $listItems = []; }
                $inList = true; $listType = 'ul'; $listItems[] = "<li>{$m[1]}</li>";
            } elseif (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $m) && !preg_match('/^[A-Z\s:]+$/', $trimmed)) {
                if ($inList && $listType !== 'ol') { $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>"; $listItems = []; }
                $inList = true; $listType = 'ol'; $listItems[] = "<li>{$m[1]}</li>";
            } else {
                if ($inList && $listItems) { $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>"; $listItems = []; $inList = false; }
                $processed[] = $line;
            }
        }
        if ($inList && $listItems) $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>";

        $content = implode("\n", $processed);
        $out = '';
        foreach (preg_split('/\n\n+/', $content) as $para) {
            $para = trim($para);
            if (!$para) continue;
            if (preg_match('/^<(ul|ol|table|h[1-6]|hr)/i', $para)) $out .= $para . "\n";
            else $out .= '<p>' . preg_replace('/\n/', '<br>', $para) . '</p>' . "\n";
        }
        $out = preg_replace('/<p>\s*<\/p>/', '', $out);
        // Strip FA icon tags (render as ? in dompdf)
        $out = preg_replace('/<i\s[^>]*class="[^"]*fa[^"]*"[^>]*>.*?<\/i>/isu', '', $out);
        // Replace math symbols dompdf can't render
        $out = preg_replace('/\x{2264}/u', '&lt;=', $out);
        $out = preg_replace('/\x{2265}/u', '&gt;=', $out);
        return $out;
    }

    private function convertMarkdownTableToPdf(string $text): string
    {
        return preg_replace_callback('/(\|.+\|\n\|[\s\-\|:]+\|\n(?:\|.+\|\n?)+)/', function ($match) {
            $lines = explode("\n", trim($match[0]));
            if (count($lines) < 2) return $match[0];
            $html = '<table><thead><tr>';
            foreach (array_filter(explode('|', $lines[0]), fn($c) => trim($c) !== '') as $cell) {
                $html .= '<th>' . trim($cell) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            for ($i = 2; $i < count($lines); $i++) {
                if (!trim($lines[$i])) continue;
                $cells = array_filter(explode('|', $lines[$i]), fn($c) => trim($c) !== '');
                $html .= '<tr>';
                foreach ($cells as $cell) $html .= '<td>' . trim($cell) . '</td>';
                $html .= '</tr>';
            }
            return $html . '</tbody></table>';
        }, $text);
    }
}
