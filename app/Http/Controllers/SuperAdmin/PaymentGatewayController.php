<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Http\Request;

class PaymentGatewayController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Payment Gateway Settings';
    }

    public function index()
    {
        $this->credentials = GlobalPaymentGatewayCredentials::first();
        return view('super-admin.payment-settings.index', $this->data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'stripe_mode'   => 'required|in:test,live',
            'stripe_status' => 'required|in:active,inactive',
        ]);

        $creds = GlobalPaymentGatewayCredentials::firstOrNew();
        $creds->fill($request->only('stripe_mode', 'stripe_status', 'live_stripe_key', 'test_stripe_key'));

        // Only overwrite secrets if a real value (non-empty, non-masked) is provided
        $secretFields = ['live_stripe_secret', 'live_stripe_webhook_secret', 'test_stripe_secret', 'test_stripe_webhook_secret'];
        foreach ($secretFields as $field) {
            $value = $request->input($field);
            if ($value && !str_contains($value, '•')) {
                $creds->{$field} = $value;
            }
        }

        $creds->save();

        return Reply::success('Payment gateway settings updated.');
    }
}
