<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\EmailSmtpSetting;
use App\Models\EmailTemplate;
use App\Models\GlobalSetting;
use App\Models\SmsSetting;
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
        $this->smsSetting  = SmsSetting::whereNull('company_id')->first();
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

    public function updateSmtp(Request $request)
    {
        $request->validate([
            'smtp_host'       => 'required|string|max:255',
            'smtp_port'       => 'required|integer|min:1|max:65535',
            'smtp_username'   => 'required|string|max:255',
            'smtp_password'   => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string',
            'from_address'    => 'required|email|max:255',
            'from_name'       => 'required|string|max:255',
        ]);

        $dbData = [
            'driver'     => 'smtp',
            'host'       => $request->smtp_host,
            'port'       => (int) $request->smtp_port,
            'encryption' => $request->smtp_encryption ?: null,
            'username'   => $request->smtp_username,
            'from_name'  => $request->from_name,
            'from_email' => $request->from_address,
            'is_active'  => true,
        ];

        if ($request->filled('smtp_password')) {
            $dbData['password'] = $request->smtp_password;
        }

        EmailSmtpSetting::updateOrCreate(['company_id' => null], $dbData);

        $envUpdates = [
            'MAIL_MAILER'       => 'smtp',
            'MAIL_SCHEME'       => $request->smtp_encryption ?: 'null',
            'MAIL_HOST'         => $request->smtp_host,
            'MAIL_PORT'         => (string) $request->smtp_port,
            'MAIL_USERNAME'     => $request->smtp_username,
            'MAIL_FROM_ADDRESS' => $request->from_address,
            'MAIL_FROM_NAME'    => $request->from_name,
        ];

        if ($request->filled('smtp_password')) {
            $envUpdates['MAIL_PASSWORD'] = $request->smtp_password;
        }

        $this->updateEnvFile($envUpdates);

        return Reply::success('SMTP settings saved successfully.');
    }

    public function updateSms(Request $request)
    {
        $request->validate([
            'twilio_sid'  => 'required|string|max:255',
            'twilio_token' => 'nullable|string|max:255',
            'twilio_from' => 'required|string|max:20',
            'sms_status'  => 'required|in:active,inactive',
        ]);

        $dbData = [
            'provider'    => 'twilio',
            'account_sid' => $request->twilio_sid,
            'from_number' => $request->twilio_from,
            'is_active'   => $request->sms_status === 'active',
        ];

        if ($request->filled('twilio_token')) {
            $dbData['auth_token'] = $request->twilio_token;
        }

        SmsSetting::updateOrCreate(['company_id' => null], $dbData);

        return Reply::success('SMS settings saved successfully.');
    }

    private function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        $content = file_get_contents($envPath);

        foreach ($data as $key => $value) {
            $needsQuotes = (bool) preg_match('/[\s#"\'\\\\]/', (string) $value);
            $formatted   = $needsQuotes ? '"' . addslashes((string) $value) . '"' : (string) $value;

            if (preg_match('/^' . preg_quote($key, '/') . '=/m', $content)) {
                $content = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', $key . '=' . $formatted, $content);
            } else {
                $content .= PHP_EOL . $key . '=' . $formatted;
            }
        }

        file_put_contents($envPath, $content);
    }
}
