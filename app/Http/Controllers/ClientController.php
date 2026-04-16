<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;

class ClientController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Clients';
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        // Clients = users that belong to companies managed by this company
        $this->clients = User::whereHas('companies', function ($q) use ($companyId) {
            $q->where('created_by', $companyId);
        })->with(['companies' => function ($q) use ($companyId) {
            $q->where('created_by', $companyId);
        }])->orderByDesc('created_at')->get();

        // Companies created by this company
        $this->companies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['clientPackage', 'package'])
            ->orderByDesc('created_at')
            ->get();

        return view('clients.index', $this->data);
    }

    public function show($id)
    {
        $this->client = User::findOrFail($id);
        $this->pageTitle = $this->client->name;
        return view('clients.show', $this->data);
    }

    public function store()
    {
        return back()->with('info', 'Client creation coming soon.');
    }

    public function update($id)
    {
        return back()->with('info', 'Client update coming soon.');
    }

    public function destroy($id)
    {
        return back()->with('success', 'Client removed.');
    }

    public function insights($id)
    {
        $this->client = User::findOrFail($id);
        $this->pageTitle = 'Insights';
        return view('clients.insights', $this->data);
    }

    public function insightDetail($clientId, $insightId)
    {
        $this->client = User::findOrFail($clientId);
        $this->pageTitle = 'Insight Detail';
        return view('clients.insight-detail', $this->data);
    }
}
