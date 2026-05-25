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
use Illuminate\Validation\Rule;
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

                $role      = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
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

                app(EmailService::class)->sendToUser($user, 'firm_welcome_email', [
                    '{{first_name}}'    => explode(' ', $user->name)[0],
                    '{{firm_name}}'     => $company->company_name,
                    '{{portal_url}}'    => $company->domainUrlFor('login') ?? url('/login'),
                    '{{password}}'      => $plainPass,
                    '{{company_name}}' => $company->company_name,
                    '{{subdomain}}'    => $company->subdomain,
                    '{{plan_name}}'    => $package?->name ?? 'No plan assigned',
                    '{{trial_ends_at}}' => $company->trial_ends_at?->format('M d, Y') ?? 'N/A',
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
        $domainType = $request->input('domain_type', 'subdomain');

        $request->validate([
            'company_name'  => ['required','string','min:2','max:100','regex:/^[a-zA-Z0-9 .&\-_]+$/'],
            'domain_type'   => ['required','in:subdomain,domain'],
            'subdomain'     => $domainType === 'subdomain'
                                ? ['required','string','min:3','max:50','regex:/^[a-z0-9-]+$/','unique:companies,subdomain']
                                : ['nullable'],
            'custom_domain' => $domainType === 'domain'
                                ? ['required','string','max:253','regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/','unique:companies,custom_domain']
                                : ['nullable'],
            'company_email' => ['required','string','min:5','max:100','regex:/^[a-zA-Z0-9@._-]+$/','email','unique:companies,company_email'],
            'company_phone' => 'required|digits:10',
            'package_id'    => 'nullable|exists:packages,id',
            'status'        => 'required|in:active,trial,inactive,suspended',
            'admin_name'    => 'required|string|max:255',
            'admin_email'   => 'required|email|max:255',
            'admin_password'=> 'required|string|min:8',
        ], [
            'company_name.required'   => 'Firm Name is required.',
            'company_name.min'        => 'Please enter a valid Firm Name.',
            'company_name.max'        => 'Please enter a valid Firm Name.',
            'company_name.regex'      => 'Please enter a valid Firm Name.',
            'subdomain.required'      => 'Subdomain is required.',
            'subdomain.min'           => 'Only lowercase letters, numbers, and hyphens are allowed.',
            'subdomain.max'           => 'Only lowercase letters, numbers, and hyphens are allowed.',
            'subdomain.regex'         => 'Only lowercase letters, numbers, and hyphens are allowed.',
            'subdomain.unique'        => 'This subdomain is already taken.',
            'custom_domain.required'  => 'Custom domain is required.',
            'custom_domain.regex'     => 'Please enter a valid domain (e.g. app.acmecorp.com).',
            'custom_domain.unique'    => 'This domain is already in use.',
            'company_email.required'  => 'Email is required.',
            'company_email.min'       => 'Please enter a valid email address.',
            'company_email.max'       => 'Please enter a valid email address.',
            'company_email.regex'     => 'Please enter a valid email address.',
            'company_email.email'     => 'Please enter a valid email address.',
            'company_email.unique'    => 'This email is already in use.',
            'company_phone.required'  => 'Phone number is required.',
            'company_phone.digits'    => 'Phone number must be 10 digits.',
        ]);

        $package = $request->package_id ? Package::find($request->package_id) : null;

        $verificationToken = $domainType === 'domain' ? Str::random(40) : null;

        $company = Company::create([
            'company_name'              => $request->company_name,
            'app_name'                  => $request->app_name ?? $request->company_name,
            'company_email'             => $request->company_email,
            'company_phone'             => $request->company_phone,
            'domain_type'               => $domainType,
            'subdomain'                 => $domainType === 'subdomain' ? Str::slug($request->subdomain) : null,
            'custom_domain'             => $domainType === 'domain' ? strtolower(trim($request->custom_domain)) : null,
            'domain_verification_token' => $verificationToken,
            'status'                    => $request->status,
            'package_id'                => $package?->id,
            'trial_ends_at'             => $package?->is_trial_allowed ? now()->addDays($package->trial_days) : null,
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

        $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
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

        app(EmailService::class)->sendToUser($user, 'firm_welcome_email', [
            '{{first_name}}'    => explode(' ', $user->name)[0],
            '{{firm_name}}'     => $company->company_name,
            '{{portal_url}}'    => $company->domainUrlFor('login') ?? url('/login'),
            '{{password}}'      => $request->admin_password,
            '{{company_name}}' => $company->company_name,
            '{{subdomain}}'    => $company->subdomain,
            '{{plan_name}}'    => $package?->name ?? 'No plan assigned',
            '{{trial_ends_at}}' => $company->trial_ends_at?->format('M d, Y') ?? 'N/A',
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
                return Reply::successWithData('Firm created successfully. Redirecting to payment…', [
                    'checkout_url' => $session->url,
                ]);
            } catch (\Exception $e) {
                // Stripe not configured or failed — firm is created, payment can be done later
                \Illuminate\Support\Facades\Log::error('SuperAdmin checkout error: ' . $e->getMessage());
            }
        }

        return Reply::successWithData('Firm created successfully. The firm has been added and is ready for setup.', ['redirect' => route('superadmin.companies.index')]);
    }

    /**
     * Check the DNS TXT record for a custom-domain company and mark it verified.
     *
     * The customer must add:  _saas-verify.{custom_domain}  TXT  "saas-verify={token}"
     */
    public function verifyDomain(int $id)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($id);

        if ($company->domain_type !== 'domain' || !$company->custom_domain) {
            return Reply::error('This firm does not use a custom domain.');
        }

        if ($company->domain_verified_at) {
            return Reply::success('Domain is already verified.');
        }

        $lookupHost = '_saas-verify.' . $company->custom_domain;
        $token      = 'saas-verify=' . $company->domain_verification_token;
        $verified   = false;

        try {
            $records = @dns_get_record($lookupHost, DNS_TXT);
            if (is_array($records)) {
                foreach ($records as $rec) {
                    if (isset($rec['txt']) && $rec['txt'] === $token) {
                        $verified = true;
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            return Reply::error('DNS lookup failed. Please try again later.');
        }

        if (!$verified) {
            return Reply::error('Verification TXT record not found. Please add the record and try again.');
        }

        $company->update(['domain_verified_at' => now()]);

        return Reply::success('Domain verified successfully!');
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
            ->withCount([
                'users',
                'payments as complete_payments_count' => fn($q) => $q->where('status', 'complete'),
            ])
            ->whereHas('users', fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', 'admin')))
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
        $company    = Company::withoutGlobalScopes()->findOrFail($id);
        $domainType = $request->input('domain_type', $company->domain_type ?? 'subdomain');

        $request->validate([
            'company_name'  => ['required','string','min:2','max:100','regex:/^[a-zA-Z0-9 .&\-_]+$/'],
            'company_email' => ['required','string','min:5','max:100','regex:/^[a-zA-Z0-9@._-]+$/','email', Rule::unique('companies','company_email')->ignore($id)],
            'company_phone' => 'required|digits:10',
            'domain_type'   => 'nullable|in:subdomain,domain',
            'custom_domain' => $domainType === 'domain'
                                ? ['required','string','max:253','regex:/^([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}$/', Rule::unique('companies','custom_domain')->ignore($id)]
                                : ['nullable'],
            'status'        => 'nullable|in:active,trial,inactive,suspended',
            'package_id'    => 'nullable|exists:packages,id',
        ], [
            'company_name.required'  => 'Firm Name is required.',
            'company_name.min'       => 'Please enter a valid Firm Name.',
            'company_name.max'       => 'Please enter a valid Firm Name.',
            'company_name.regex'     => 'Please enter a valid Firm Name.',
            'company_email.required' => 'Email is required.',
            'company_email.min'      => 'Please enter a valid email address.',
            'company_email.max'      => 'Please enter a valid email address.',
            'company_email.regex'    => 'Please enter a valid email address.',
            'company_email.email'    => 'Please enter a valid email address.',
            'company_email.unique'   => 'This email is already in use.',
            'company_phone.required' => 'Phone number is required.',
            'company_phone.digits'   => 'Phone number must be 10 digits.',
            'custom_domain.required' => 'Custom domain is required.',
            'custom_domain.regex'    => 'Please enter a valid domain (e.g. app.acmecorp.com).',
            'custom_domain.unique'   => 'This domain is already in use.',
        ]);

        $data = $request->only('company_name', 'app_name', 'package_id', 'company_email', 'company_phone');

        if ($request->filled('status')) {
            $data['status'] = $request->status;
        }

        $data['domain_type'] = $domainType;

        if ($domainType === 'domain') {
            $newDomain = strtolower(trim($request->custom_domain));
            $data['custom_domain'] = $newDomain;

            // Reset verification if the domain has changed
            if ($newDomain !== $company->custom_domain) {
                $data['domain_verified_at']        = null;
                $data['domain_verification_token'] = Str::random(40);
            }
            // Ensure a token exists even if the domain hasn't changed
            if (empty($company->domain_verification_token)) {
                $data['domain_verification_token'] = Str::random(40);
            }
        } else {
            // Switching back to subdomain — clear custom domain data
            $data['custom_domain']             = null;
            $data['domain_verified_at']        = null;
            $data['domain_verification_token'] = null;
        }

        $company->update($data);

        return Reply::successWithData('Company updated successfully.', []);
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
            ->whereHas('roles', fn ($q) => $q->where('name', 'admin'))
            ->first();

        // Fall back to any user in that company if no admin yet
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
