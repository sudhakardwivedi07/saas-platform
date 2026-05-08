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
            '{{login_url}}'     => $company ? tenant_url($company->subdomain, 'login') : url('/login'),
            '{{support_email}}' => $company?->company_email ?? config('mail.from.address'),
            '{{date}}'          => now()->format('Y-m-d'),
        ], $extra);

        return $this->send($user->email, $templateSlug, $placeholders, $user->company_id);
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
     * Configure Laravel's mailer at runtime for per-tenant SMTP.
     */
    private function configureMailer(?int $companyId): void
    {
        $setting = \App\Models\EmailSmtpSetting::where(function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhereNull('company_id');
        })
        ->where('is_active', true)
        ->orderByRaw('company_id IS NULL ASC')
        ->first();

        if (!$setting) return;

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
        }

        if ($setting->from_email) {
            Config::set('mail.from.address', $setting->from_email);
            Config::set('mail.from.name', $setting->from_name ?? config('app.name'));
        }
    }
}
