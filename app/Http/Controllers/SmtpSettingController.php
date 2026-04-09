<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmailSmtpSetting;
use Illuminate\Http\Request;

class SmtpSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Email Settings';
    }

    public function index()
    {
        $this->smtpSetting = EmailSmtpSetting::where('company_id', company()->id)->first()
            ?? new EmailSmtpSetting(['company_id' => company()->id]);

        return view('settings.email-smtp', $this->data);
    }

    public function update(Request $request)
    {
        abort_403(!auth()->user()->can('edit_settings'));

        $request->validate([
            'driver'     => 'required|in:smtp,mailgun,sendgrid,ses,log',
            'from_email' => 'required|email|max:255',
            'from_name'  => 'required|string|max:255',
            'host'       => 'nullable|string|max:255',
            'port'       => 'nullable|integer|min:1|max:65535',
        ]);

        EmailSmtpSetting::updateOrCreate(
            ['company_id' => company()->id],
            $request->only(
                'driver', 'host', 'port', 'encryption',
                'username', 'password', 'api_key',
                'from_name', 'from_email', 'is_active'
            )
        );

        return Reply::success('Email settings updated.');
    }
}
