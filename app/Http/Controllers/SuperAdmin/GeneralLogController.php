<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;

class GeneralLogController extends AccountBaseController
{
    private string $logPath;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'General Logs';
        $this->logPath   = storage_path('logs/laravel.log');
    }

    public function index(Request $request)
    {
        $filter  = $request->get('filter', 'all');
        $search  = trim($request->get('search', ''));
        $perPage = 100;

        $entries = $this->parseLog($filter, $search, $perPage);

        $this->entries   = $entries;
        $this->filter    = $filter;
        $this->search    = $search;
        $this->logExists = file_exists($this->logPath);
        $this->logSizeKb = $this->logExists ? round(filesize($this->logPath) / 1024, 1) : 0;

        return view('super-admin.general-logs.index', $this->data);
    }

    public function clear()
    {
        if (file_exists($this->logPath)) {
            file_put_contents($this->logPath, '');
        }
        return redirect()->route('superadmin.general-logs.index')
            ->with('success', 'Laravel log cleared.');
    }

    public function download()
    {
        if (!file_exists($this->logPath)) {
            return redirect()->route('superadmin.general-logs.index')
                ->with('error', 'Log file does not exist yet.');
        }
        return response()->download($this->logPath, 'laravel-' . now()->format('Y-m-d') . '.log');
    }

    private function parseLog(string $filter, string $search, int $limit): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }

        $raw = file_get_contents($this->logPath);
        if (!$raw) return [];

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

        $entries = array_reverse($entries);

        if ($filter !== 'all') {
            $entries = array_filter($entries, fn($e) => $e['level'] === $filter);
        }

        if ($search !== '') {
            $entries = array_filter($entries, function ($e) use ($search) {
                return stripos($e['message'], $search) !== false
                    || stripos($e['context'], $search) !== false;
            });
        }

        return array_values(array_slice($entries, 0, $limit));
    }
}
