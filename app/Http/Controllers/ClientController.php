<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\SuperAdmin\Package;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClientController extends AccountBaseController
{
    private function enforceMaxCompaniesLimit(int $ownerCompanyId): void
    {
        $ownerCompany = Company::withoutGlobalScopes()->find($ownerCompanyId);
        $plan = $ownerCompany?->activePlan();
        $maxCompanies = $plan?->max_companies;

        if (!$plan) {
            abort(
                422,
                'No plan is assigned to your account. Please select a subscription plan before adding clients or companies.'
            );
        }

        if (!$maxCompanies || (int) $maxCompanies <= 0) {
            return;
        }

        $createdCompaniesCount = Company::withoutGlobalScopes()
            ->whereIn('created_by', array_filter([$ownerCompanyId, auth()->id()]))
            ->count();

        abort_if(
            $createdCompaniesCount >= (int) $maxCompanies,
            422,
            "Company limit reached for your plan ({$maxCompanies} max companies)."
        );
    }

    private function ensureInsightsQuotaAvailable(Company $company): void
    {
        $plan = $company->activePlan();
        $limit = (int) ($plan?->insights_per_month ?? 0);

        if ($limit <= 0) {
            return;
        }

        $used = (int) ($company->insights_used ?? 0);
        abort_if(
            $used >= $limit,
            422,
            "Monthly insights limit reached ({$limit}/{$limit}). Upgrade your plan to continue."
        );
    }

    private function resolveFallbackPackageId(): ?int
    {
        return Package::query()
            ->where('is_active', true)
            ->orderBy('sort')
            ->value('id');
    }

    private function resolveClientPackageId(int $parentCompanyId, ?int $requestedPackageId): ?int
    {
        if ($requestedPackageId) {
            $requestedPlan = ClientPackage::forCompany($parentCompanyId)
                ->where('is_active', true)
                ->find($requestedPackageId);

            if ($requestedPlan) {
                return $requestedPlan->id;
            }
        }

        // Default to the first active free plan when no explicit plan was selected.
        $freePlan = ClientPackage::forCompany($parentCompanyId)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('is_free', true)
                    ->orWhere('monthly_price', '<=', 0);
            })
            ->orderBy('sort')
            ->orderBy('id')
            ->first();

        return $freePlan?->id;
    }

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

        $tenant = Company::withoutGlobalScopes()->find($companyId);
        $this->hasActivePlan = (bool) $tenant?->activePlan();

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
        $this->enforceMaxCompaniesLimit($parentCompanyId);

        $resolvedClientPackageId = $this->resolveClientPackageId(
            $parentCompanyId,
            $request->filled('client_package_id') ? (int) $request->client_package_id : null
        );

        $subCompany = Company::create([
            'created_by'        => $parentCompanyId,
            'company_name'      => $request->company_name,
            'company_email'     => $request->email,
            'status'            => 'active',
            'package_id'        => $this->resolveFallbackPackageId(),
            'industry_type'     => $request->industry_type,
            'client_package_id' => $resolvedClientPackageId,
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
            try {
                $this->enforceMaxCompaniesLimit($parentCompanyId);
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                if ($e->getStatusCode() === 422) {
                    $errors[] = $e->getMessage();
                    break;
                }
                throw $e;
            }

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
                ? ClientPackage::forCompany($parentCompanyId)
                    ->where('is_active', true)
                    ->whereRaw('LOWER(name) = ?', [strtolower($planTier)])
                    ->first()
                : null;

            $resolvedClientPackageId = $this->resolveClientPackageId(
                $parentCompanyId,
                $pkg?->id
            );

            $subCompany = Company::create([
                'created_by'        => $parentCompanyId,
                'company_name'      => $clientName ?: ($email . "'s Company"),
                'company_email'     => $email,
                'status'            => 'active',
                'package_id'        => $this->resolveFallbackPackageId(),
                'client_package_id' => $resolvedClientPackageId,
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
            'message' => "$created client(s) imported" . ($skipped ? ", $skipped skipped." : '.') . (!empty($errors) ? ' ' . $errors[0] : ''),
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

    public function dashboard($id)
    {
        abort_403(!auth()->user()->hasAnyRole(['client_admin', 'manager']));
        $this->clientUser  = User::with(['company.quickbooksConnections', 'company.clientPackage'])->findOrFail($id);
        $this->pageTitle   = $this->clientUser->name . "'s Dashboard";
        return view('clients.dashboard', $this->data);
    }

    public function insights($id)
    {
        $this->client = User::findOrFail($id);
        $this->ensureInsightsQuotaAvailable($this->client->company);
        $this->pageTitle = 'Insights';
        return view('clients.insights', $this->data);
    }

    public function insightDetail($clientId, $insightId)
    {
        $this->client = User::findOrFail($clientId);
        $this->ensureInsightsQuotaAvailable($this->client->company);
        $this->pageTitle = 'Insight Detail';
        return view('clients.insight-detail', $this->data);
    }
}
