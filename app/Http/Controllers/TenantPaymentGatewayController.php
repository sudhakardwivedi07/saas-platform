<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\CompanyPaymentGateway;
use Illuminate\Http\Request;

class TenantPaymentGatewayController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Payment Gateway';
    }

    public function index()
    {
        $this->gateway = CompanyPaymentGateway::where('company_id', company()->id)->first();
        return view('settings.payment-gateway', $this->data);
    }

    public function update(Request $request)
    {
        abort_403(!auth()->user()->can('manage_payment_gateway'));

        $request->validate([
            'mode'   => 'required|in:test,live',
            'status' => 'required|in:active,inactive',
        ]);

        $gateway = CompanyPaymentGateway::firstOrNew(['company_id' => company()->id]);
        $gateway->fill($request->only('mode', 'status', 'live_key', 'test_key'));

        // Only update secrets if a real (non-masked) value is provided
        foreach (['live_secret', 'live_webhook_secret', 'test_secret', 'test_webhook_secret'] as $field) {
            $value = $request->input($field);
            if ($value && !str_contains($value, '•')) {
                $gateway->{$field} = $value;
            }
        }

        $gateway->company_id = company()->id;
        $gateway->save();

        return Reply::success('Payment gateway settings updated.');
    }
}
