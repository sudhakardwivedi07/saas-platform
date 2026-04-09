<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\QuickBooksConnection;

class DashboardController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Dashboard';
    }

    public function index()
    {
        $company = company();

        // SuperAdmin has no tenant context — send them to the SuperAdmin panel
        if (!$company) {
            return redirect()->route('superadmin.dashboard');
        }

        $this->subscription  = $company->subscription;
        $this->documentCount = Document::count();
        $this->qbConnections = QuickBooksConnection::active()->get();
        $this->recentDocs    = Document::with('uploader')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboard.index', $this->data);
    }
}
