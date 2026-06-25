<?php

namespace App\Services;

use App\Models\Company;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use App\Models\SmsSetting;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send an email via a named template slug.
     * Resolves: tenant-specific template → global fallback.
     * Resolves: tenant-specific SMTP → global fallback.
     */
    public function send(
        string $toEmail,
        string $templateSlug,
        array  $placeholders  = [],
        ?int   $companyId     = null,
        ?string $attachmentPath = null
    ): bool {
        $template = EmailTemplate::resolveFor($templateSlug, $companyId);

        if (!$template) {
            Log::warning("EmailService: template '{$templateSlug}' not found for company {$companyId}");
            return false;
        }

        $subject = $this->replacePlaceholders($template->subject, $placeholders);
        $body    = $this->replacePlaceholders($template->body, $placeholders);

        $log = EmailLog::create([
            'company_id'  => $companyId,
            'template_id' => $template->id,
            'to_email'    => $toEmail,
            'subject'     => $subject,
            'status'      => 'queued',
        ]);

        try {
            $this->configureMailer($companyId);

            $fromName  = $template->from_name  ?? config('mail.from.name');
            $fromEmail = $template->from_email ?? config('mail.from.address');

            Mail::html($this->wrapInLayout($body, $placeholders), function ($msg) use (
                $toEmail, $subject, $fromName, $fromEmail, $template, $attachmentPath
            ) {
                $msg->to($toEmail)
                    ->subject($subject)
                    ->from($fromEmail, $fromName);

                if ($template->cc)  $msg->cc(explode(',', $template->cc));
                if ($template->bcc) $msg->bcc(explode(',', $template->bcc));
                if ($attachmentPath && file_exists($attachmentPath)) {
                    $msg->attach($attachmentPath);
                }
            });

            $log->update(['status' => 'sent', 'sent_at' => now()]);
            return true;

        } catch (\Throwable $e) {
            Log::error("EmailService failed for {$toEmail}: " . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send an email using ONLY the firm's own template + SMTP.
     * Does NOT fall back to global/superadmin templates or SMTP.
     * Returns false silently if the template is missing or inactive.
     */
    public function sendFromFirm(
        int    $firmId,
        string $toEmail,
        string $templateSlug,
        array  $placeholders   = [],
        ?string $attachmentPath = null
    ): bool {
        $template = EmailTemplate::where('company_id', $firmId)
                                 ->where('slug', $templateSlug)
                                 ->where('is_active', true)
                                 ->first();

        if (!$template) {
            Log::info("EmailService: firm {$firmId} template '{$templateSlug}' not found or inactive — skipping.");
            return false;
        }

        $subject = $this->replacePlaceholders($template->subject, $placeholders);
        $body    = $this->replacePlaceholders($template->body,    $placeholders);

        $log = EmailLog::create([
            'company_id'  => $firmId,
            'template_id' => $template->id,
            'to_email'    => $toEmail,
            'subject'     => $subject,
            'status'      => 'queued',
        ]);

        try {
            $this->configureFirmMailer($firmId);

            $fromName  = $template->from_name  ?? config('mail.from.name');
            $fromEmail = $template->from_email ?? config('mail.from.address');

            Mail::html($this->wrapInLayout($body, $placeholders), function ($msg) use (
                $toEmail, $subject, $fromName, $fromEmail, $template, $attachmentPath
            ) {
                $msg->to($toEmail)
                    ->subject($subject)
                    ->from($fromEmail, $fromName);

                if ($template->cc)  $msg->cc(explode(',', $template->cc));
                if ($template->bcc) $msg->bcc(explode(',', $template->bcc));
                if ($attachmentPath && file_exists($attachmentPath)) {
                    $msg->attach($attachmentPath);
                }
            });

            $log->update(['status' => 'sent', 'sent_at' => now()]);
            return true;

        } catch (\Throwable $e) {
            Log::error("EmailService::sendFromFirm failed for {$toEmail}: " . $e->getMessage());
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Send directly to a User model (convenience wrapper).
     */
    public function sendToUser(User $user, string $templateSlug, array $extra = []): bool
    {
        $company = $user->company;

        $placeholders = array_merge([
            '{{name}}'          => $user->name,
            '{{email}}'         => $user->email,
            '{{app_name}}'      => $company?->app_name ?? global_setting()?->global_app_name ?? config('app.name'),
            '{{company}}'       => $company?->company_name ?? '',
            '{{login_url}}'     => $company?->login_url ?? url('/login'),
            '{{support_email}}' => $company?->company_email ?? config('mail.from.address'),
            '{{date}}'          => now()->format('Y-m-d'),
        ], $extra);

        return $this->send($user->email, $templateSlug, $placeholders, $user->company_id);
    }

    /**
     * Send a raw HTML email using DB-configured SMTP, bypassing template lookup.
     * Used as a fallback when no DB template exists for a given slug.
     */
    public function sendHtml(
        string  $toEmail,
        string  $subject,
        string  $htmlBody,
        ?int    $companyId = null,
        ?string $fromEmail = null,
        ?string $fromName  = null,
        ?string $appName   = null
    ): bool {
        try {
            $this->configureMailer($companyId);

            $from = $fromEmail ?? config('mail.from.address');
            $name = $fromName  ?? config('mail.from.name');

            $wrapped = $this->wrapInLayout($htmlBody, ['{{app_name}}' => $appName ?? config('app.name')]);

            Mail::html($wrapped, function ($msg) use ($toEmail, $subject, $from, $name) {
                $msg->to($toEmail)->subject($subject)->from($from, $name);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("EmailService::sendHtml failed for {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Replace {{placeholder}} tokens in a string.
     */
    public function replacePlaceholders(string $content, array $placeholders): string
    {
        return str_replace(array_keys($placeholders), array_values($placeholders), $content);
    }

    /**
     * Wrap the email body in a clean responsive HTML layout.
     */
    private function wrapInLayout(string $body, array $placeholders): string
    {
        $appName = $placeholders['{{app_name}}'] ?? config('app.name');
        $primaryColor = '#2563eb';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { font-family: Inter, Arial, sans-serif; background: #f8fafc; margin: 0; padding: 20px; }
  .wrapper { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
  .header { background: {$primaryColor}; padding: 24px 32px; }
  .header h1 { color: #fff; margin: 0; font-size: 20px; }
  .body { padding: 32px; color: #1e293b; font-size: 15px; line-height: 1.6; }
  .footer { background: #f1f5f9; padding: 16px 32px; text-align: center; font-size: 12px; color: #64748b; }
  a { color: {$primaryColor}; }
</style>
</head>
<body>
  <div class="wrapper">
    <div class="body">{$body}</div>
  </div>
</body>
</html>
HTML;
    }

    /**
     * Configure mailer using ONLY the firm's own SMTP (no global fallback).
     */
    private function configureFirmMailer(int $firmId): void
    {
        $setting = \App\Models\EmailSmtpSetting::where('company_id', $firmId)
                        ->where('is_active', true)
                        ->first();

        if (!$setting) return;

        $this->applySmtpSetting($setting);
    }

    /**
     * Configure Laravel's mailer at runtime for per-tenant SMTP.
     *
     * Resolution order:
     *   1. Company-specific SMTP (company_id = $companyId)
     *   2. Parent firm's SMTP — for client companies whose created_by points to a firm
     *   3. Global SuperAdmin SMTP (company_id IS NULL)
     */
    private function configureMailer(?int $companyId): void
    {
        $setting = $this->resolveSmtpSetting($companyId);

        if (!$setting) return;

        $this->applySmtpSetting($setting);
    }

    /**
     * Walk the company hierarchy to find the best active SMTP setting.
     */
    private function resolveSmtpSetting(?int $companyId): ?\App\Models\EmailSmtpSetting
    {
        if ($companyId) {
            // Direct company SMTP
            $setting = \App\Models\EmailSmtpSetting::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
            if ($setting) return $setting;

            // Walk up to parent firm for client companies.
            // Client companies have created_by = firm's company_id (an integer that is a valid company).
            // Firm companies have created_by = superadmin user_id (not a company id).
            $company = \App\Models\Company::find($companyId);
            if ($company && $company->created_by) {
                $parentExists = \App\Models\Company::where('id', $company->created_by)->exists();
                if ($parentExists) {
                    $setting = \App\Models\EmailSmtpSetting::where('company_id', $company->created_by)
                        ->where('is_active', true)
                        ->first();
                    if ($setting) return $setting;
                }
            }
        }

        // Global SuperAdmin SMTP fallback
        return \App\Models\EmailSmtpSetting::whereNull('company_id')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Apply an SMTP setting to Laravel's runtime mail config.
     * Handles smtp driver and sendgrid (via SMTP relay).
     */
    private function applySmtpSetting(\App\Models\EmailSmtpSetting $setting): void
    {
        if ($setting->driver === 'smtp') {
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => $setting->host,
                'port'       => $setting->port,
                'encryption' => $setting->encryption === 'none' ? null : $setting->encryption,
                'username'   => $setting->username,
                'password'   => $setting->password,
            ]);
            app('mail.manager')->purge('smtp');
        } elseif ($setting->driver === 'sendgrid' && $setting->api_key) {
            // SendGrid via SMTP relay — username is always the literal string "apikey"
            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp', [
                'transport'  => 'smtp',
                'host'       => 'smtp.sendgrid.net',
                'port'       => 587,
                'encryption' => 'tls',
                'username'   => 'apikey',
                'password'   => $setting->api_key,
            ]);
            app('mail.manager')->purge('smtp');
        }

        if ($setting->from_email) {
            Config::set('mail.from.address', $setting->from_email);
            Config::set('mail.from.name', $setting->from_name ?? config('app.name'));
        }
    }
}
