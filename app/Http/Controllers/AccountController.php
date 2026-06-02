<?php

namespace App\Http\Controllers;

use App\Models\ClientPackage;
use App\Models\Company;
use App\Models\EventLog;
use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use App\Models\SuperAdmin\Subscription;
use App\Models\SupportTicket;
use App\Models\SuperAdmin\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;

class AccountController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'My Account';
    }

    public function index()
    {
        if (!auth()->user()->hasAnyRole(['admin', 'manager'])) {
            $company  = company();
            $parentId = $company->created_by;

            $plans = $parentId
                ? ClientPackage::where('company_id', $parentId)
                    ->where('is_active', true)
                    ->orderBy('sort')
                    ->orderBy('monthly_price')
                    ->get()
                : collect();

            $this->currentPlan  = $company->activePlan();
            $this->plans        = $plans;
            $this->subscription = $company->subscription;

            $this->data['myTickets'] = SupportTicket::where('user_id', auth()->id())
                ->with('user', 'company')
                ->orderByDesc('created_at')
                ->get();

            return view('account.user', $this->data);
        }

        // Activity log data for the tab
        $this->activities = EventLog::query()
            ->where('company_id', company()?->id)
            ->with('user:id,name,email')
            ->latest('created_at')
            ->get();

        $this->eventTypes = EventLog::query()
            ->where('company_id', company()?->id)
            ->distinct()
            ->orderBy('event_type')
            ->pluck('event_type');

        // Branding for the Login Page tab
        $this->branding = company()?->branding;

        // ── Subscription tab data ──────────────────────────────────────────────
        $company   = company();
        $companyId = $company?->id;

        $this->subscription = $company?->subscription;
        $this->packages     = Package::active()->orderBy('sort')->get();

        // Lazy-sync payment method from Stripe for subscriptions that pre-date
        // the pm_* columns (i.e. pm_last4 is null but a Stripe sub ID exists).
        $this->syncPaymentMethodIfMissing($this->subscription);

        // Sub-companies for usage stats
        $subCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $companyId)
            ->with(['clientPackage', 'package', 'quickbooksConnections',
                    'users' => fn($q) => $q->orderBy('id')->limit(1)])
            ->get();
        $this->subCompanies = $subCompanies;

        $maxInsights = $this->subscription?->package?->max_documents ?? 0;
        $this->totalInsightsUsed      = $subCompanies->sum('insights_used');
        $this->totalInsightsRemaining = $maxInsights > 0
            ? max(0, $maxInsights - $this->totalInsightsUsed)
            : 0;

        $quotaFull = $subCompanies->filter(fn($c) => $c->clientPackage && $c->clientPackage->max_documents > 0 && $c->insights_used >= $c->clientPackage->max_documents)->count();
        $inactive  = $subCompanies->where('status', 'inactive')->count();
        $noQB      = $subCompanies->filter(fn($c) => $c->quickbooksConnections->where('is_active', true)->isEmpty())->count();

        $this->companiesAtRisk = $subCompanies->filter(fn($c) =>
            $c->status === 'inactive' ||
            $c->quickbooksConnections->where('is_active', true)->isEmpty() ||
            ($c->clientPackage && $c->clientPackage->max_documents > 0 && $c->insights_used >= $c->clientPackage->max_documents)
        )->count();
        $this->atRiskContext = collect([
            $quotaFull ? $quotaFull . ' quota full' : null,
            $inactive  ? $inactive  . ' inactive'   : null,
            $noQB      ? $noQB      . ' no QB'       : null,
        ])->filter()->implode(' · ');
        $this->uniqueClients = $subCompanies->map(fn($c) => $c->users->first()?->id)->filter()->unique()->count();

        return view('account.index', $this->data);
    }

    /**
     * For subscriptions that existed before the pm_* columns were added,
     * fetch the default payment method from Stripe once and persist it so
     * the Payment Method card displays on the next page load.
     */
    private function syncPaymentMethodIfMissing(?Subscription $sub): void
    {
        if (!$sub || $sub->pm_last4 || !$sub->stripe_subscription_id) {
            return;
        }

        try {
            $creds  = GlobalPaymentGatewayCredentials::first();
            $secret = $creds?->getActiveSecret();
            if (!$secret) {
                return;
            }
            Stripe::setApiKey($secret);

            $stripeSub = StripeSubscription::retrieve([
                'id'     => $sub->stripe_subscription_id,
                'expand' => ['default_payment_method'],
            ]);

            $pm = $stripeSub->default_payment_method;
            if ($pm && $pm->type === 'card' && $pm->card) {
                $sub->update([
                    'pm_brand'     => $pm->card->brand,
                    'pm_last4'     => $pm->card->last4,
                    'pm_exp_month' => $pm->card->exp_month,
                    'pm_exp_year'  => $pm->card->exp_year,
                ]);
                // Refresh the in-memory model so the view sees the new values.
                $sub->refresh();
            }
        } catch (\Throwable $e) {
            // Non-fatal — if Stripe is unavailable or keys aren't configured,
            // just skip; the Payment Method card will remain hidden.
            Log::channel('stripe')->warning('[AccountController] syncPaymentMethodIfMissing failed', [
                'subscription_id' => $sub->id,
                'error'           => $e->getMessage(),
            ]);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user->update(['name' => $request->name]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password changed successfully.');
    }
}
