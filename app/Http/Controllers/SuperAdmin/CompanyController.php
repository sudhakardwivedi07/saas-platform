<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class CompanyController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin'])->except('stopImpersonation');
        $this->pageTitle = 'Companies';
    }

    public function create()
    {
        $this->packages = Package::active()->orderBy('sort')->get();
        return view('super-admin.companies.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain'    => 'required|string|max:63|alpha_dash|unique:companies,subdomain',
            'company_email'=> 'required|email|max:255',
            'package_id'   => 'nullable|exists:packages,id',
            'status'       => 'required|in:active,inactive,suspended',
            'admin_name'   => 'required|string|max:255',
            'admin_email'  => 'required|email|max:255',
            'admin_password'=> 'required|string|min:8',
        ]);

        $package = $request->package_id ? Package::find($request->package_id) : null;

        $company = Company::create([
            'company_name'  => $request->company_name,
            'app_name'      => $request->app_name ?? $request->company_name,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'subdomain'     => Str::slug($request->subdomain),
            'status'        => $request->status,
            'package_id'    => $package?->id,
            'trial_ends_at' => $package?->is_trial_allowed ? now()->addDays($package->trial_days) : null,
        ]);

        CompanyBranding::create(['company_id' => $company->id]);

        if ($package) {
            Subscription::create([
                'company_id'           => $company->id,
                'package_id'           => $package->id,
                'status'               => 'trialing',
                'billing_cycle'        => 'monthly',
                'trial_ends_at'        => $company->trial_ends_at,
                'current_period_start' => now(),
                'current_period_end'   => $company->trial_ends_at ?? now()->addDays(14),
            ]);
        }

        $role = Role::firstOrCreate(['name' => 'client_admin', 'guard_name' => 'web']);
        $user = User::create([
            'company_id'        => $company->id,
            'name'              => $request->admin_name,
            'email'             => $request->admin_email,
            'password'          => Hash::make($request->admin_password),
            'status'            => 'active',
            'admin_approval'    => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole($role);

        return Reply::successWithData('Company created successfully.', ['redirect' => route('superadmin.companies.index')]);
    }

    public function index(Request $request)
    {
        $this->companies = Company::withoutGlobalScopes()
            ->with(['package', 'subscription'])
            ->when($request->search, fn ($q) => $q->where('company_name', 'like', "%{$request->search}%"))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('super-admin.companies.index', $this->data);
    }

    public function show(int $id)
    {
        $this->company = Company::withoutGlobalScopes()
            ->with(['package', 'subscription', 'users', 'payments', 'branding'])
            ->findOrFail($id);

        $this->userCount      = $this->company->users->count();
        $this->totalPaid      = $this->company->payments->where('status', 'complete')->sum('amount');
        $this->recentPayments = $this->company->payments()->orderByDesc('created_at')->limit(5)->get();

        return view('super-admin.companies.show', $this->data);
    }

    public function edit(int $id)
    {
        $this->company  = Company::withoutGlobalScopes()->with('branding')->findOrFail($id);
        $this->packages = Package::active()->orderBy('sort')->get();
        return view('super-admin.companies.edit', $this->data);
    }

    public function update(Request $request, int $id)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($id);

        $request->validate([
            'company_name' => 'required|string|max:255',
            'status'       => 'required|in:active,inactive,suspended',
            'package_id'   => 'nullable|exists:packages,id',
        ]);

        $company->update($request->only(
            'company_name', 'app_name', 'status',
            'package_id', 'company_email', 'company_phone'
        ));

        return Reply::success('Company updated successfully.');
    }

    public function destroy(int $id)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($id);
        $company->update(['status' => 'suspended']);
        return Reply::success('Company suspended.');
    }

    public function impersonate(int $id)
    {
        abort_403(!is_superadmin());

        $company = Company::withoutGlobalScopes()->findOrFail($id);

        $admin = User::where('company_id', $id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'client_admin'))
            ->first();

        // Fall back to any user in that company if no client_admin yet
        if (!$admin) {
            $admin = User::where('company_id', $id)->firstOrFail();
        }

        session(['impersonating_company' => $id, 'original_superadmin' => auth()->id()]);
        Auth::loginUsingId($admin->id);

        return redirect()->route('dashboard')->with('info', "Viewing as {$company->company_name}");
    }

    public function stopImpersonation()
    {
        $superAdminId = session('original_superadmin');
        abort_403(!$superAdminId);

        session()->forget(['impersonating_company', 'original_superadmin']);
        Auth::loginUsingId($superAdminId);

        return redirect()->route('superadmin.dashboard');
    }
}
