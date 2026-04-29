<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;

class WebhookLogController extends AccountBaseController
{
    private string $logPath;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Webhook Logs';
        $this->logPath   = storage_path('logs/stripe.log');
    }

    public function index(Request $request)
    {
        $filter   = $request->get('filter', 'all');   // all | error | warning | info
        $search   = trim($request->get('search', ''));
        $perPage  = 100;

        $entries = $this->parseLog($filter, $search, $perPage);

        $this->entries       = $entries;
        $this->filter        = $filter;
        $this->search        = $search;
        $this->logExists     = file_exists($this->logPath);
        $this->logSizeKb     = $this->logExists ? round(filesize($this->logPath) / 1024, 1) : 0;
        $this->logPath       = $this->logPath;

        return view('super-admin.webhook-logs.index', $this->data);
    }

    public function clear()
    {
        if (file_exists($this->logPath)) {
            file_put_contents($this->logPath, '');
        }
        return redirect()->route('superadmin.webhook-logs.index')
            ->with('success', 'Stripe log cleared.');
    }

    public function download()
    {
        if (!file_exists($this->logPath)) {
            return redirect()->route('superadmin.webhook-logs.index')
                ->with('error', 'Log file does not exist yet.');
        }
        return response()->download($this->logPath, 'stripe-webhook-' . now()->format('Y-m-d') . '.log');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function parseLog(string $filter, string $search, int $limit): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }

        $raw = file_get_contents($this->logPath);
        if (!$raw) return [];

        // Split into individual log entries (each starts with a [date] prefix)
        $lines   = explode("\n", $raw);
        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+\w+\.(\w+):\s+(.*)$/', $line, $m)) {
                if ($current) {
                    $entries[] = $current;
                }
                $context = '';
                $message = $m[3];
                // Extract JSON context from end of message
                if (preg_match('/^(.*?)(\s+\{.+\}|\s+\[.+\])$/', $message, $cm)) {
                    $message = trim($cm[1]);
                    $context = trim($cm[2]);
                }
                $current = [
                    'datetime' => $m[1],
                    'level'    => strtolower($m[2]),
                    'message'  => $message,
                    'context'  => $context,
                    'raw'      => $line,
                ];
            } elseif ($current && !empty(trim($line))) {
                $current['context'] .= "\n" . $line;
            }
        }
        if ($current) {
            $entries[] = $current;
        }

        // Newest first
        $entries = array_reverse($entries);

        // Filter by level
        if ($filter !== 'all') {
            $entries = array_filter($entries, fn($e) => $e['level'] === $filter);
        }

        // Filter by search
        if ($search !== '') {
            $entries = array_filter($entries, function ($e) use ($search) {
                return stripos($e['message'], $search) !== false
                    || stripos($e['context'], $search) !== false;
            });
        }

        return array_values(array_slice($entries, 0, $limit));
    }
}
