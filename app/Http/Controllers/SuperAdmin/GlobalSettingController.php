<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\EmailTemplate;
use App\Models\GlobalSetting;
use App\Models\SuperAdmin\GlobalPaymentGatewayCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
        $this->templates   = EmailTemplate::whereNull('company_id')->orderBy('name')->get();
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
            'accent_color'    => 'nullable|string|max:20',
            'logo'            => 'nullable|image|max:2048',
            'favicon'         => 'nullable|image|max:512',
        ]);

        $setting = GlobalSetting::firstOrCreate([]);

        $data = $request->only([
            'global_app_name', 'company_email', 'timezone',
            'date_format', 'time_format', 'locale',
            'currency_code', 'primary_color', 'accent_color',
            'allow_registration', 'email_verification', 'maintenance_mode',
        ]);

        if ($request->hasFile('logo')) {
            if ($setting->logo) {
                Storage::disk('public')->delete($setting->logo);
            }
            $data['logo'] = $request->file('logo')->store('global', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($setting->favicon) {
                Storage::disk('public')->delete($setting->favicon);
            }
            $data['favicon'] = $request->file('favicon')->store('global', 'public');
        }

        $setting->update($data);

        Cache::forget('global_setting');

        return Reply::success('Settings updated successfully.');
    }
}
