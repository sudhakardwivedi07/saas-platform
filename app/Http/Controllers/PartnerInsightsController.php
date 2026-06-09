<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessReportInsightsJob;
use App\Models\Company;
use App\Models\CompanyQuickbooksSetting;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Models\ReportAttachment;
use App\Models\User;
use App\Services\QuickBooksCSVExportService;
use App\Services\QuickBooksService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerInsightsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_403(!auth()->user()->hasAnyRole(['admin', 'manager']));
            return $next($request);
        });
        $this->pageTitle = 'Insights';
    }

    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $subCompanyIds = $this->allClientCompanyIds();

        // All reports for those sub-companies (not firm reports)
        $this->reports = Report::withTrashed()
            ->with(['user', 'company'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('is_firm_report', false)
            ->orderByDesc('created_at')
            ->get();

        // Billing-cycle usage: only count original AI-generated reports (not version copies)
        $cycleUsed = Report::whereIn('company_id', $subCompanyIds)
            ->where('is_firm_report', false)
            ->whereNull('deleted_at')
            ->whereNull('parent_report_id')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->whereNotIn('status', ['Failed'])
            ->count();

        // Package limit from the admin company's subscription plan
        $adminPlan    = $this->company?->clientPackage ?? $this->company?->package ?? $this->company?->activePlan();
        $monthlyLimit = (int) ($adminPlan?->insights_per_month ?? $adminPlan?->max_insights_per_month ?? 0);

        // Stats
        $this->totalInsights     = $monthlyLimit;
        $this->insightsUsed      = $cycleUsed;
        $this->insightsRemaining = max(0, $monthlyLimit - $cycleUsed);
        $this->avgPerClient      = $subCompanyIds->count() > 0
            ? round($cycleUsed / $subCompanyIds->count(), 1)
            : 0;
        $this->limitReached      = $monthlyLimit > 0 && $cycleUsed >= $monthlyLimit;

        return view('partner.insights.index', $this->data);
    }

    // ── AJAX: clients with at least one QB-connected company ─────────────────

    public function getClients()
    {
        $allClientIds = $this->allClientCompanyIds();

        // Partner's own company IDs — users who belong here are admins/managers, not clients
        $partnerCompanyId = $this->partnerCompanyId();
        $partnerCompanyIds = collect([$partnerCompanyId])
            ->merge(
                auth()->user()->managedCompanies()
                    ->withoutGlobalScopes()
                    ->pluck('companies.id')
            )
            ->unique()->filter()->values()->all();

        $qbConnectedCompanyIds = QuickBooksConnection::withoutGlobalScopes()
            ->whereIn('company_id', $allClientIds)
            ->where('is_active', true)
            ->where('access_token_expires_at', '>', now()->addMinutes(10))
            ->pluck('company_id')
            ->unique();

        // Direct users of QB-connected companies, excluding super admins
        $directUsers = User::withoutGlobalScopes()
            ->whereIn('company_id', $qbConnectedCompanyIds)
            ->where('is_superadmin', false)
            ->select('id', 'name', 'email', 'company_id')
            ->get();

        // Users linked via user_companies, excluding super admins and partner's own users
        $linkedUsers = User::withoutGlobalScopes()
            ->whereIn('id', function ($q) use ($qbConnectedCompanyIds) {
                $q->select('user_id')
                  ->from('user_companies')
                  ->whereIn('company_id', $qbConnectedCompanyIds);
            })
            ->where('is_superadmin', false)
            ->whereNotIn('company_id', $partnerCompanyIds)
            ->select('id', 'name', 'email', 'company_id')
            ->get();

        $clients = $directUsers->merge($linkedUsers)
            ->unique('email')
            ->map(fn($u) => [
                'id'    => $u->id,
                'name'  => $u->name,
                'email' => $u->email,
            ])
            ->values();

        return response()->json(['status' => 'success', 'clients' => $clients]);
    }

    // ── AJAX: QB-connected companies for a specific client ───────────────────

    public function getCompanies(int $clientId)
    {
        $clientUser   = User::findOrFail($clientId);
        $allClientIds = $this->allClientCompanyIds();

        // Collect all user IDs that represent the same person (same email)
        $samePersonUserIds = User::withoutGlobalScopes()
            ->where('email', $clientUser->email)
            ->pluck('id');

        // Their company IDs: via company_id column + via user_companies links
        $samePersonCompanyIds = User::withoutGlobalScopes()
            ->where('email', $clientUser->email)
            ->pluck('company_id')
            ->merge(
                DB::table('user_companies')
                    ->whereIn('user_id', $samePersonUserIds)
                    ->pluck('company_id')
            )
            ->unique();

        // Restrict to companies within the partner's managed scope
        $inScope = $samePersonCompanyIds->intersect($allClientIds);

        $qbConnectedIds = QuickBooksConnection::withoutGlobalScopes()
            ->whereIn('company_id', $inScope)
            ->where('is_active', true)
            ->where('access_token_expires_at', '>', now()->addMinutes(10))
            ->pluck('company_id')
            ->unique();

        $companies = Company::withoutGlobalScopes()
            ->whereIn('id', $qbConnectedIds)
            ->select('id', 'company_name')
            ->orderBy('company_name')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->company_name]);

        return response()->json(['status' => 'success', 'companies' => $companies]);
    }

    // ── Generate ──────────────────────────────────────────────────────────────

    public function generate(Request $request)
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
        ]);

        $companyId = (int) $request->company_id;

        if (!$this->allClientCompanyIds()->contains($companyId)) {
            return response()->json(['status' => 'error', 'message' => 'Access denied.'], 403);
        }

        // Enforce the partner's monthly insight limit (from their subscription plan)
        $adminPlan    = $this->company?->clientPackage ?? $this->company?->package;
        $monthlyLimit = $adminPlan ? (int) ($adminPlan->insights_per_month ?? $adminPlan->max_insights_per_month ?? 0) : 0;

        if ($monthlyLimit > 0) {
            $subCompanyIds = $this->allClientCompanyIds();
            $cycleUsed = Report::whereIn('company_id', $subCompanyIds)
                ->where('is_firm_report', false)
                ->whereNull('deleted_at')
                ->whereNull('parent_report_id')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->whereNotIn('status', ['Failed'])
                ->count();

            if ($cycleUsed >= $monthlyLimit) {
                return response()->json([
                    'status'        => 'error',
                    'limit_reached' => true,
                    'message'       => "You have reached your monthly insight limit ({$monthlyLimit} insights/month). Please upgrade your plan or wait until next month.",
                ], 422);
            }
        }

        // Derive the primary user for this company (used as report owner)
        $clientUser = User::withoutGlobalScopes()->where('company_id', $companyId)->first()
            ?? User::withoutGlobalScopes()
                ->whereIn('id', function ($q) use ($companyId) {
                    $q->select('user_id')->from('user_companies')->where('company_id', $companyId);
                })
                ->first();

        if (!$clientUser) {
            return response()->json(['status' => 'error', 'message' => 'No user found for this company.'], 422);
        }

        // withoutGlobalScopes() required — QuickBooksConnection has TenantScope
        $connection = QuickBooksConnection::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->first();

        if (!$connection) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No active QuickBooks connection for this company.',
            ], 422);
        }

        $credentials = $this->resolveCredentials($companyId);
        if (!$credentials) {
            return response()->json([
                'status'  => 'error',
                'message' => 'QuickBooks credentials are not configured for this company.',
            ], 422);
        }

        // Only one insight per company at a time
        $processing = Report::where('company_id', $companyId)
            ->where('user_id', $clientUser->id)
            ->whereIn('status', ['Uploading', 'Analyzing'])
            ->exists();

        if ($processing) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insight generation is already in progress for this company.',
            ], 422);
        }

        $company        = Company::withoutGlobalScopes()->find($companyId);
        $companyLabel   = trim($company?->company_name ?? '');
        $analysisNumber = Report::withTrashed()
            ->where('company_id', $companyId)
            ->where('user_id', $clientUser->id)
            ->count() + 1;

        $generatedName = $companyLabel
            ? $companyLabel . ' (Analysis ' . $analysisNumber . ')'
            : 'Analysis ' . $analysisNumber;

        $endDate   = $request->get('end_date',   Carbon::now()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subMonths(11)->startOfMonth()->format('Y-m-d'));

        try {
            (new QuickBooksService($connection, $credentials))->refreshTokenIfNeeded();
            $connection->refresh();

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
                    'user_id'        => $clientUser->id,
                    'company_id'     => $companyId,
                    'is_firm_report' => false,
                    'publish_status' => 'draft',
                    'report_name'    => $generatedName,
                    'upload_date'    => now()->toDateString(),
                    'total_file_size'=> 0,
                    'status'         => 'Uploading',
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
                $industry = $clientUser->business_context ?? 'General Business';

                set_time_limit(1200);
                ini_set('max_execution_time', 1200);
                ini_set('memory_limit', '512M');

                ProcessReportInsightsJob::dispatch($report->id, $apiFiles, $style, $industry, keepFiles: true)->onConnection('sync');

                // Re-refresh token after the sync job so the connection stays "Connected"
                // (the job can take 10–30 min; the access token may expire during that time)
                try {
                    $connection->refresh();
                    (new QuickBooksService($connection, $credentials))->refreshTokenIfNeeded();
                } catch (\Exception $e) {
                    Log::warning('PartnerInsights: post-generate token refresh failed: ' . $e->getMessage());
                }

                return response()->json([
                    'status'       => 'processing',
                    'message'      => 'QuickBooks data synced. Insights are being generated.',
                    'report_id'    => $report->id,
                    'report_name'  => $report->report_name,
                    'client_name'  => $clientUser->name,
                    'company_name' => $company->company_name,
                    'user_id'      => $clientUser->id,
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('PartnerInsightsController::generate error: ' . $e->getMessage(), [
                'partner_company_id' => $partnerCompanyId,
                'client_id'          => $clientUser->id,
                'company_id'         => $companyId,
                'trace'              => $e->getTraceAsString(),
            ]);

            $needsReconnect = str_contains($e->getMessage(), 'reconnect')
                           || str_contains($e->getMessage(), 'Re-authentication')
                           || str_contains($e->getMessage(), 'invalid_grant')
                           || str_contains($e->getMessage(), 'Refresh OAuth 2');

            return response()->json([
                'status'  => 'error',
                'message' => $needsReconnect
                    ? 'QuickBooks authorization expired for this company. Ask the client to reconnect.'
                    : 'Failed to generate insights: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ── Status polling ────────────────────────────────────────────────────────

    public function checkStatus(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
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

    // ── View ──────────────────────────────────────────────────────────────────

    public function view(string $encryptedId)
    {
        $reportId = Report::decryptId($encryptedId);
        if (!$reportId) abort(404);

        $subCompanyIds = $this->allClientCompanyIds();

        $this->report = Report::with(['attachments', 'user', 'company'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        $this->pageTitle = 'Insight Report';
        return view('partner.insights.detail', $this->data);
    }

    // ── Get data (AJAX) ───────────────────────────────────────────────────────

    public function getData(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->first();

        if (!$report) {
            return response()->json(['status' => 'error', 'message' => 'Report not found'], 404);
        }

        if (empty($report->insight_json_data)) {
            return response()->json(['status' => 'error', 'message' => 'No insight data available yet.'], 404);
        }

        return response()->json([
            'status'         => 'success',
            'data'           => $report->insight_json_data,
            'publish_status' => $report->publish_status ?? 'draft',
        ]);
    }

    // ── Update content ────────────────────────────────────────────────────────

    public function updateContent(Request $request, int $reportId)
    {
        $request->validate([
            'sections'           => 'required|array',
            'sections.*.title'   => 'required|string',
            'sections.*.html'    => 'present|string',
        ]);

        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        $sections = $request->sections;

        // Build plain-text version for PDF export
        $reportText = '';
        foreach ($sections as $section) {
            $reportText .= ($section['title'] ?? '') . "\n"
                         . $this->htmlToPlainText($section['html'] ?? '') . "\n\n";
        }

        $jsonData             = is_array($report->insight_json_data) ? $report->insight_json_data : [];
        $jsonData['sections'] = $sections;
        $jsonData['report']   = trim($reportText);

        // Published → create a new published record so all versions are preserved in the list
        if ($report->isPublished()) {
            $next = $this->spawnVersion($report, $jsonData, 'published');

            return response()->json([
                'status'       => 'success',
                'message'      => 'Saved as new version.',
                'redirect_url' => route('partner-insights.view', $next->encrypted_id),
                'version'      => $next->version,
            ]);
        }

        // Draft → update in place
        $report->insight_json_data = $jsonData;
        $report->save();

        return response()->json(['status' => 'success', 'message' => 'Insight updated.']);
    }

    private function htmlToPlainText(string $html): string
    {
        // Preserve list items as bullet points
        $html = preg_replace('/<li[^>]*>\s*(.*?)\s*<\/li>/si', '- $1' . "\n", $html);
        // Preserve headings
        $html = preg_replace('/<h3[^>]*>(.*?)<\/h3>/si', '### $1' . "\n", $html);
        $html = preg_replace('/<h4[^>]*>(.*?)<\/h4>/si', '#### $1' . "\n", $html);
        // Paragraphs
        $html = preg_replace('/<p[^>]*>(.*?)<\/p>/si', '$1' . "\n\n", $html);
        // Bold / italic
        $html = preg_replace('/<strong>(.*?)<\/strong>/si', '**$1**', $html);
        $html = preg_replace('/<em>(.*?)<\/em>/si',         '*$1*',   $html);
        // Line breaks
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        // Strip remaining tags
        $html = strip_tags($html);
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($html);
    }

    // ── Publish ───────────────────────────────────────────────────────────────

    public function publish(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        // Create a new published record so v1 Draft stays in the list alongside v2 Published
        $next = $this->spawnVersion($report, $report->insight_json_data, 'published');

        return response()->json([
            'status'       => 'success',
            'message'      => 'Insight published.',
            'redirect_url' => route('partner-insights.view', $next->encrypted_id),
            'version'      => $next->version,
        ]);
    }

    // ── Preview PDF (HTML preview page) ──────────────────────────────────────

    public function previewPdf(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::with(['user', 'company'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        if (empty($report->insight_json_data)) {
            abort(404, 'No insight data available.');
        }

        $sections = $this->getReportSections($report);

        $this->pageTitle = 'Preview — ' . ($report->report_name ?: 'Insight');
        $this->report    = $report;
        $this->sections  = $sections;

        return view('partner.insights.preview', $this->data);
    }

    // ── Export PDF ────────────────────────────────────────────────────────────

    public function exportPdf(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::with(['user', 'company'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        if (empty($report->insight_json_data)) {
            abort(404, 'No insight data available.');
        }

        $sections = $this->getReportSections($report);

        if (empty($sections)) {
            abort(404, 'No report content found.');
        }

        // Convert logo to base64 so DOMPDF can embed it without HTTP requests
        $logoBase64 = '';
        $branding   = $this->branding;
        if ($branding?->logo) {
            $logoPath = storage_path('app/public/' . $branding->logo);
            if (file_exists($logoPath)) {
                $mime       = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('enable_php', true);
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->setOption('defaultFont', 'Arial');
        $pdf->setOption('fontHeightRatio', 1.1);
        $pdf->setOption('show_warnings', false);

        $pdf->loadView('partner.insights.pdf', [
            'sections'    => $sections,
            'report'      => $report,
            'companyName' => $this->companyName,
            'logoBase64'  => $logoBase64,
            'reportDate'  => $report->created_at->format('M d, Y'),
        ]);

        $timestamp = $report->created_at->format('mdyHis');
        $base      = preg_replace('/[^A-Za-z0-9\-_ ]/', '', $report->report_name ?: 'Insight') . '_' . $timestamp;

        return $pdf->download($base . '.pdf');
    }

    // ── Shared section-extraction helper ─────────────────────────────────────

    private function getReportSections(Report $report): array
    {
        $insightData = $report->insight_json_data;

        // Prefer structured HTML sections saved by the inline editor
        if (is_array($insightData) && !empty($insightData['sections'])) {
            return array_map(fn ($s) => [
                'title'             => $s['title']   ?? '',
                'formatted_content' => $s['html']    ?? $s['content'] ?? '',
            ], $insightData['sections']);
        }

        // Fall back to parsing the raw report text
        $reportText = '';
        if (is_string($insightData)) {
            $reportText = $insightData;
        } elseif (is_array($insightData)) {
            if (isset($insightData['report'])) {
                $reportText = $insightData['report'];
            } else {
                $longest = '';
                foreach ($insightData as $value) {
                    if (is_string($value) && strlen($value) > strlen($longest)) $longest = $value;
                }
                $reportText = $longest;
            }
        }

        if (empty($reportText)) return [];

        $sections = $this->parseInsightSectionsForPdf($reportText);
        foreach ($sections as &$section) {
            $section['formatted_content'] = $this->formatContentForPdf($section['content']);
        }
        return $sections;
    }

    // ── Download CSV ZIP ──────────────────────────────────────────────────────

    public function download(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::with('attachments')
            ->whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        if ($report->attachments->isEmpty()) {
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

        foreach ($report->attachments as $attachment) {
            $filePath = Storage::disk('public')->path('reports/' . $attachment->attachment);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $attachment->original_filename ?? $attachment->attachment);
            }
        }
        $zip->close();

        return response()->download($tempZip, $zipName)->deleteFileAfterSend(true);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        $report->delete();

        return response()->json(['status' => 'success', 'message' => 'Insight deleted.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function partnerCompanyId(): int
    {
        return (int) (company()?->id ?? auth()->user()->company_id);
    }

    private function resolveCredentials(int $companyId): ?array
    {
        $setting = CompanyQuickbooksSetting::where('company_id', $companyId)->first();

        if (!$setting) {
            $createdBy = Company::withoutGlobalScopes()
                ->where('id', $companyId)
                ->value('created_by');

            if ($createdBy) {
                $setting = CompanyQuickbooksSetting::where('company_id', $createdBy)->first();
            }
        }

        // Final fallback: use the partner/admin company credentials directly.
        // Needed when created_by is a user ID (client-created sub-companies).
        if (!$setting) {
            $setting = CompanyQuickbooksSetting::where('company_id', $this->partnerCompanyId())->first();
        }

        if (!$setting) return null;

        return [
            'client_id'     => $setting->client_id,
            'client_secret' => $setting->client_secret,
            'redirect_uri'  => $setting->redirect_uri,
            'base_url'      => $setting->base_url,
        ];
    }

    /**
     * Returns all client company IDs the partner can manage.
     * Mirrors the same 4-step resolution used in QuickBooksController:
     * admin companies → direct client companies → client users → user_companies links.
     */
    private function allClientCompanyIds(): \Illuminate\Support\Collection
    {
        $partnerCompanyId = $this->partnerCompanyId();

        $adminCompanyIds = collect([$partnerCompanyId])
            ->merge(
                auth()->user()->managedCompanies()
                    ->withoutGlobalScopes()
                    ->pluck('companies.id')
            )
            ->unique()->filter()->values()->all();

        $directClientIds = Company::withoutGlobalScopes()
            ->whereIn('created_by', $adminCompanyIds)
            ->pluck('id');

        $clientUserIds = User::withoutGlobalScopes()
            ->whereIn('company_id', $directClientIds)
            ->pluck('id');

        $linkedCompanyIds = DB::table('user_companies')
            ->whereIn('user_id', $clientUserIds)
            ->pluck('company_id');

        return $directClientIds->merge($linkedCompanyIds)->unique()->filter()->values();
    }

    // ── Version helper ────────────────────────────────────────────────────────

    private function spawnVersion(Report $source, mixed $jsonData, string $publishStatus): Report
    {
        return Report::create([
            'user_id'          => $source->user_id,
            'company_id'       => $source->company_id,
            'is_firm_report'   => $source->is_firm_report,
            'report_name'      => $source->report_name,
            'upload_date'      => $source->upload_date ?? now()->toDateString(),
            'total_file_size'  => $source->total_file_size,
            'status'           => 'Analyzed',
            'publish_status'   => $publishStatus,
            'insight_json_data'=> $jsonData,
            'version'          => ($source->version ?? 1) + 1,
            'parent_report_id' => $source->parent_report_id ?? $source->id,
        ]);
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
                $start = $m[0][$i][1] + strlen($m[0][$i][0]);
                $end   = isset($m[0][$i + 1]) ? $m[0][$i + 1][1] : strlen($text);
                $sections[] = ['title' => $match[0], 'content' => trim(substr($text, $start, $end - $start))];
            }
            if (!empty($sections)) return $sections;
        }

        $parts = preg_split('/\n\s*[\x{2550}\x{2500}=]{10,}\s*\n/u', $text);
        if (count($parts) > 1) {
            $map        = [];
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
                if (!empty($map[$canon])) $sections[] = ['title' => $canon, 'content' => $map[$canon]];
            }
            if (!empty($sections)) return $sections;
        }

        return [['title' => 'Financial Insights', 'content' => $text]];
    }

    private function expandPartByCanonical(string $partTitle, string $partContent, array $normToCanon): array
    {
        $result  = [];
        $lines   = explode("\n", $partContent);
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
