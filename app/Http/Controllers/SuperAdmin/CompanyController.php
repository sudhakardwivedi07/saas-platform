<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\Company;
use App\Models\CompanyBranding;
use App\Models\SuperAdmin\Package;
use App\Models\SuperAdmin\Subscription;
use App\Models\User;
use App\Services\EmailService;
use App\Services\StripeService;
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

    public function bulkStore(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt|max:5120']);

        $handle  = fopen($request->file('csv_file')->getRealPath(), 'r');
        $rawHead = fgetcsv($handle);
        $header  = array_map(fn($c) => strtolower(trim($c, " \t\n\r\0\x0B\"")), $rawHead);

        $missing = array_diff(['firm_name', 'contact_name', 'email', 'subdomain'], $header);
        if (!empty($missing)) {
            fclose($handle);
            return Reply::error('Missing required CSV columns: ' . implode(', ', $missing));
        }

        $packages = Package::active()->get()->keyBy(fn($p) => strtolower($p->name));
        $seenSubs = [];
        $results  = ['success' => 0, 'errors' => []];
        $rowNum   = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < count($header)) continue;

            $d = array_map(fn($v) => trim($v, " \t\n\r\0\x0B\""), array_combine($header, array_slice($row, 0, count($header))));

            $errors = [];
            if (empty($d['firm_name']))    $errors[] = 'firm_name is required';
            if (empty($d['contact_name'])) $errors[] = 'contact_name is required';
            if (empty($d['email']) || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'valid email is required';
            }
            if (empty($d['subdomain'])) {
                $errors[] = 'subdomain is required';
            } else {
                $slug = Str::slug($d['subdomain']);
                if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
                    $errors[] = 'subdomain must contain only letters, numbers, and hyphens';
                } elseif (isset($seenSubs[$slug])) {
                    $errors[] = 'duplicate subdomain in file';
                } elseif (Company::withoutGlobalScopes()->where('subdomain', $slug)->exists()) {
                    $errors[] = 'subdomain already taken';
                }
            }

            if (!empty($errors)) {
                $results['errors'][] = ['row' => $rowNum, 'firm' => $d['firm_name'] ?? "Row $rowNum", 'errors' => $errors];
                continue;
            }

            $slug    = Str::slug($d['subdomain']);
            $package = !empty($d['plan']) ? ($packages[strtolower($d['plan'])] ?? null) : null;
            $seenSubs[$slug] = true;

            try {
                $company = Company::create([
                    'company_name'  => $d['firm_name'],
                    'app_name'      => $d['firm_name'],
                    'company_email' => $d['email'],
                    'company_phone' => $d['phone'] ?? null,
                    'subdomain'     => $slug,
                    'status'        => 'active',
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

                $role      = Role::firstOrCreate(['name' => 'client_admin', 'guard_name' => 'web']);
                $plainPass = Str::random(16);
                $user      = User::create([
                    'company_id'        => $company->id,
                    'name'              => $d['contact_name'],
                    'email'             => $d['email'],
                    'password'          => Hash::make($plainPass),
                    'status'            => 'active',
                    'admin_approval'    => true,
                    'email_verified_at' => now(),
                ]);
                $user->assignRole($role);

                app(EmailService::class)->sendToUser($user, 'user_welcome', [
                    '{{password}}' => $plainPass,
                ]);

                $results['success']++;
            } catch (\Exception $e) {
                $results['errors'][] = ['row' => $rowNum, 'firm' => $d['firm_name'], 'errors' => [$e->getMessage()]];
            }
        }

        fclose($handle);

        $msg = $results['success'] . ' firm(s) created successfully.';
        if (!empty($results['errors'])) {
            $msg .= ' ' . count($results['errors']) . ' row(s) had errors.';
        }

        return Reply::successWithData($msg, ['results' => $results]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => ['required','string','min:2','max:100','regex:/^[a-zA-Z0-9 .&\-_]+$/'],
            'subdomain'    => 'required|string|max:63|alpha_dash|unique:companies,subdomain',
            'company_email'=> 'required|email|max:255',
            'package_id'   => 'nullable|exists:packages,id',
            'status'       => 'required|in:active,inactive,suspended',
            'admin_name'   => 'required|string|max:255',
            'admin_email'  => 'required|email|max:255',
            'admin_password'=> 'required|string|min:8',
        ], [
            'company_name.required' => 'Firm Name is required.',
            'company_name.min'      => 'Please enter a valid Firm Name.',
            'company_name.max'      => 'Please enter a valid Firm Name.',
            'company_name.regex'    => 'Please enter a valid Firm Name.',
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

        app(EmailService::class)->sendToUser($user, 'user_welcome', [
            '{{password}}' => $request->admin_password,
        ]);

        // If a paid plan is selected, redirect to Stripe checkout
        if ($package && $package->monthly_price > 0) {
            try {
                $session = (new StripeService())->createCheckoutSession(
                    $company,
                    $package,
                    $request->input('billing_cycle', 'monthly'),
                    route('superadmin.companies.index'),
                    route('superadmin.companies.index')
                );
                return Reply::successWithData('Firm created. Redirecting to payment…', [
                    'checkout_url' => $session->url,
                ]);
            } catch (\Exception $e) {
                // Stripe not configured or failed — firm is created, payment can be done later
                \Illuminate\Support\Facades\Log::error('SuperAdmin checkout error: ' . $e->getMessage());
            }
        }

        return Reply::successWithData('Company created successfully.', ['redirect' => route('superadmin.companies.index')]);
    }

    public function initiateCheckout(Request $request, int $id)
    {
        $request->validate([
            'package_id'    => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $company = Company::withoutGlobalScopes()->findOrFail($id);
        $package = Package::findOrFail($request->package_id);

        try {
            $session = (new StripeService())->createCheckoutSession(
                $company,
                $package,
                $request->billing_cycle,
                route('superadmin.companies.index'),
                route('superadmin.companies.index')
            );
            return Reply::successWithData('Redirecting to payment.', ['checkout_url' => $session->url]);
        } catch (\Exception $e) {
            return Reply::error('Payment gateway error: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $this->companies = Company::withoutGlobalScopes()
            ->with(['package', 'subscription', 'onboardedBy', 'adminUser'])
            ->withCount('users')
            ->whereHas('users', fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'client_admin')))
            ->orderByDesc('created_at')
            ->get();

        $this->packages = Package::active()->orderBy('name')->get();

        return view('super-admin.companies.index', $this->data);
    }

    public function show(int $id)
    {
        $this->company = Company::withoutGlobalScopes()
            ->with(['package', 'subscription', 'users', 'payments', 'branding', 'onboardedBy'])
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
            'company_name' => ['required','string','min:2','max:100','regex:/^[a-zA-Z0-9 .&\-_]+$/'],
            'status'       => 'nullable|in:active,inactive,suspended',
            'package_id'   => 'nullable|exists:packages,id',
        ], [
            'company_name.required' => 'Firm Name is required.',
            'company_name.min'      => 'Please enter a valid Firm Name.',
            'company_name.max'      => 'Please enter a valid Firm Name.',
            'company_name.regex'    => 'Please enter a valid Firm Name.',
        ]);

        $data = $request->only('company_name', 'app_name', 'package_id', 'company_email', 'company_phone');
        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }
        $company->update($data);

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company updated successfully.');
    }

    public function destroy(int $id)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($id);
        $company->update(['status' => 'suspended']);
        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company suspended.');
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
