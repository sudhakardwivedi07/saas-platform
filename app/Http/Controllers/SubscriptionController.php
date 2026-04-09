<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\SuperAdmin\Package;
use App\Services\StripeService;
use Illuminate\Http\Request;

class SubscriptionController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Billing';
    }

    public function index()
    {
        $company            = company();
        $this->subscription = $company->subscription;
        $this->payments     = $company->payments()->orderByDesc('created_at')->paginate(10);
        $this->packages     = Package::active()->orderBy('sort')->get();

        return view('billing.index', $this->data);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'package_id'    => 'required|exists:packages,id',
            'billing_cycle' => 'required|in:monthly,annual',
        ]);

        $company = company();
        $package = Package::findOrFail($request->package_id);

        $session = (new StripeService())->createCheckoutSession(
            $company,
            $package,
            $request->billing_cycle
        );

        return response()->json(['checkout_url' => $session->url]);
    }

    public function success()
    {
        return view('billing.success');
    }

    public function cancel()
    {
        return redirect()->route('billing.index')->with('info', 'Checkout was cancelled.');
    }
}
