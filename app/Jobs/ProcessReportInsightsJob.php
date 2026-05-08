<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\QuickBooksConnection;
use App\Models\Report;
use App\Models\SuperAdmin\Subscription;
use App\Services\EmailService;
use App\Services\Kpis\QuickBooksKpiDataService;
use App\Services\KpiService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessReportInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 900;
    public array $backoff = [30, 60, 120];

    private const MAX_CONCURRENT_API_CALLS = 3;
    private const MAX_RELEASES             = 5;

    public function __construct(
        public int    $reportId,
        public array  $uploadedFiles,
        public string $style     = '',
        public string $industry  = '',
        public bool   $keepFiles = false
    ) {
        $this->afterCommit = true;
        if (empty($this->style)) {
            $this->style = config('dashboard_graph_settings.default_style', 'plain');
        }
    }

    public function handle(): void
    {
        // Rate-limit: max MAX_CONCURRENT_API_CALLS concurrent API calls
        $acquired    = false;
        $maxWaitTime = 300;
        $waitInterval = 10;
        $totalWaited  = 0;

        // Safety check: reset stale counter (stuck for > 30 min)
        $counterLastReset = Cache::get('analyze_api_counter_timestamp');
        $now              = time();

        if ($counterLastReset && ($now - $counterLastReset) > 1800) {
            $currentCount = (int) Cache::get('analyze_api_current_count', 0);
            if ($currentCount >= self::MAX_CONCURRENT_API_CALLS) {
                Log::warning('ProcessReportInsightsJob: API counter appears stuck, resetting', [
                    'report_id'           => $this->reportId,
                    'current_count'       => $currentCount,
                    'counter_age_seconds' => $now - $counterLastReset,
                ]);
                Cache::put('analyze_api_current_count', 0);
                Cache::put('analyze_api_counter_timestamp', $now);
            }
        }

        if (!$counterLastReset) {
            Cache::put('analyze_api_counter_timestamp', $now);
        }

        Log::info('ProcessReportInsightsJob: Checking for available API slot', [
            'report_id'      => $this->reportId,
            'max_concurrent' => self::MAX_CONCURRENT_API_CALLS,
        ]);

        while (!$acquired && $totalWaited < $maxWaitTime) {
            $currentCount = (int) Cache::get('analyze_api_current_count', 0);

            if ($currentCount < self::MAX_CONCURRENT_API_CALLS) {
                $newCount = Cache::increment('analyze_api_current_count');
                if ($newCount <= self::MAX_CONCURRENT_API_CALLS) {
                    $acquired = true;
                    Cache::put('analyze_api_counter_timestamp', time());
                    Log::info('ProcessReportInsightsJob: Acquired API slot', [
                        'report_id'      => $this->reportId,
                        'slot_number'    => $newCount,
                        'waited_seconds' => $totalWaited,
                    ]);
                } else {
                    Cache::decrement('analyze_api_current_count');
                }
            }

            if (!$acquired) {
                Log::info('ProcessReportInsightsJob: Waiting for API slot', [
                    'report_id'      => $this->reportId,
                    'current_count'  => $currentCount,
                    'waited_seconds' => $totalWaited,
                    'max_wait'       => $maxWaitTime,
                ]);
                sleep($waitInterval);
                $totalWaited += $waitInterval;
            }
        }

        if (!$acquired) {
            $releaseKey   = 'report_release_count_' . $this->reportId;
            $releaseCount = (int) Cache::get($releaseKey, 0) + 1;
            Cache::put($releaseKey, $releaseCount, 3600);

            Log::warning('ProcessReportInsightsJob: Could not acquire API slot, releasing job back to queue', [
                'report_id'     => $this->reportId,
                'total_waited'  => $totalWaited,
                'release_count' => $releaseCount,
                'max_releases'  => self::MAX_RELEASES,
            ]);

            if ($releaseCount >= self::MAX_RELEASES) {
                Log::error('ProcessReportInsightsJob: Max releases exceeded, marking report as failed', [
                    'report_id'     => $this->reportId,
                    'release_count' => $releaseCount,
                ]);
                Report::find($this->reportId)?->update(['status' => 'Failed']);
                Cache::forget($releaseKey);
                return;
            }

            $this->release(60);
            return;
        }

        Cache::forget('report_release_count_' . $this->reportId);

        $processingSucceeded = false;
        $report              = null;
        $user                = null;

        try {
            $this->processReport();
            $processingSucceeded = true;

            $report = Report::find($this->reportId);
            $user   = $report?->user()->first();

        } catch (\Exception $e) {
            Log::error('ProcessReportInsightsJob: processing failed', [
                'report_id' => $this->reportId,
                'error'     => $e->getMessage(),
            ]);

            Report::find($this->reportId)?->update(['status' => 'Failed']);
            throw $e;

        } finally {
            $remaining = Cache::decrement('analyze_api_current_count');
            if ($remaining < 0) Cache::put('analyze_api_current_count', 0);
            Cache::put('analyze_api_counter_timestamp', time());

            Log::info('ProcessReportInsightsJob: Released API slot', [
                'report_id'           => $this->reportId,
                'remaining_slots_used' => max(0, $remaining),
            ]);
        }

        // Send "Insight Ready" email on success
        if ($processingSucceeded && $user && $report) {
            try {
                $companyId    = $report->company_id ?? $user->company_id;
                $insightsLeft = $this->resolveInsightsLeft($user->company_id, $companyId);

                app(EmailService::class)->sendToUser($user, 'insight-ready', [
                    '{{first_name}}'    => strtok($user->name, ' '),
                    '{{insight_url}}'   => env('APP_URL') . ($report->is_firm_report ? '/firm-insights' : '/insights'),
                    '{{insights_left}}' => $insightsLeft !== null ? (string) $insightsLeft : 'Unlimited',
                ]);

                Log::info('ProcessReportInsightsJob: Insight Ready email sent', [
                    'user_id'       => $user->id,
                    'report_id'     => $report->id,
                    'insights_left' => $insightsLeft,
                ]);
            } catch (\Throwable $e) {
                Log::error('ProcessReportInsightsJob: Insight Ready email failed', [
                    'user_id'   => $user->id,
                    'report_id' => $report->id,
                    'error'     => $e->getMessage(),
                ]);
            }

            $this->sendFirstFirmInsightEmail($report, $user);
        }
    }

    private function sendFirstFirmInsightEmail(Report $report, $user): void
    {
        if (!$user || !$report->is_firm_report) return;

        // Only fire for reports on the partner's own firm (company_id matches user's company)
        $ownCompanyId = $user->company_id;
        $reportCompanyId = $report->company_id ?? $ownCompanyId;

        if ($reportCompanyId !== $ownCompanyId) return;

        try {
            $totalFirmAnalyzed = Report::withTrashed()
                ->where('is_firm_report', true)
                ->where('company_id', $ownCompanyId)
                ->where('status', 'Analyzed')
                ->count();

            if ($totalFirmAnalyzed !== 1) return;

            $company   = Company::withoutGlobalScopes()->find($ownCompanyId);
            $firmEmail = $company?->company_email ?? $user->email;

            app(EmailService::class)->send(
                toEmail:      $firmEmail,
                templateSlug: 'first_insight_generated_partners_own_firm',
                placeholders: [
                    '{{name}}'          => $user->name,
                    '{{first_name}}'    => strtok($user->name, ' '),
                    '{{firm_name}}'     => $company?->company_name ?? '',
                    '{{app_name}}'      => $company?->app_name ?? config('app.name'),
                    '{{report_name}}'   => $report->report_name ?? 'Your First Insight',
                    '{{insight_url}}'   => env('APP_URL') . '/firm-insights',
                    '{{portal_url}}'    => url('/login'),
                    '{{login_url}}'     => url('/login'),
                    '{{support_email}}' => $company?->company_email ?? config('mail.from.address'),
                ],
                companyId: $ownCompanyId
            );

            Log::info('ProcessReportInsightsJob: First firm insight email sent', [
                'user_id'    => $user->id,
                'company_id' => $ownCompanyId,
                'report_id'  => $report->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessReportInsightsJob: First firm insight email failed', [
                'user_id'   => $user->id,
                'report_id' => $report->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    private function processReport(): void
    {
        set_time_limit(1200);
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit', '512M');

        // Atomically grab the report — skip if another process already claimed it
        $grabbed = DB::table('reports')
            ->where('id', $this->reportId)
            ->where('status', 'Uploading')
            ->update(['status' => 'Analyzing']);

        if (!$grabbed) {
            $existing = Report::find($this->reportId);
            if ($existing && $existing->status !== 'Uploading') {
                Log::info('ProcessReportInsightsJob: report already claimed, skipping', [
                    'report_id' => $this->reportId,
                    'status'    => $existing->status,
                ]);
                return;
            }
        }

        $report = Report::find($this->reportId);
        if (!$report) {
            Log::error("ProcessReportInsightsJob: report {$this->reportId} not found");
            return;
        }

        Log::info('ProcessReportInsightsJob started', [
            'report_id'   => $report->id,
            'files_count' => count($this->uploadedFiles),
            'file_keys'   => array_keys($this->uploadedFiles),
        ]);

        try {
            $user            = $report->user()->first();
            $businessContext = $user?->business_context ?? '';
            $industry        = !empty($businessContext) ? $businessContext : $this->industry;

            if (empty($industry)) {
                $industry = 'General Business';
                Log::warning('No business_context found, using default industry', [
                    'user_id'         => $user?->id,
                    'report_id'       => $report->id,
                    'passed_industry' => $this->industry,
                ]);
            }

            $multipart = [];

            foreach ($this->uploadedFiles as $apiFieldName => $fileInfo) {
                $path = $fileInfo['path'] ?? null;

                if (!$path) {
                    Log::error('File path is null for API', [
                        'report_id'  => $report->id,
                        'field_name' => $apiFieldName,
                        'file_info'  => $fileInfo,
                    ]);
                    continue;
                }

                if (!file_exists($path)) {
                    Log::error('File not found for API', [
                        'report_id'   => $report->id,
                        'field_name'  => $apiFieldName,
                        'path'        => $path,
                        'file_exists' => false,
                    ]);
                    continue;
                }

                if (!is_readable($path)) {
                    Log::error('File is not readable for API', [
                        'report_id'   => $report->id,
                        'field_name'  => $apiFieldName,
                        'path'        => $path,
                        'permissions' => substr(sprintf('%o', fileperms($path)), -4),
                    ]);
                    continue;
                }

                try {
                    $fileHandle = fopen($path, 'r');
                    if ($fileHandle === false) {
                        throw new \Exception('Failed to open file handle');
                    }

                    $multipart[] = [
                        'name'     => $apiFieldName,
                        'contents' => $fileHandle,
                        'filename' => $fileInfo['name'] ?? basename($path),
                    ];

                    Log::info('File added to multipart', [
                        'report_id'  => $report->id,
                        'field_name' => $apiFieldName,
                        'filename'   => $fileInfo['name'] ?? basename($path),
                        'file_size'  => filesize($path),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to open file for API', [
                        'report_id'  => $report->id,
                        'field_name' => $apiFieldName,
                        'path'       => $path,
                        'error'      => $e->getMessage(),
                    ]);
                }
            }

            if (empty($multipart)) {
                Log::error('No files to send to API', ['report_id' => $report->id]);
                $report->update(['status' => 'Failed']);
                return;
            }

            $multipart[] = ['name' => 'style',    'contents' => $this->style];
            $multipart[] = ['name' => 'industry', 'contents' => $industry];

            Log::info('Multipart payload prepared', [
                'report_id'            => $report->id,
                'total_fields'         => count($multipart),
                'file_fields'          => count($multipart) - 2,
                'style'                => $this->style,
                'industry'             => $industry,
                'business_context_used' => !empty($businessContext),
            ]);

            $client = new Client([
                'timeout'         => 900,
                'connect_timeout' => 60,
                'verify'          => false,
                'http_errors'     => false,
                'curl'            => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_TCP_KEEPALIVE  => 1,
                    CURLOPT_TCP_KEEPIDLE   => 300,
                    CURLOPT_TCP_KEEPINTVL  => 300,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
                ],
            ]);

            $apiUrl        = config('dashboard_graph_settings.analyze_url');
            $resp          = null;
            $lastException = null;
            $maxRetries    = 3;
            $retryDelay    = 5;

            try {
                for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                    try {
                        Log::info('Starting API call to analyze endpoint', [
                            'report_id'                => $report->id,
                            'url'                      => $apiUrl,
                            'timeout_seconds'          => 900,
                            'connect_timeout_seconds'  => 60,
                            'attempt'                  => $attempt,
                            'max_retries'              => $maxRetries,
                        ]);

                        $startTime = microtime(true);
                        $resp      = $client->post($apiUrl, ['multipart' => $multipart]);
                        $duration  = round(microtime(true) - $startTime, 2);

                        Log::info('API call completed successfully', [
                            'report_id'        => $report->id,
                            'duration_seconds' => $duration,
                            'attempt'          => $attempt,
                        ]);
                        break;

                    } catch (ConnectException $e) {
                        $lastException = $e;
                        Log::warning('Connection error on attempt ' . $attempt, [
                            'report_id'  => $report->id,
                            'error'      => $e->getMessage(),
                            'attempt'    => $attempt,
                            'will_retry' => $attempt < $maxRetries,
                        ]);

                        if ($attempt < $maxRetries) {
                            Log::info('Retrying API call after delay', [
                                'report_id'    => $report->id,
                                'delay_seconds' => $retryDelay,
                                'next_attempt' => $attempt + 1,
                            ]);
                            sleep($retryDelay);
                            $retryDelay *= 2;
                        }
                    } catch (RequestException $e) {
                        throw $e;
                    }
                }

                if (!$resp && $lastException) {
                    throw $lastException;
                }

                $httpCode     = $resp->getStatusCode();
                $responseBody = (string) $resp->getBody();

                Log::info('AI API response', [
                    'report_id' => $report->id,
                    'code'      => $httpCode,
                    'body_head' => substr($responseBody, 0, 1000),
                ]);

            } catch (RequestException | ConnectException $e) {
                $httpCode     = $e->getResponse()?->getStatusCode() ?? 0;
                $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();

                $isTimeout         = str_contains($e->getMessage(), 'timed out')
                                  || str_contains($e->getMessage(), 'Operation timed out')
                                  || str_contains($e->getMessage(), 'cURL error 28');

                $isConnectionError = str_contains($e->getMessage(), 'Failed to connect')
                                  || str_contains($e->getMessage(), "Couldn't connect to server")
                                  || $e instanceof ConnectException;

                Log::error('Guzzle request failed', [
                    'report_id'           => $report->id,
                    'code'                => $httpCode,
                    'error'               => $e->getMessage(),
                    'body'                => $responseBody,
                    'is_timeout'          => $isTimeout,
                    'is_connection_error' => $isConnectionError,
                ]);

                if ($isConnectionError) {
                    Log::error('API connection failed - unable to reach server', [
                        'report_id'  => $report->id,
                        'suggestion' => 'Check network connectivity, firewall settings, or DNS resolution.',
                    ]);
                } elseif ($isTimeout) {
                    Log::error('API request timed out - analysis is taking longer than expected', [
                        'report_id'  => $report->id,
                        'suggestion' => 'Consider increasing timeout or using a dedicated queue worker.',
                    ]);
                }

                $report->update(['status' => 'Failed']);
                return;
            }

            if ($httpCode !== 200) {
                Log::error('AI API non-200', [
                    'report_id' => $report->id,
                    'code'      => $httpCode,
                    'body'      => $responseBody,
                ]);
                $report->update(['status' => 'Failed']);
                return;
            }

            $responseData = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON from AI API', [
                    'report_id'  => $report->id,
                    'json_error' => json_last_error_msg(),
                    'body_head'  => substr($responseBody, 0, 500),
                ]);
                $report->update(['status' => 'Failed']);
                return;
            }

            if (!empty($responseData['error'])) {
                Log::error('API returned an error in response', [
                    'report_id' => $report->id,
                    'error'     => $responseData['error'],
                    'keys'      => implode(', ', array_keys($responseData)),
                ]);
                $report->update(['status' => 'Failed']);
                return;
            }

            if (isset($responseData['success']) && $responseData['success'] === false) {
                Log::error('API returned success=false', [
                    'report_id' => $report->id,
                    'error'     => $responseData['error'] ?? 'Unknown error',
                    'keys'      => implode(', ', array_keys($responseData)),
                ]);
                $report->update(['status' => 'Failed']);
                return;
            }

            $isSuccess = isset($responseData['success']) && $responseData['success'] === true;

            Log::info('API response structure received', [
                'report_id'           => $report->id,
                'keys'                => implode(', ', array_keys($responseData)),
                'has_success'         => isset($responseData['success']),
                'success_value'       => $responseData['success'] ?? null,
                'has_report'          => isset($responseData['report']),
                'has_financial_summary' => isset($responseData['financial_summary']),
            ]);

            $hasReportData = isset($responseData['report'])
                          || isset($responseData['financial_summary'])
                          || isset($responseData['metrics'])
                          || $isSuccess;

            if (!$hasReportData) {
                Log::warning('API response may be missing expected data fields', [
                    'report_id' => $report->id,
                    'keys'      => implode(', ', array_keys($responseData)),
                    'note'      => 'Response will be saved but may not contain expected report data',
                ]);
            }

            $report->update([
                'status'            => 'Analyzed',
                'insight_json_data' => $responseData,
            ]);

            // Increment insights_used after successful analysis (covers both user and firm reports)
            $companyId = $report->company_id ?? $user?->company_id;
            if ($companyId) {
                Company::withoutGlobalScopes()->where('id', $companyId)->increment('insights_used');
                $this->invalidateKpiCaches((int) $companyId, (int) $report->user_id);
            }

            Log::info('Report analyzed and saved', [
                'report_id'     => $report->id,
                'response_keys' => implode(', ', array_keys($responseData)),
            ]);

            $this->sendUpgradeEmailIfEligible($report, $user);
            $this->checkAndDeactivateSubscription($user, $report->company_id ?? $user?->company_id);
            $this->cleanupTempFiles();

        } catch (\Throwable $e) {
            Log::error('ProcessReportInsightsJob unexpected error', [
                'report_id' => $report->id,
                'error'     => $e->getMessage(),
            ]);
            $report->update(['status' => 'Failed']);
            $this->cleanupTempFiles();
        }
    }

    private function sendUpgradeEmailIfEligible(Report $report, $user): void
    {
        if (!$user) return;

        try {
            $totalAnalyzed = Report::withTrashed()
                ->where('user_id', $user->id)
                ->where('status', 'Analyzed')
                ->count();

            $qbConnected = QuickBooksConnection::where('user_id', $user->id)
                ->where('is_active', 1)
                ->exists();

            $hasActiveSubscription = Subscription::where('company_id', $user->company_id)
                ->whereIn('status', ['active', 'trialing'])
                ->exists();

            Log::info('Upgrade email eligibility check', [
                'user_id'                => $user->id,
                'total_analyzed'         => $totalAnalyzed,
                'qb_connected'           => $qbConnected,
                'has_active_subscription' => $hasActiveSubscription,
            ]);

            if ($qbConnected && $totalAnalyzed === 1 && !$hasActiveSubscription) {
                app(EmailService::class)->sendToUser($user, 'upgrade-prompt', [
                    '{{first_name}}'  => strtok($user->name, ' '),
                    '{{upgrade_url}}' => env('APP_URL') . '/billing',
                ]);
                Log::info('Upgrade prompt email sent', ['user_id' => $user->id]);
            }
        } catch (\Throwable $e) {
            Log::error('Upgrade prompt email failed', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }

    private function checkAndDeactivateSubscription($user, ?int $companyId): void
    {
        if (!$user || !$companyId) return;

        try {
            $company = Company::withoutGlobalScopes()->find($companyId);
            $subscription = Subscription::where('company_id', $user->company_id)
                ->where('status', 'active')
                ->first();

            if (!$company || !$subscription) return;

            $plan         = $subscription->clientPackage ?? $subscription->package;
            $monthlyLimit = $plan?->max_insights_per_month ?? $plan?->insights_per_month ?? null;

            Log::info('Checking subscription limit after analysis', [
                'subscription_id' => $subscription->id,
                'insights_used'   => $company->insights_used,
                'limit'           => $monthlyLimit,
            ]);

            if ($monthlyLimit && $company->insights_used >= (int) $monthlyLimit) {
                DB::table('subscriptions')
                    ->where('id', $subscription->id)
                    ->update(['status' => 'limit_reached']);

                Log::warning('Subscription set to limit_reached', [
                    'subscription_id' => $subscription->id,
                    'insights_used'   => $company->insights_used,
                    'limit'           => $monthlyLimit,
                ]);

                $this->sendQuotaExhaustedEmail($user, $company, (int) $company->insights_used, (int) $monthlyLimit);
            }
        } catch (\Throwable $e) {
            Log::error('checkAndDeactivateSubscription error', ['error' => $e->getMessage()]);
        }
    }

    private function sendQuotaExhaustedEmail($user, Company $company, int $quotaUsed, int $quotaTotal): void
    {
        try {
            $firmEmail      = $company->company_email ?? $user->email;
            $quotaResetDate = now()->startOfMonth()->addMonth()->format('M d, Y');

            app(EmailService::class)->send(
                toEmail:      $firmEmail,
                templateSlug: 'insight_quota_exhausted_partners_own_firm',
                placeholders: [
                    '{{name}}'              => $user->name,
                    '{{first_name}}'        => strtok($user->name, ' '),
                    '{{firm_name}}'         => $company->company_name,
                    '{{app_name}}'          => $company->app_name ?? config('app.name'),
                    '{{quota_used}}'        => $quotaUsed,
                    '{{Quota_Used}}'        => $quotaUsed,
                    '{{quota_total}}'       => $quotaTotal,
                    '{{Quota_Total}}'       => $quotaTotal,
                    '{{quota_reset_date}}'  => $quotaResetDate,
                    '{{upgrade_url}}'       => url('/billing'),
                    '{{billing_url}}'       => url('/billing'),
                    '{{portal_url}}'        => url('/login'),
                    '{{login_url}}'         => url('/login'),
                    '{{support_email}}'     => $company->company_email ?? config('mail.from.address'),
                ],
                companyId: $company->id
            );

            Log::info('ProcessReportInsightsJob: Quota exhausted email sent', [
                'user_id'    => $user->id,
                'company_id' => $company->id,
                'used'       => $quotaUsed,
                'total'      => $quotaTotal,
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessReportInsightsJob: Quota exhausted email failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }

    private function resolveInsightsLeft(int $userCompanyId, int $reportCompanyId): ?int
    {
        try {
            $subscription = Subscription::where('company_id', $userCompanyId)
                ->whereIn('status', ['active', 'trialing'])
                ->first();

            if (!$subscription) return null;

            $plan         = $subscription->clientPackage ?? $subscription->package;
            $monthlyLimit = $plan?->max_insights_per_month ?? $plan?->insights_per_month ?? null;

            if (!$monthlyLimit) return null;

            $company = Company::withoutGlobalScopes()->find($reportCompanyId);
            return max(0, (int) $monthlyLimit - (int) ($company?->insights_used ?? 0));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function cleanupTempFiles(): void
    {
        if ($this->keepFiles) return;

        foreach ($this->uploadedFiles as $fileInfo) {
            $path = $fileInfo['path'] ?? null;
            if ($path && file_exists($path)) {
                @unlink($path);
            }
        }
    }

    private function invalidateKpiCaches(int $companyId, int $reportUserId): void
    {
        try {
            KpiService::clearKpiCache($companyId);
            QuickBooksKpiDataService::forgetDatasetCache($companyId, $reportUserId);
            Log::info('ProcessReportInsightsJob: KPI caches invalidated', [
                'company_id' => $companyId,
                'user_id'    => $reportUserId,
            ]);
        } catch (\Throwable $e) {
            Log::warning('ProcessReportInsightsJob: cache invalidation failed', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessReportInsightsJob permanently failed', [
            'report_id' => $this->reportId,
            'error'     => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);

        try {
            $report = Report::find($this->reportId);
            if ($report && strtolower($report->status) !== 'analyzed') {
                $report->update(['status' => 'Failed']);
                Log::info('Report status updated to Failed after job failure', ['report_id' => $this->reportId]);
            }
        } catch (\Exception $e) {
            Log::error('Could not update report status after job failure', [
                'report_id' => $this->reportId,
                'error'     => $e->getMessage(),
            ]);
        }

        $currentCount = (int) Cache::get('analyze_api_current_count', 0);
        if ($currentCount > 0) {
            Cache::decrement('analyze_api_current_count');
        }
    }
}
