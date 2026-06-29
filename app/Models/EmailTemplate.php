<?php

namespace App\Models;

class EmailTemplate extends BaseModel
{
    protected $table = 'email_templates';

    // EmailTemplates can be global (company_id = NULL) or firm-specific (company_id = firm_id).
    // HasTenant is intentionally NOT used — resolution uses resolveFor() fallback logic.

    protected $fillable = [
        'company_id', 'name', 'slug', 'subject', 'body',
        'from_name', 'from_email', 'cc', 'bcc', 'is_active',
        'trigger_event',
    ];

    protected $casts = ['is_active' => 'boolean'];

    /**
     * Seed the two default email templates for a newly created firm.
     * Safe to call multiple times — skips any slug that already exists for the firm.
     */
    public static function seedForFirm(int $firmId): void
    {
        $defaults = [
            [
                'name'          => 'Partner User Welcome Email',
                'slug'          => 'partner_user_welcome_email',
                'trigger_event' => 'Partner User Welcome Email',
                'subject'       => "You've been invited to {{company_name}}'s portal",
                'body'          => "<p>Hi {{first_name}},</p>\n\n<p>You've been invited to access {{company_name}}'s portal. Your account has been created and is ready to use. You can sign in using the details below.</p>\n<p><strong>Role:</strong> {{role}}</p>\n\n<p><strong>Your login details:</strong></p>\n<ul>\n  <li><strong>Portal:</strong> <a href=\"{{portal_url}}\">{{portal_url}}</a></li>\n  <li><strong>Email:</strong> {{email}}</li>\n  <li><strong>Temporary Password:</strong> {{password}}</li>\n</ul>\n\n<p>Best regards,<br>\n{{company_name}}</p>",
            ],
            [
                'name'          => 'Share Insight with Client',
                'slug'          => 'share_insight_with_client',
                'trigger_event' => 'Share Insight with Client',
                'subject'       => 'Financial Insight Report — {{company_name}} | {{generated_date}}',
                'body'          => "<p>Your financial insight report for {{company_name}} is ready. Please find the report attached to this email.</p>\n\n<p><strong>Report Details</strong></p>\n\n<ul>\n<li><strong>Report:</strong> {{insight_title}}</li>\n\n<li><strong>Prepared by:</strong> {{cfo_name}}, {{firm_name}}</li>\n\n<li><strong>Period:</strong> {{report_period}}</li>\n\n<li><strong>Date:</strong> {{generated_date}}</li>\n</ul>\n\n<p>If you have any questions or would like to discuss the findings, feel free to reach out.</p>\n\n<p>Best regards, <br>\n{{firm_name}}</p>",
            ],
        ];

        foreach ($defaults as $tpl) {
            if (self::where('company_id', $firmId)->where('slug', $tpl['slug'])->exists()) {
                continue;
            }

            self::create([
                'company_id'    => $firmId,
                'name'          => $tpl['name'],
                'slug'          => $tpl['slug'],
                'trigger_event' => $tpl['trigger_event'],
                'subject'       => $tpl['subject'],
                'body'          => $tpl['body'],
                'is_active'     => true,
            ]);
        }
    }

    /**
     * Find the best template for a tenant: tenant-specific first, then global fallback.
     */
    public static function resolveFor(string $slug, ?int $companyId = null): ?self
    {
        return self::where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                  ->orWhereNull('company_id');
            })
            ->orderByRaw('company_id IS NULL ASC') // tenant-specific wins
            ->first();
    }
}
