<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'slug'    => 'user_welcome',
                'name'    => 'Welcome Email',
                'subject' => 'Welcome to {{app_name}}!',
                'body'    => '
<h2>Welcome, {{name}}!</h2>
<p>Thank you for joining <strong>{{app_name}}</strong>. Your account has been created successfully.</p>
<p><strong>Email:</strong> {{email}}</p>
<p>Click below to get started:</p>
<p><a href="{{login_url}}" style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">Log In Now</a></p>
<p>If you have any questions, contact us at {{support_email}}.</p>
<p>— The {{app_name}} Team</p>',
            ],
            [
                'slug'    => 'password_reset',
                'name'    => 'Password Reset',
                'subject' => 'Password Reset Request — {{app_name}}',
                'body'    => '
<p>Hi {{first_name}},</p>
<p>We received a request to reset your password. Click the button below to set a new password. This link is valid for 5 minutes and can only be used once.</p>
<p style="text-align:center;margin:28px 0;">
  <a href="{{reset_url}}" style="background:#2563eb;color:#fff;padding:14px 32px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;font-size:15px;">Reset My Password</a>
</p>
<p>If you did not request a password reset, please ignore this email — your account remains secure, and no changes have been made.</p>
<p style="color:#64748b;font-size:13px;">For security, never share this link with anyone. Our team will never ask for your password.</p>
<p>Regards,<br>{{app_name}}</p>',
            ],
            [
                'slug'    => 'password_changed',
                'name'    => 'Password Changed Confirmation',
                'subject' => 'Your Password Has Been Changed — {{app_name}}',
                'body'    => '
<p>Hi {{first_name}},</p>
<p>This is a confirmation that your password was successfully changed on {{changed_at}}.</p>
<p>If you made this change, no further action is required.</p>
<p>If you did not make this change, please contact our support team immediately at <a href="mailto:{{firm_email}}">{{firm_email}}</a> so we can protect your account.</p>
<p>Regards,<br>{{app_name}}</p>',
            ],
            [
                'slug'    => 'payment_success',
                'name'    => 'Payment Successful',
                'subject' => 'Payment Confirmed — {{app_name}}',
                'body'    => '
<h2>Payment Successful ✓</h2>
<p>Hi {{name}},</p>
<p>Your payment of <strong>{{amount}}</strong> has been received for the <strong>{{plan_name}}</strong> plan.</p>
<p>Your subscription is active until <strong>{{period_end}}</strong>.</p>
<p><a href="{{login_url}}" style="background:#16a34a;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">Access Dashboard</a></p>
<p>— The {{app_name}} Team</p>',
            ],
            [
                'slug'    => 'payment_failed',
                'name'    => 'Payment Failed',
                'subject' => 'Action Required: Payment Failed — {{app_name}}',
                'body'    => '
<h2>Payment Failed</h2>
<p>Hi {{name}},</p>
<p>We were unable to process your payment for the <strong>{{plan_name}}</strong> plan.</p>
<p>Please update your payment method to continue using {{app_name}}.</p>
<p><a href="{{billing_url}}" style="background:#dc2626;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">Update Payment Method</a></p>
<p>— The {{app_name}} Team</p>',
            ],
            [
                'slug'    => 'subscription_canceled',
                'name'    => 'Subscription Canceled',
                'subject' => 'Your {{app_name}} subscription has been canceled',
                'body'    => '
<h2>Subscription Canceled</h2>
<p>Hi {{name}},</p>
<p>Your <strong>{{plan_name}}</strong> subscription has been canceled. You will retain access until <strong>{{ends_at}}</strong>.</p>
<p>We would love to have you back.</p>
<p><a href="{{login_url}}" style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">Reactivate Subscription</a></p>
<p>— The {{app_name}} Team</p>',
            ],
            [
                'slug'    => 'two_factor_code',
                'name'    => 'Two-Factor Authentication Code',
                'subject' => 'Your {{app_name}} Verification Code',
                'body'    => '
<h2>Your Verification Code</h2>
<p>Hi {{name}},</p>
<p>Your one-time verification code is:</p>
<p style="font-size:40px;font-weight:700;letter-spacing:12px;text-align:center;color:#1e293b;padding:16px 0;">{{code}}</p>
<p style="text-align:center;color:#64748b;font-size:13px;">This code expires in <strong>10 minutes</strong>. Never share it with anyone.</p>
<p>— The {{app_name}} Team</p>',
            ],
            [
                'slug'    => 'document_shared',
                'name'    => 'Document Shared With You',
                'subject' => '{{sender_name}} shared a document with you',
                'body'    => '
<h2>Document Shared</h2>
<p>Hi {{name}},</p>
<p><strong>{{sender_name}}</strong> has shared a document with you:</p>
<p style="background:#f1f5f9;padding:12px 16px;border-radius:6px;border-left:4px solid #2563eb;"><strong>{{document_name}}</strong></p>
<p><a href="{{document_url}}" style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">View Document</a></p>
<p style="color:#64748b;font-size:13px;">This link expires in 24 hours.</p>
<p>— The {{app_name}} Team</p>',
            ],
            [
                'slug'    => 'trial_ending',
                'name'    => 'Trial Ending Soon',
                'subject' => 'Your {{app_name}} trial ends in {{days_left}} days',
                'body'    => '
<h2>Your Free Trial is Ending Soon ⏳</h2>
<p>Hi {{name}},</p>
<p>Your free trial of <strong>{{app_name}}</strong> ends in <strong>{{days_left}} day(s)</strong> on <strong>{{trial_end_date}}</strong>.</p>
<p>Upgrade now to keep all your data and continue without interruption.</p>
<p><a href="{{billing_url}}" style="background:#2563eb;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;font-weight:600;">Choose a Plan</a></p>
<p>— The {{app_name}} Team</p>',
            ],
        ];

        foreach ($templates as $tpl) {
            DB::table('email_templates')->insertOrIgnore(array_merge($tpl, [
                'company_id' => null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Email templates seeded: ' . count($templates) . ' templates.');
    }
}
