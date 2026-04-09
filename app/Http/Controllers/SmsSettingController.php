<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\SmsSetting;
use Illuminate\Http\Request;

class SmsSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'SMS Settings';
    }

    public function index()
    {
        $this->smsSetting = SmsSetting::where('company_id', company()->id)->first()
            ?? new SmsSetting(['company_id' => company()->id]);

        return view('settings.sms', $this->data);
    }

    public function update(Request $request)
    {
        abort_403(!auth()->user()->can('edit_settings'));

        $request->validate([
            'provider'    => 'required|in:twilio,vonage,aws_sns',
            'from_number' => 'required|string|max:50',
        ]);

        SmsSetting::updateOrCreate(
            ['company_id' => company()->id],
            $request->only('provider', 'account_sid', 'auth_token', 'from_number', 'is_active')
        );

        return Reply::success('SMS settings updated.');
    }
}
