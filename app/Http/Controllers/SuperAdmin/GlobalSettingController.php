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
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

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
        $this->smtpSetting = EmailSmtpSetting::whereNull('company_id')->first();
        return view('super-admin.settings.index', $this->data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'global_app_name' => 'required|string|max:255',
            'company_email'   => 'required|email|max:255',
            'timezone'        => 'required|string',
            'date_format'     => 'nullable|string|max:30',
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
            'time_format', 'locale',
            'currency_code', 'primary_color', 'accent_color',
            'allow_registration', 'email_verification', 'maintenance_mode',
        ]);

        // date_format is managed via its own dedicated endpoint; only include
        // it when explicitly sent (e.g., future forms that still pass it).
        if ($request->filled('date_format')) {
            $data['date_format'] = $request->date_format;
        }

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
            'host'       => trim($request->smtp_host),
            'port'       => (int) $request->smtp_port,
            'encryption' => $request->smtp_encryption ?: null,
            'username'   => trim($request->smtp_username),
            'from_name'  => trim($request->from_name),
            'from_email' => trim($request->from_address),
            'is_active'  => true,
        ];

        if ($request->filled('smtp_password')) {
            $dbData['password'] = trim($request->smtp_password);
        }

        EmailSmtpSetting::updateOrCreate(['company_id' => null], $dbData);

        return Reply::success('SMTP settings saved successfully.');
    }

    public function updateDateFormat(Request $request)
    {
        $request->validate([
            'date_format' => 'required|string|max:30',
        ]);

        $setting = GlobalSetting::firstOrCreate([]);
        $setting->update(['date_format' => $request->date_format]);

        Cache::forget('global_setting');

        return Reply::success('Date format updated. Changes will be visible across the entire platform.');
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

    public function testSmtp(Request $request)
    {
        $request->validate(['to_email' => 'required|email|max:255']);

        $setting = EmailSmtpSetting::whereNull('company_id')->first();

        if (!$setting || !$setting->host) {
            return Reply::error('No SMTP settings saved yet. Please save your settings first, then test.');
        }

        try {
            $username   = rawurlencode((string)($setting->username ?? ''));
            $password   = rawurlencode((string)($setting->password ?? ''));
            $host       = $setting->host;
            $port       = (int)($setting->port ?? 587);
            $encryption = $setting->encryption ?? 'tls';

            if ($encryption === 'ssl') {
                $dsn = "smtps://{$username}:{$password}@{$host}:{$port}?timeout=15";
            } elseif ($encryption === 'tls') {
                $dsn = "smtp://{$username}:{$password}@{$host}:{$port}?timeout=15";
            } else {
                $dsn = "smtp://{$username}:{$password}@{$host}:{$port}?timeout=15&verify_peer=0";
            }

            $transport = Transport::fromDsn($dsn);
            $mailer    = new SymfonyMailer($transport);

            $appName = GlobalSetting::first()?->global_app_name ?? config('app.name');

            $email = (new SymfonyEmail())
                ->from(new Address($setting->from_email, $setting->from_name ?? ''))
                ->to($request->to_email)
                ->subject('SMTP Test — ' . $appName)
                ->html(
                    '<p>Hi,</p>'
                    . '<p>This is a test email sent from <strong>' . e($appName) . '</strong> to verify your SMTP configuration.</p>'
                    . '<p>If you received this, your SMTP settings are working correctly.</p>'
                );

            $mailer->send($email);

            return Reply::success('Test email sent successfully to ' . $request->to_email . '. Please check your inbox.');
        } catch (\Exception $e) {
            return Reply::error('SMTP test failed: ' . $e->getMessage());
        }
    }

}
