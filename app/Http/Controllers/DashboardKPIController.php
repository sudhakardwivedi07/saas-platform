<?php

namespace App\Http\Controllers;

use App\Models\ClientDashboardSetting;
use App\Models\Company;
use App\Models\User;
use App\Services\Kpis\KpiFormattedDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DashboardKPIController extends AccountBaseController
{
    private const KPI_TYPES = [
        'runway-burn',
        'working-capital',
        'operating-cashflow',
        'revenue',
        'ebitda',
        'margin-trends',
        'ar-aging',
        'ap-leverage',
        'revenue-concentration',
    ];

    private const KPI_VIEW_MAP = [
        'runway-burn' => 'dashboard.kpis.runway-burn.detail',
        'working-capital' => 'dashboard.kpis.working-capital.detail',
        'operating-cashflow' => 'dashboard.kpis.operating-cashflow.detail',
        'revenue' => 'dashboard.kpis.revenue.detail',
        'ebitda' => 'dashboard.kpis.ebitda.detail',
        'margin-trends' => 'dashboard.kpis.margin-trends.detail',
        'ar-aging' => 'dashboard.kpis.ar-aging.detail',
        'ap-leverage' => 'dashboard.kpis.ap-leverage.detail',
        'revenue-concentration' => 'dashboard.kpis.revenue-concentration.detail',
    ];

    private const KPI_TITLE_MAP = [
        'runway-burn' => 'Runway & Burn',
        'working-capital' => 'Working Capital',
        'operating-cashflow' => 'Operating Cash Flow',
        'revenue' => 'Revenue',
        'ebitda' => 'EBITDA',
        'margin-trends' => 'Margin Trends',
        'ar-aging' => 'AR Aging',
        'ap-leverage' => 'AP Leverage',
        'revenue-concentration' => 'Revenue Concentration',
    ];

    private function resolveKpiCompany(User $user, Request $request): int
    {
        $requested = (int) $request->input('company_id', 0);
        $tenantId = (int) (company()?->id ?? $user->company_id ?? 0);

        if ($requested > 0) {
            if ((int) $tenantId === $requested) {
                return $requested;
            }
            $allowed = $user->portfolioCompanies()->contains(fn ($c) => (int) $c->id === $requested);
            if ($allowed) {
                return $requested;
            }
        }

        return $tenantId;
    }

    private function assertCanAccessCompany(User $user, int $companyId): void
    {
        $tenantId = (int) (company()?->id ?? $user->company_id ?? 0);
        if ($tenantId && $companyId === $tenantId) {
            return;
        }
        if ($user->portfolioCompanies()->contains(fn ($c) => (int) $c->id === $companyId)) {
            return;
        }
        abort(403);
    }

    private function detailView(string $kpiType, string $routeName, Request $request)
    {
        abort_unless(in_array($kpiType, self::KPI_TYPES, true), 404);
        $user = auth()->user();
        abort_unless($user, 403);

        $companyId = $this->resolveKpiCompany($user, $request);
        $this->assertCanAccessCompany($user, $companyId);

        $this->pageTitle = (self::KPI_TITLE_MAP[$kpiType] ?? 'KPI') . ' Details';

        return view(self::KPI_VIEW_MAP[$kpiType], array_merge($this->data, [
            'kpiType' => $kpiType,
            'detailTitle' => self::KPI_TITLE_MAP[$kpiType] ?? 'KPI',
            'kpiPageTitle' => self::KPI_TITLE_MAP[$kpiType] ?? 'KPI',
            'selectedCompanyId' => $companyId,
            'kpiDataUrl' => route('dashboard-kpi.' . $routeName),
            'kpiListBackUrl' => session('impersonating_client_original_id')
                ? route('dashboard')
                : (auth()->user()->hasRole('client_admin')
                    ? route('my-financials.index')
                    : route('dashboard')),
        ]));
    }

    private function dataFor(string $kpiType, Request $request): JsonResponse
    {
        abort_unless(in_array($kpiType, self::KPI_TYPES, true), 404);
        $user = auth()->user();
        abort_unless($user, 403);

        $companyId = $this->resolveKpiCompany($user, $request);
        $this->assertCanAccessCompany($user, $companyId);

        if (! $user->companyHasFinancialKpiSource($companyId)) {
            return response()->json([
                'success' => true,
                'kpis_unavailable' => true,
                'data' => [],
            ]);
        }

        try {
            return response()->json([
                'success' => true,
                'data' => KpiFormattedDataService::get($companyId, $kpiType),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load KPI details',
            ], 500);
        }
    }

    public function runwayBurnDetails(Request $request) { return $this->detailView('runway-burn', 'cash-burn-runway', $request); }
    public function workingCapitalDetails(Request $request) { return $this->detailView('working-capital', 'working-capital', $request); }
    public function operatingCashFlowDetails(Request $request) { return $this->detailView('operating-cashflow', 'operating-cash-flow', $request); }
    public function revenueDetails(Request $request) { return $this->detailView('revenue', 'revenue-trend', $request); }
    public function ebitdaDetails(Request $request) { return $this->detailView('ebitda', 'ebitda', $request); }
    public function marginTrendsDetails(Request $request) { return $this->detailView('margin-trends', 'margin-trends', $request); }
    public function arAgingDetails(Request $request) { return $this->detailView('ar-aging', 'ar-aging', $request); }
    public function apAgingDetails(Request $request) { return $this->detailView('ap-leverage', 'ap-aging', $request); }
    public function revenueConcentrationDetails(Request $request) { return $this->detailView('revenue-concentration', 'revenue-concentration', $request); }

    public function cashBurnRunway(Request $request): JsonResponse { return $this->dataFor('runway-burn', $request); }
    public function workingCapital(Request $request): JsonResponse { return $this->dataFor('working-capital', $request); }
    public function operatingCashFlow(Request $request): JsonResponse { return $this->dataFor('operating-cashflow', $request); }
    public function revenueTrend(Request $request): JsonResponse { return $this->dataFor('revenue', $request); }
    public function ebitda(Request $request): JsonResponse { return $this->dataFor('ebitda', $request); }
    public function marginTrends(Request $request): JsonResponse { return $this->dataFor('margin-trends', $request); }
    public function arAging(Request $request): JsonResponse { return $this->dataFor('ar-aging', $request); }
    public function apAging(Request $request): JsonResponse { return $this->dataFor('ap-leverage', $request); }
    public function revenueConcentration(Request $request): JsonResponse { return $this->dataFor('revenue-concentration', $request); }

    public function getKpiSynopsisFiles(Request $request, int $kpiId = 1): JsonResponse
    {
        try {
            $user = auth()->user();
            abort_unless($user, 403);

            $companyId = $this->resolveKpiCompany($user, $request);
            $this->assertCanAccessCompany($user, $companyId);

            // Get latest analyzed report
            $report = \App\Models\Report::where('user_id', $user->id)
                ->where('company_id', $companyId)
                ->where(function($query) {
                    $query->whereRaw('LOWER(status) = ?', ['analyzed'])
                          ->orWhereRaw('LOWER(status) = ?', ['analysed']);
                })
                ->orderBy('id', 'desc')
                ->first();

            if (!$report) {
                $report = \App\Models\Report::where('user_id', $user->id)
                    ->where('company_id', $companyId)
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reports found'
                ], 404);
            }

            // Get all attachments for the report (simplified since no financial_document_id)
            $attachments = $report->attachments()->get();

            if ($attachments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files found in latest report'
                ], 404);
            }

            // Prepare file paths for API
            $files = [];
            foreach ($attachments as $attachment) {
                $filePath = $attachment->attachment;
                $fullPath = storage_path('app/public/reports/' . $filePath);

                if (file_exists($fullPath)) {
                    $files[] = [
                        'path' => $fullPath,
                        'name' => $attachment->original_filename ?? $filePath,
                        'attachment_id' => $attachment->id
                    ];
                }
            }

            if (empty($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Files not found on server'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'files' => $files,
                'kpi_id' => $kpiId,
                'report_id' => $report->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get files: ' . $e->getMessage()
            ], 500);
        }
    }

    public function callKpiSynopsisApi(Request $request): JsonResponse
    {
        try {
            $kpiId = $request->input('kpi_id', 1);
            $kpiName = $request->input('kpi_name');

            if (!$kpiName) {
                $config = config('dashboard_graph_settings.synopsis_api_kpis');
                $kpiName = $config[$kpiId] ?? null;
            }

            if (!$kpiName) {
                return response()->json([
                    'success' => false,
                    'message' => 'KPI name not found'
                ], 404);
            }

            $user = auth()->user();
            abort_unless($user, 403);

            $companyId = $this->resolveKpiCompany($user, $request);

            // KPI type map (id → slug)
            $kpiTypeMap = [
                1 => 'runway-burn', 2 => 'working-capital', 3 => 'operating-cashflow',
                4 => 'revenue', 5 => 'ebitda', 6 => 'margin-trends',
                7 => 'ar-aging', 8 => 'ap-leverage', 9 => 'revenue-concentration',
            ];
            $kpiType = $kpiTypeMap[(int) $kpiId] ?? null;

            // Return saved override if one exists (skip external AI call)
            if ($kpiType && $companyId) {
                $setting  = ClientDashboardSetting::where('company_id', $companyId)->first();
                $override = $setting?->kpi_synopsis_overrides[$kpiType] ?? null;
                if ($override && (($override['strength'] ?? '') || ($override['shortcoming'] ?? '') || ($override['action_plan'] ?? ''))) {
                    return response()->json(['success' => true, 'data' => array_merge(['success' => true], $override)]);
                }
            }
            $this->assertCanAccessCompany($user, $companyId);

            // For client pages an admin passes client_user_id so we look up that user's reports
            $reportUserId = $user->id;
            $clientUserId = (int) $request->input('client_user_id', 0);
            if ($clientUserId > 0 && $user->hasAnyRole(['admin', 'manager'])) {
                $reportUserId = $clientUserId;
            }

            // Get latest analyzed report
            $report = \App\Models\Report::where('user_id', $reportUserId)
                ->where('company_id', $companyId)
                ->where(function($query) {
                    $query->whereRaw('LOWER(status) = ?', ['analyzed'])
                          ->orWhereRaw('LOWER(status) = ?', ['analysed']);
                })
                ->orderBy('id', 'desc')
                ->first();

            if (!$report) {
                $report = \App\Models\Report::where('user_id', $reportUserId)
                    ->where('company_id', $companyId)
                    ->orderBy('id', 'desc')
                    ->first();
            }

            // When impersonating, the report may have been generated by the admin (not the client).
            // Fall back to any analyzed report for this company regardless of who created it.
            if (!$report && session('impersonating_client_original_id')) {
                $report = \App\Models\Report::where('company_id', $companyId)
                    ->where(function ($query) {
                        $query->whereRaw('LOWER(status) = ?', ['analyzed'])
                              ->orWhereRaw('LOWER(status) = ?', ['analysed']);
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$report) {
                    $report = \App\Models\Report::where('company_id', $companyId)
                        ->orderBy('id', 'desc')
                        ->first();
                }
            }

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'No reports found'
                ], 404);
            }

            // Get all attachments for the report
            $attachments = $report->attachments()->get();

            if ($attachments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files found in latest report'
                ], 404);
            }

            // Prepare files for API
            $files = [];
            foreach ($attachments as $attachment) {
                $filePath = $attachment->attachment;
                $fullPath = storage_path('app/public/reports/' . $filePath);

                if (file_exists($fullPath)) {
                    $files[] = [
                        'path' => $fullPath,
                        'name' => $attachment->original_filename ?? $filePath
                    ];
                }
            }

            if (empty($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Files not found on server'
                ], 404);
            }

            // Get graph data for the KPI
            $graphData = $this->getGraphDataForSynopsis($kpiId, $companyId);

            // Call the API
            $apiUrl = config('dashboard_graph_settings.synopsis_api_url');
            return $this->callSynopsisApiWithCurl($apiUrl, $kpiName, $files, $graphData);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to call API: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getGraphDataForSynopsis(int $kpiId, int $companyId): ?array
    {
        try {
            $kpiType = match($kpiId) {
                1 => 'runway-burn',
                2 => 'working-capital',
                3 => 'operating-cashflow',
                4 => 'revenue',
                5 => 'ebitda',
                6 => 'margin-trends',
                7 => 'ar-aging',
                8 => 'ap-leverage',
                9 => 'revenue-concentration',
                default => null
            };

            if (!$kpiType) return null;

            $data = KpiFormattedDataService::get($companyId, $kpiType);

            // Format data based on KPI type (simplified)
            return match($kpiId) {
                1 => $this->formatRunwayBurnData($data),
                2 => $this->formatWorkingCapitalData($data),
                3 => $this->formatOperatingCashFlowData($data),
                4 => $this->formatRevenueData($data),
                5 => $this->formatEbitdaData($data),
                6 => $this->formatMarginTrendsData($data),
                7 => $this->formatArAgingData($data),
                8 => $this->formatApLeverageData($data),
                9 => $this->formatRevenueConcentrationData($data),
                default => null
            };

        } catch (\Exception $e) {
            \Log::warning('Failed to get graph data for synopsis: ' . $e->getMessage());
            return null;
        }
    }

    private function formatRunwayBurnData(array $data): ?array
    {
        if (!isset($data['monthly_data'])) return null;

        return [
            'data' => array_map(function($item) {
                return [
                    'month' => $item['month'] ?? '',
                    'cash_burn/cash_build' => $item['net_change'] ?? 0
                ];
            }, $data['monthly_data'])
        ];
    }

    private function formatWorkingCapitalData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function formatOperatingCashFlowData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function formatRevenueData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function formatEbitdaData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function formatMarginTrendsData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function formatArAgingData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function formatApLeverageData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function formatRevenueConcentrationData(array $data): ?array
    {
        // Simplified - return basic structure
        return ['data' => []];
    }

    private function callSynopsisApiWithCurl(string $apiUrl, string $kpiName, array $files, ?array $graphData = null): JsonResponse
    {
        try {
            // Validate files exist
            $validFiles = [];
            foreach ($files as $file) {
                if (isset($file['path']) && file_exists($file['path'])) {
                    $validFiles[] = $file;
                }
            }

            if (empty($validFiles)) {
                throw new \Exception('No valid files found to upload');
            }

            $dataArray = $graphData['data'] ?? [];
            $dataJson = json_encode($dataArray);

            // Build multipart array for Guzzle
            $multipart = [
                [
                    'name' => 'kpi_name',
                    'contents' => $kpiName
                ],
                [
                    'name' => 'data',
                    'contents' => $dataJson,
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]
            ];

            // Add each file
            foreach ($validFiles as $file) {
                $filePath = $file['path'];
                $fileName = $file['name'] ?? basename($filePath);

                if (file_exists($filePath)) {
                    $multipart[] = [
                        'name' => 'files',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => $fileName
                    ];
                }
            }

            // Create Guzzle client
            $client = new \GuzzleHttp\Client([
                'timeout' => 120,
                'verify' => false
            ]);

            // Make API call
            $response = $client->post($apiUrl, [
                'multipart' => $multipart,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            // Close file handles
            foreach ($multipart as $part) {
                if (isset($part['contents']) && is_resource($part['contents'])) {
                    fclose($part['contents']);
                }
            }

            $httpCode = $response->getStatusCode();
            $responseBody = (string) $response->getBody();

            if ($httpCode !== 200) {
                throw new \Exception('API returned HTTP ' . $httpCode . ': ' . substr($responseBody, 0, 500));
            }

            $responseData = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API call failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveKpiSynopsis(Request $request): JsonResponse
    {
        $user = auth()->user();
        $canEdit = $user && ($user->hasAnyRole(['admin', 'manager']) || session('impersonating_client_original_id'));
        abort_unless($canEdit, 403);

        $companyId  = (int) $request->input('company_id');
        $kpiType    = $request->input('kpi_type');
        $strength   = $request->input('strength', '');
        $shortcoming = $request->input('shortcoming', '');
        $actionPlan  = $request->input('action_plan', '');

        if (!$companyId || !$kpiType) {
            return response()->json(['success' => false, 'message' => 'Missing required fields'], 422);
        }

        $setting = ClientDashboardSetting::forCompany($companyId);
        $overrides = $setting->kpi_synopsis_overrides ?? [];
        $overrides[$kpiType] = [
            'strength'    => $strength,
            'shortcoming' => $shortcoming,
            'action_plan' => $actionPlan,
        ];
        $setting->update(['kpi_synopsis_overrides' => $overrides]);

        return response()->json(['success' => true, 'message' => 'Synopsis saved successfully']);
    }

    public function getKpiSynopsisOverride(Request $request): JsonResponse
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $companyId = (int) $request->input('company_id');
        $kpiType   = $request->input('kpi_type');

        if (!$companyId || !$kpiType) {
            return response()->json(['success' => false, 'data' => null]);
        }

        $setting = ClientDashboardSetting::where('company_id', $companyId)->first();
        $override = $setting?->kpi_synopsis_overrides[$kpiType] ?? null;

        return response()->json(['success' => true, 'data' => $override]);
    }
}

