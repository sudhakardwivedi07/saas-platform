<?php

namespace App\Jobs;

use App\Models\Report;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessReportInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 900;
    public array $backoff = [30, 60, 120];

    private const MAX_CONCURRENT = 3;
    private const MAX_RELEASES   = 5;

    public function __construct(
        public int    $reportId,
        public array  $uploadedFiles,
        public string $style    = '',
        public string $industry = ''
    ) {
        $this->afterCommit = true;
        if (empty($this->style)) {
            $this->style = config('dashboard_graph_settings.default_style', 'plain');
        }
    }

    public function handle(): void
    {
        // Rate-limit: max MAX_CONCURRENT concurrent API calls
        $lockKey      = 'analyze_api_slot';
        $acquired     = false;
        $maxWait      = 300;
        $interval     = 10;
        $totalWaited  = 0;

        // Reset stale counter
        $lastReset = Cache::get('analyze_api_counter_timestamp');
        $now       = time();
        if ($lastReset && ($now - $lastReset) > 1800) {
            if ((int) Cache::get('analyze_api_current_count', 0) >= self::MAX_CONCURRENT) {
                Cache::put('analyze_api_current_count', 0);
                Cache::put('analyze_api_counter_timestamp', $now);
            }
        }
        if (!$lastReset) Cache::put('analyze_api_counter_timestamp', $now);

        while (!$acquired && $totalWaited < $maxWait) {
            if ((int) Cache::get('analyze_api_current_count', 0) < self::MAX_CONCURRENT) {
                $new = Cache::increment('analyze_api_current_count');
                if ($new <= self::MAX_CONCURRENT) {
                    $acquired = true;
                    Cache::put('analyze_api_counter_timestamp', time());
                } else {
                    Cache::decrement('analyze_api_current_count');
                }
            }
            if (!$acquired) {
                sleep($interval);
                $totalWaited += $interval;
            }
        }

        if (!$acquired) {
            $releaseKey   = 'report_release_count_' . $this->reportId;
            $releaseCount = (int) Cache::get($releaseKey, 0) + 1;
            Cache::put($releaseKey, $releaseCount, 3600);

            if ($releaseCount >= self::MAX_RELEASES) {
                Log::error('ProcessReportInsightsJob: max releases exceeded', ['report_id' => $this->reportId]);
                Report::find($this->reportId)?->update(['status' => 'Failed']);
                Cache::forget($releaseKey);
                return;
            }

            $this->release(60);
            return;
        }

        Cache::forget('report_release_count_' . $this->reportId);

        try {
            $this->processReport();
        } finally {
            $remaining = Cache::decrement('analyze_api_current_count');
            if ($remaining < 0) Cache::put('analyze_api_current_count', 0);
            Cache::put('analyze_api_counter_timestamp', time());
        }
    }

    private function processReport(): void
    {
        set_time_limit(1200);
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit', '512M');

        $report = Report::find($this->reportId);
        if (!$report) {
            Log::error("ProcessReportInsightsJob: report {$this->reportId} not found");
            return;
        }

        $report->update(['status' => 'Analyzing']);
        Log::info('ProcessReportInsightsJob started', ['report_id' => $report->id, 'files' => array_keys($this->uploadedFiles)]);

        try {
            $multipart = [];

            foreach ($this->uploadedFiles as $fieldName => $fileInfo) {
                $path = $fileInfo['path'] ?? null;
                if (!$path || !file_exists($path) || !is_readable($path)) {
                    Log::error('File not available for API', ['field' => $fieldName, 'path' => $path]);
                    continue;
                }

                $handle = fopen($path, 'r');
                if ($handle === false) continue;

                $multipart[] = [
                    'name'     => $fieldName,
                    'contents' => $handle,
                    'filename' => $fileInfo['name'] ?? basename($path),
                ];
            }

            if (empty($multipart)) {
                Log::error('No files to send to API', ['report_id' => $report->id]);
                $report->update(['status' => 'Failed']);
                return;
            }

            // Use report user's business context as industry if available
            $user     = $report->user()->first();
            $industry = $user?->business_context ?? $this->industry ?: 'General Business';

            $multipart[] = ['name' => 'style',    'contents' => $this->style];
            $multipart[] = ['name' => 'industry', 'contents' => $industry];

            $client = new Client([
                'timeout'         => 900,
                'connect_timeout' => 60,
                'verify'          => false,
                'http_errors'     => false,
                'curl'            => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
                ],
            ]);

            $apiUrl      = config('dashboard_graph_settings.analyze_url');
            $resp        = null;
            $lastEx      = null;
            $retryDelay  = 5;

            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    Log::info('Calling AI analyze API', ['attempt' => $attempt, 'url' => $apiUrl, 'report_id' => $report->id]);
                    $resp = $client->post($apiUrl, ['multipart' => $multipart]);
                    break;
                } catch (ConnectException $e) {
                    $lastEx = $e;
                    if ($attempt < 3) { sleep($retryDelay); $retryDelay *= 2; }
                } catch (RequestException $e) {
                    throw $e;
                }
            }

            if (!$resp && $lastEx) throw $lastEx;

            $httpCode     = $resp->getStatusCode();
            $responseBody = (string) $resp->getBody();

            Log::info('AI API response', ['code' => $httpCode, 'preview' => substr($responseBody, 0, 500)]);

            if ($httpCode !== 200) {
                Log::error('AI API non-200', ['code' => $httpCode, 'report_id' => $report->id]);
                $report->update(['status' => 'Failed']);
                return;
            }

            $responseData = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON from AI API', ['report_id' => $report->id]);
                $report->update(['status' => 'Failed']);
                return;
            }

            // Check for explicit error in response
            if (!empty($responseData['error']) || (isset($responseData['success']) && $responseData['success'] === false)) {
                Log::error('AI API returned error', ['report_id' => $report->id, 'error' => $responseData['error'] ?? 'success=false']);
                $report->update(['status' => 'Failed']);
                return;
            }

            $report->update([
                'status'            => 'Analyzed',
                'insight_json_data' => $responseData,
            ]);

            Log::info('Report analyzed and saved', ['report_id' => $report->id]);

        } catch (\Throwable $e) {
            Log::error('ProcessReportInsightsJob unexpected error', ['report_id' => $report->id, 'error' => $e->getMessage()]);
            $report->update(['status' => 'Failed']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessReportInsightsJob failed permanently', [
            'report_id' => $this->reportId,
            'error'     => $exception->getMessage(),
        ]);
        Report::find($this->reportId)?->update(['status' => 'Failed']);
    }
}
