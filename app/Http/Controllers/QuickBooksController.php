<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Jobs\SyncQuickBooksJob;
use App\Models\QuickBooksConnection;
use App\Services\QuickBooksService;
use Illuminate\Http\Request;

class QuickBooksController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'QuickBooks';
    }

    public function index()
    {
        $this->connections = QuickBooksConnection::with('connectedBy', 'syncLogs')
            ->orderByDesc('created_at')
            ->get();

        return view('quickbooks.index', $this->data);
    }

    public function connect()
    {
        abort_403(!auth()->user()->can('connect_quickbooks'));

        $companyId = company()?->id ?? auth()->user()->company_id;
        abort_403(!$companyId);
        session(['qb_company_id' => $companyId, 'qb_user_id' => auth()->id()]);

        return redirect(QuickBooksService::getAuthorizationUrl());
    }

    public function callback(Request $request)
    {
        $request->validate([
            'code'    => 'required|string',
            'realmId' => 'required|string',
        ]);

        $companyId = session('qb_company_id');
        $userId    = session('qb_user_id');
        session()->forget(['qb_company_id', 'qb_user_id']);

        try {
            QuickBooksService::handleCallback(
                code:      $request->code,
                realmId:   $request->realmId,
                companyId: $companyId,
                userId:    $userId,
            );

            return redirect()->route('quickbooks.index')
                ->with('success', 'QuickBooks connected successfully!');
        } catch (\Throwable $e) {
            return redirect()->route('quickbooks.index')
                ->with('error', 'Failed to connect QuickBooks: ' . $e->getMessage());
        }
    }

    public function sync(Request $request, int $connectionId)
    {
        abort_403(!auth()->user()->can('sync_quickbooks'));

        $request->validate(['sync_type' => 'required|in:customers,invoices']);
        $connection = QuickBooksConnection::findOrFail($connectionId);

        SyncQuickBooksJob::dispatch($connection->id, $request->sync_type)
            ->onQueue('quickbooks');

        return Reply::success('Sync job dispatched. Results will appear shortly.');
    }

    public function disconnect(int $connectionId)
    {
        $connection = QuickBooksConnection::findOrFail($connectionId);
        $connection->update(['is_active' => false]);
        return Reply::success('QuickBooks disconnected.');
    }
}
