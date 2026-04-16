<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\GlobalSetting;
use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GlobalSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Global Settings';
    }

    public function index()
    {
        $this->setting     = GlobalSetting::first();
        $this->credentials = GlobalPaymentGatewayCredentials::first();
        return view('super-admin.settings.index', $this->data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'global_app_name' => 'required|string|max:255',
            'company_email'   => 'required|email|max:255',
            'timezone'        => 'required|string',
            'date_format'     => 'required|string',
            'locale'          => 'required|string|max:10',
            'currency_code'   => 'required|string|max:10',
            'primary_color'   => 'nullable|string|max:20',
        ]);

        $setting = GlobalSetting::firstOrCreate([]);
        $setting->update($request->only([
            'global_app_name', 'company_email', 'timezone',
            'date_format', 'time_format', 'locale',
            'currency_code', 'primary_color',
            'allow_registration', 'email_verification', 'maintenance_mode',
        ]));

        Cache::forget('global_setting');

        return Reply::success('Settings updated successfully.');
    }
}
