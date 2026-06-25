<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessReportInsightsJob;
use App\Jobs\SendEmailJob;
use App\Models\Company;
use App\Models\EmailSmtpSetting;
use App\Models\EmailTemplate;
use App\Models\EventLog;
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

        // All reports for those sub-companies (not firm reports) — latest version per insight only
        $this->reports = Report::withTrashed()
            ->with(['user', 'company', 'company.users'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('is_firm_report', false)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn($r) => $r->report_name . '||' . $r->company_id)
            ->map(fn($group) => $group->sortByDesc('version')->first())
            ->values();

        // Billing-cycle usage: all root reports this month (not versions, not failed).
        $cycleUsed = Report::withTrashed()
            ->whereIn('company_id', $subCompanyIds)
            ->where('is_firm_report', false)
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
        $clientCount             = $this->directClientCount();
        $this->avgPerClient      = $clientCount > 0
            ? round($cycleUsed / $clientCount, 2)
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
            ->pluck('company_id')
            ->unique();

        // Direct users of QB-connected companies, excluding super admins
        $directUsers = User::withoutGlobalScopes()
            ->whereIn('company_id', $qbConnectedCompanyIds)
            ->where('is_superadmin', false)
            ->select('id', 'name', 'email', 'company_id', 'created_at')
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
            ->select('id', 'name', 'email', 'company_id', 'created_at')
            ->get();

        $clients = $directUsers->merge($linkedUsers)
            ->unique('email')
            ->sortByDesc('created_at')
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
            ->pluck('company_id')
            ->unique();

        $companies = Company::withoutGlobalScopes()
            ->whereIn('id', $qbConnectedIds)
            ->select('id', 'company_name', 'custom_insight_limit', 'insights_used')
            ->orderBy('company_name')
            ->get()
            ->map(fn($c) => [
                'id'                  => $c->id,
                'name'                => $c->company_name,
                'custom_insight_limit'=> $c->custom_insight_limit,  // null = not set by admin
                'insights_used'       => (int) ($c->insights_used ?? 0),
            ]);

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
            $cycleUsed = Report::withTrashed()
                ->whereIn('company_id', $subCompanyIds)
                ->where('is_firm_report', false)
                ->whereNull('parent_report_id')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->whereNotIn('status', ['Failed'])
                ->get(['report_name', 'company_id'])
                ->unique(fn($r) => $r->report_name . '||' . $r->company_id)
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
            ->whereNull('parent_report_id')
            ->count() + 1;

        $generatedName = $companyLabel
            ? $companyLabel . ' (Analysis ' . $analysisNumber . ')'
            : 'Analysis ' . $analysisNumber;

        $endDate   = $request->get('end_date',   Carbon::now()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subMonths(11)->startOfMonth()->format('Y-m-d'));

        try {
            (new QuickBooksService($connection, $credentials))->refreshTokenIfNeeded();
            $connection->refresh();

            // Multiple sequential QB API calls can exceed PHP's default 30s limit.
            set_time_limit(0);

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

                try {
                    EventLog::record('insight_generated', 'Insight report generated for client', [
                        'report_id'         => $report->id ?? null,
                        'report_name'       => $report->report_name ?? null,
                        'client_company_id' => $report->company_id ?? null,
                    ]);
                } catch (\Throwable $e) {}

                Company::withoutGlobalScopes()->where('id', $companyId)->update(['last_active_at' => now()]);

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

        $this->report = Report::with(['attachments', 'user', 'company', 'company.users'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->firstOrFail();

        $firmId = company()->id;
        $this->canShareInsight =
            EmailTemplate::where('company_id', $firmId)
                         ->where('slug', 'share_insight_with_client')
                         ->where('is_active', true)
                         ->exists()
            &&
            EmailSmtpSetting::where('company_id', $firmId)
                             ->where('is_active', true)
                             ->exists();

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

    // ── Get all versions of an insight series (AJAX) ──────────────────────────

    public function getVersions(int $reportId)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::withTrashed()
            ->whereIn('company_id', $subCompanyIds)
            ->where('id', $reportId)
            ->first();

        if (!$report) {
            return response()->json(['status' => 'error', 'message' => 'Report not found'], 404);
        }

        $rootId = $report->parent_report_id ?? $report->id;

        $versions = Report::withTrashed()
            ->whereIn('company_id', $subCompanyIds)
            ->where(function ($q) use ($rootId) {
                $q->where('id', $rootId)
                  ->orWhere('parent_report_id', $rootId);
            })
            ->orderBy('version')
            ->get(['id', 'version', 'publish_status', 'created_at', 'deleted_at']);

        return response()->json([
            'status'   => 'success',
            'versions' => $versions->map(fn($v) => [
                'id'             => $v->id,
                'encrypted_id'   => $v->encrypted_id,
                'version'        => $v->version ?? 1,
                'publish_status' => $v->publish_status ?? 'draft',
                'created_at'     => $v->created_at->format('M d, Y'),
                'is_deleted'     => !is_null($v->deleted_at),
            ]),
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

            try {
                EventLog::record('insight_saved', 'Insight report draft saved', [
                    'report_id'   => $report->id ?? null,
                    'report_name' => $report->report_name ?? null,
                    'status'      => $report->status ?? null,
                ]);
            } catch (\Throwable $e) {}

            Company::withoutGlobalScopes()->where('id', $report->company_id)->update(['last_active_at' => now()]);

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
        // Always bump updated_at so "Last Updated" reflects this save even when content is unchanged
        $report->touch();

        try {
            EventLog::record('insight_saved', 'Insight report draft saved', [
                'report_id'   => $report->id ?? null,
                'report_name' => $report->report_name ?? null,
                'status'      => $report->status ?? null,
            ]);
        } catch (\Throwable $e) {}

        Company::withoutGlobalScopes()->where('id', $report->company_id)->update(['last_active_at' => now()]);

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

        try {
            EventLog::record('insight_published', 'Insight report published to client', [
                'report_id'         => $report->id ?? null,
                'report_name'       => $report->report_name ?? null,
                'client_company_id' => $report->company_id ?? null,
            ]);
        } catch (\Throwable $e) {}

        Company::withoutGlobalScopes()->where('id', $report->company_id)->update(['last_active_at' => now()]);

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
            $logoPath = Storage::disk('public')->path($branding->logo);
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath);
                if (in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
                    $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                } elseif ($mime === 'image/webp') {
                    if (function_exists('imagecreatefromwebp')) {
                        $img = @imagecreatefromwebp($logoPath);
                        if ($img) {
                            ob_start(); imagepng($img); $pngData = ob_get_clean(); imagedestroy($img);
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($pngData);
                        }
                    } elseif (class_exists('Imagick')) {
                        try {
                            $im = new \Imagick($logoPath); $im->setImageFormat('png');
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($im->getImageBlob());
                        } catch (\Exception $e) {}
                    }
                } elseif ($mime === 'image/svg+xml') {
                    if (class_exists('Imagick')) {
                        try {
                            $im = new \Imagick(); $im->setBackgroundColor('none');
                            $im->readImageBlob(file_get_contents($logoPath));
                            $im->setImageFormat('png');
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($im->getImageBlob());
                        } catch (\Exception $e) {}
                    }
                    if (!$logoBase64) {
                        $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
                    }
                }
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
            'preparedBy'  => user()?->name ?? $this->companyName,
            'logoBase64'  => $logoBase64,
            'reportDate'  => $report->created_at->format('M d, Y'),
            'branding'    => $this->branding,
        ]);

        $base = Str::slug($report->report_name ?: 'Insight');

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
                'formatted_content' => $this->applyPdfAmountAlignment($s['html'] ?? $s['content'] ?? ''),
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

    // ── Export all insights as CSV ────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $subCompanyIds = $this->allClientCompanyIds();

        $search   = trim($request->get('search',    ''));
        $status   = trim($request->get('status',    ''));
        $dateFrom = trim($request->get('date_from', ''));
        $dateTo   = trim($request->get('date_to',   ''));

        $query = Report::withTrashed()
            ->with(['user', 'company'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('is_firm_report', false);

        // Date range filter on created_at
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $reports = $query->orderByDesc('created_at')
            ->get()
            ->groupBy(fn($r) => $r->report_name . '||' . $r->company_id)
            ->map(fn($group) => $group->sortByDesc('version')->first())
            ->values();

        // Status filter — map UI filter value to row status
        if ($status) {
            $reports = $reports->filter(function ($report) use ($status) {
                $isDeleted    = $report->trashed() || strtolower($report->status ?? '') === 'deleted';
                $isProcessing = $report->isProcessing();
                $isFailed     = strtolower($report->status ?? '') === 'failed';
                $isReady      = $report->isReady();
                $publishSt    = $report->publish_status ?? 'draft';

                $rowStatus = $isDeleted    ? 'deleted'
                           : ($isProcessing ? 'processing'
                           : ($isFailed     ? 'failed'
                           : ($isReady      ? $publishSt : 'processing')));

                return $rowStatus === $status;
            })->values();
        }

        // Text search — report name, client name, company name
        if ($search) {
            $lower = strtolower($search);
            $reports = $reports->filter(function ($report) use ($lower) {
                $haystack = strtolower(
                    ($report->report_name ?? '') . ' ' .
                    ($report->user?->name ?? '') . ' ' .
                    ($report->company?->company_name ?? '')
                );
                return str_contains($haystack, $lower);
            })->values();
        }

        $filename = 'insights_' . now()->format('m-d-Y') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($reports) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Excel opens with correct encoding
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Insight Report', 'Generated', 'Client Name', 'Company Name', 'Status']);

            foreach ($reports as $report) {
                $isDeleted    = $report->trashed() || strtolower($report->status ?? '') === 'deleted';
                $isProcessing = $report->isProcessing();
                $isFailed     = strtolower($report->status ?? '') === 'failed';
                $isReady      = $report->isReady();
                $publishSt    = $report->publish_status ?? 'draft';

                if ($isDeleted) {
                    $status = 'Deleted';
                } elseif ($isProcessing) {
                    $status = 'Processing';
                } elseif ($isFailed) {
                    $status = 'Failed';
                } elseif ($isReady) {
                    $status = ucfirst($publishSt);
                } else {
                    $status = 'Processing';
                }

                fputcsv($handle, [
                    $report->report_name ?? '',
                    $report->created_at->format('m/d/Y'),
                    $report->user?->name ?? '—',
                    $report->company?->company_name ?? '—',
                    $status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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

    /**
     * Returns only the direct client company count (companies created by this partner's admin).
     * Used for the "average per client" stat to avoid inflation from user_companies links.
     */
    private function directClientCount(): int
    {
        $partnerCompanyId = $this->partnerCompanyId();
        $adminCompanyIds = collect([$partnerCompanyId])
            ->merge(auth()->user()->managedCompanies()->withoutGlobalScopes()->pluck('companies.id'))
            ->unique()->filter()->values()->all();

        return Company::withoutGlobalScopes()
            ->whereIn('created_by', $adminCompanyIds)
            ->count();
    }

    // ── Version helper ────────────────────────────────────────────────────────

    private function spawnVersion(Report $source, mixed $jsonData, string $publishStatus): Report
    {
        $rootId = $source->parent_report_id ?? $source->id;

        $maxVersion = Report::withTrashed()
            ->where(function ($q) use ($rootId) {
                $q->where('id', $rootId)
                  ->orWhere('parent_report_id', $rootId);
            })
            ->max('version') ?? 1;

        $next = Report::create([
            'user_id'          => $source->user_id,
            'company_id'       => $source->company_id,
            'is_firm_report'   => $source->is_firm_report,
            'report_name'      => $source->report_name,
            'upload_date'      => $source->upload_date ?? now()->toDateString(),
            'total_file_size'  => $source->total_file_size,
            'status'           => 'Analyzed',
            'publish_status'   => $publishStatus,
            'insight_json_data'=> $jsonData,
            'version'          => $maxVersion + 1,
            'parent_report_id' => $rootId,
        ]);

        $rootAttachments = Report::find($rootId)?->attachments ?? collect();
        foreach ($rootAttachments as $attachment) {
            ReportAttachment::create([
                'report_id'         => $next->id,
                'attachment'        => $attachment->attachment,
                'file_size'         => $attachment->file_size,
                'file_type'         => $attachment->file_type,
                'original_filename' => $attachment->original_filename,
            ]);
        }

        return $next;
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
                $key = $this->normalizeTitle($canon);
                if (!empty($map[$canon])) $sections[] = ['title' => $canon, 'content' => $map[$canon]];
                elseif (!empty($map[$key]))  $sections[] = ['title' => $canon, 'content' => $map[$key]];
            }
            if (!empty($sections)) return $sections;
        }

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
        // Break inline bold labels ending with ':' onto their own paragraphs
        $content = preg_replace('/(\S) +(<strong>[A-Za-z][^<]*:<\/strong>)/', "$1\n\n$2", $content);

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
                if (str_ends_with(trim($m[1]), ':')) {
                    if ($inList && $listItems) { $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>"; $listItems = []; $inList = false; }
                    $processed[] = '<p><strong style="color:inherit;">' . $m[1] . '</strong></p>';
                } else {
                    if ($inList && $listType !== 'ul') { $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>"; $listItems = []; }
                    $inList = true; $listType = 'ul'; $listItems[] = "<li>{$m[1]}</li>";
                }
            } elseif (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $m) && !preg_match('/^[A-Z\s:]+$/', $trimmed)) {
                if ($inList && $listType !== 'ol') { $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>"; $listItems = []; }
                $inList = true; $listType = 'ol'; $listItems[] = "<li>{$m[1]}</li>";
            } else {
                if ($inList && $listItems) { $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>"; $listItems = []; $inList = false; }
                $processed[] = $line;
            }
        }
        if ($inList && $listItems) $processed[] = "<{$listType}>" . implode('', $listItems) . "</{$listType}>";

        // Join standalone bold-label lines with their following content line
        for ($i = 0; $i < count($processed) - 1; $i++) {
            $c = trim($processed[$i]); $n = trim($processed[$i + 1]);
            if (preg_match('/^<strong>[^<]+:<\/strong>$/', $c) && $n !== '' && !preg_match('/^<(ul|ol|table|div|h[1-6]|hr)/i', $n)) {
                $processed[$i] = $c . ' ' . $n; array_splice($processed, $i + 1, 1); $i--;
            }
        }
        // Insert paragraph separators before bold-label+content lines
        for ($i = 1; $i < count($processed); $i++) {
            if (preg_match('/^<strong>[^<]+:<\/strong>/', trim($processed[$i])) && $processed[$i - 1] !== '') {
                array_splice($processed, $i, 0, ['']); $i++;
            }
        }
        // Plain text lines immediately before a <ul>/<ol> become subheadings
        for ($i = 0; $i < count($processed) - 1; $i++) {
            $curr = trim($processed[$i]); $next = trim($processed[$i + 1]);
            if ($curr && !preg_match('/^<(ul|ol|table|div|h[1-6]|hr)/i', $curr) && preg_match('/^<(ul|ol)/i', $next)) {
                $processed[$i] = '<div class="insight-subheading">' . $curr . '</div>';
            }
        }

        $content = implode("\n", $processed);
        $out = '';
        foreach (preg_split('/\n\n+/', $content) as $para) {
            $para = trim($para);
            if (!$para) continue;
            if (preg_match('/^<(ul|ol|table|div|h[1-6]|hr)/i', $para)) $out .= $para . "\n";
            elseif (preg_match('/^<strong>[^<]+<\/strong>$/', $para)) $out .= '<p class="is-section-heading">' . $para . '</p>' . "\n";
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
                foreach ($cells as $cell) {
                    $v = trim($cell);
                    $align = preg_match('/^\s*[\$£€]?[\d,]+(\.\d+)?\s*(%|[a-zA-Z]*)\s*$/', $v) ? 'right' : 'left';
                    $html .= '<td style="text-align:' . $align . ';">' . $v . '</td>';
                }
                $html .= '</tr>';
            }
            return $html . '</tbody></table>';
        }, $text);
    }

    private function applyPdfAmountAlignment(string $html): string
    {
        return preg_replace_callback('/<td([^>]*)>(.*?)<\/td>/is', function ($m) {
            $text = trim(strip_tags($m[2]));
            if (preg_match('/^\s*[\$£€]?[\d,]+(\.\d+)?\s*(%|[a-zA-Z]*)\s*$/', $text)) {
                return '<td' . $m[1] . ' style="text-align:right;">' . $m[2] . '</td>';
            }
            return $m[0];
        }, $html);
    }

    // ── Share insight (send PDF to client via email) ──────────────────────────

    public function shareInsight(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $request->validate(['recipient_email' => 'required|email']);

        $subCompanyIds = $this->allClientCompanyIds();

        $report = Report::with(['user', 'company'])
            ->whereIn('company_id', $subCompanyIds)
            ->where('id', $id)
            ->where('publish_status', 'published')
            ->firstOrFail();

        $sections = $this->getReportSections($report);

        if (empty($sections)) {
            return response()->json(['status' => 'error', 'message' => 'No report content found.'], 422);
        }

        $logoBase64 = '';
        if ($this->branding?->logo) {
            $logoPath = Storage::disk('public')->path($this->branding->logo);
            if (file_exists($logoPath)) {
                $mime = mime_content_type($logoPath);
                if (in_array($mime, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])) {
                    $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
                } elseif ($mime === 'image/webp') {
                    if (function_exists('imagecreatefromwebp')) {
                        $img = @imagecreatefromwebp($logoPath);
                        if ($img) {
                            ob_start(); imagepng($img); $pngData = ob_get_clean(); imagedestroy($img);
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($pngData);
                        }
                    } elseif (class_exists('Imagick')) {
                        try {
                            $im = new \Imagick($logoPath); $im->setImageFormat('png');
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($im->getImageBlob());
                        } catch (\Exception $e) {}
                    }
                } elseif ($mime === 'image/svg+xml') {
                    if (class_exists('Imagick')) {
                        try {
                            $im = new \Imagick(); $im->setBackgroundColor('none');
                            $im->readImageBlob(file_get_contents($logoPath));
                            $im->setImageFormat('png');
                            $logoBase64 = 'data:image/png;base64,' . base64_encode($im->getImageBlob());
                        } catch (\Exception $e) {}
                    }
                    if (!$logoBase64) {
                        $logoBase64 = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($logoPath));
                    }
                }
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
            'preparedBy'  => user()?->name ?? $this->companyName,
            'logoBase64'  => $logoBase64,
            'reportDate'  => $report->created_at->format('M d, Y'),
            'branding'    => $this->branding,
        ]);

        $tempDir = storage_path('app/temp_pdfs');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $tempPath = $tempDir . '/' . Str::slug($report->report_name ?: 'insight') . '-' . uniqid() . '.pdf';
        file_put_contents($tempPath, $pdf->output());

        $report->load('company.users');
        $clientUser      = $report->company?->users->first();
        $clientFirstName = $clientUser ? explode(' ', trim($clientUser->name))[0] : ($report->company?->company_name ?? '');

        app(\App\Services\EmailService::class)->sendFromFirm(
            firmId:         auth()->user()->company_id,
            toEmail:        $request->recipient_email,
            templateSlug:   'share_insight_with_client',
            placeholders:   [
                '{{client_name}}'       => $clientFirstName,
                '{{insight_title}}'     => $report->report_name,
                '{{company_name}}'      => $report->company?->company_name ?? '',
                '{{client_first_name}}' => $clientFirstName,
                '{{generated_date}}'    => $report->created_at->format('M d, Y'),
                '{{cfo_name}}'          => auth()->user()->name,
                '{{partner_name}}'      => $this->companyName,
                '{{firm_name}}'         => $this->companyName,
                '{{portal_url}}'        => company()?->domainUrlFor('login') ?? url('/login'),
            ],
            attachmentPath: $tempPath,
        );

        return response()->json(['status' => 'success', 'message' => 'Insight shared successfully.']);
    }
}
