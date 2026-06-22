<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmailSmtpSetting;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

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

        $data = $request->only(
            'driver', 'host', 'port', 'encryption',
            'username', 'password', 'api_key',
            'from_name', 'from_email', 'is_active'
        );

        foreach (['password', 'username', 'host', 'api_key', 'from_name', 'from_email'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }

        EmailSmtpSetting::updateOrCreate(
            ['company_id' => company()->id],
            $data
        );

        return Reply::success('Email settings updated.');
    }

    public function testSmtp(Request $request)
    {
        abort_403(!auth()->user()->can('edit_settings'));

        $request->validate(['to_email' => 'required|email|max:255']);

        $setting = EmailSmtpSetting::where('company_id', company()->id)->first();

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

            $portalName = company()?->company_name ?? config('app.name');

            $email = (new SymfonyEmail())
                ->from(new Address($setting->from_email, $setting->from_name ?? ''))
                ->to($request->to_email)
                ->subject('SMTP Test — ' . $portalName)
                ->html(
                    '<p>Hi,</p>'
                    . '<p>This is a test email sent from <strong>' . e($portalName) . '</strong> to verify your SMTP configuration.</p>'
                    . '<p>If you received this, your SMTP settings are working correctly.</p>'
                );

            $mailer->send($email);

            return Reply::success('Test email sent successfully to ' . $request->to_email . '. Please check your inbox.');
        } catch (\Exception $e) {
            return Reply::error('SMTP test failed: ' . $e->getMessage());
        }
    }
}
