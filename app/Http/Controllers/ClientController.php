<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        abort_403(!auth()->user()->hasAnyRole(['client_admin', 'manager']));

        $companyId = company()?->id ?? auth()->user()->company_id;

        // Companies created by this client admin
        $this->companies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with([
                'clientPackage',
                'package',
                'quickbooksConnections',
                'users' => fn($q) => $q->orderBy('id')->limit(1),
            ])
            ->orderByDesc('created_at')
            ->get();

        // All unique clients (first user of each sub-company) for the filter dropdown
        $this->clientsList = $this->companies
            ->map(fn($c) => $c->users->first())
            ->filter()
            ->unique('id')
            ->values();

        $subCompanyIds = $this->companies->pluck('id');

        // Client packages for assign plan modal
        $this->clientPackages = \App\Models\ClientPackage::forCompany($companyId)
            ->where('is_active', true)->orderBy('sort')->get();

        // Clients = users whose company_id is one of those sub-companies
        $this->clients = User::whereIn('company_id', $subCompanyIds)
            ->with(['company.quickbooksConnections', 'company.clientPackage', 'company.package'])
            ->orderByDesc('created_at')
            ->get();

        return view('clients.index', $this->data);
    }

    public function show($id)
    {
        $this->client = User::with(['company.quickbooksConnections'])->findOrFail($id);
        $this->pageTitle = $this->client->name;
        return view('clients.show', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!auth()->user()->can('create_users'));

        $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'required|email|max:255|unique:users,email',
            'phone'             => 'nullable|string|max:30',
            'company_name'      => 'required|string|max:255',
            'industry_type'     => 'nullable|string|max:100',
            'client_package_id' => 'nullable|exists:client_packages,id',
            'password'          => 'required|string|min:8',
        ]);

        $parentCompanyId = company()->id;

        $subCompany = Company::create([
            'created_by'        => $parentCompanyId,
            'company_name'      => $request->company_name,
            'company_email'     => $request->email,
            'status'            => 'active',
            'industry_type'     => $request->industry_type,
            'client_package_id' => $request->client_package_id ?: null,
        ]);

        $user = User::create([
            'company_id' => $subCompany->id,
            'name'       => $request->name,
            'email'      => $request->email,
            'mobile'     => $request->phone,
            'password'   => Hash::make($request->password),
        ]);

        $user->assignRole('user');

        return redirect()->route('clients.index')
            ->with('success', 'Client "' . $user->name . '" added successfully.');
    }

    public function bulkUpload(\Illuminate\Http\Request $request)
    {
        abort_403(!auth()->user()->hasAnyRole(['client_admin', 'manager']));

        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $parentCompanyId = company()->id;
        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        // Skip header row
        $header = fgetcsv($handle);

        $created = 0;
        $skipped = 0;
        $errors  = [];

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) continue;

            [$clientName, $email] = array_map('trim', $row);
            $planTier = trim($row[2] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $skipped++;
                continue;
            }

            $pkg = $planTier
                ? \App\Models\ClientPackage::forCompany($parentCompanyId)
                    ->where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [strtolower($planTier)])
                    ->first()
                : null;

            $subCompany = Company::create([
                'created_by'        => $parentCompanyId,
                'company_name'      => $clientName ?: ($email . "'s Company"),
                'company_email'     => $email,
                'status'            => 'active',
                'client_package_id' => $pkg?->id,
            ]);

            $user = User::create([
                'company_id' => $subCompany->id,
                'name'       => $clientName ?: $email,
                'email'      => $email,
                'password'   => Hash::make(\Illuminate\Support\Str::random(12)),
            ]);
            $user->assignRole('user');
            $created++;
        }

        fclose($handle);

        return response()->json([
            'status'  => 'success',
            'message' => "$created client(s) imported" . ($skipped ? ", $skipped skipped." : '.'),
        ]);
    }

    public function update($id)
    {
        return back()->with('info', 'Client update coming soon.');
    }

    public function destroy($id)
    {
        return back()->with('success', 'Client removed.');
    }

    public function toggleStatus($id)
    {
        abort_403(!auth()->user()->hasAnyRole(['client_admin', 'manager']));
        $client = User::findOrFail($id);
        $client->status = $client->status === 'active' ? 'inactive' : 'active';
        $client->save();
        return response()->json([
            'status'  => 'success',
            'message' => 'Client ' . ($client->status === 'active' ? 'enabled' : 'disabled') . '.',
            'new_status' => $client->status,
        ]);
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
