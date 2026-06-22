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
                'body'          => "Hi {{client_name}},\n\nYou have been invited to access {{company_name}}'s client portal.\n\nPortal URL: {{portal_url}}\n\nPlease log in using your registered email address.\n\nBest regards,\n{{partner_name}}",
            ],
            [
                'name'          => 'Share Insight with Client',
                'slug'          => 'share_insight_with_client',
                'trigger_event' => 'Share Insight with Client',
                'subject'       => 'Financial Insight Report — {{company_name}} | {{portal_name}}',
                'body'          => "Hi {{client_name}},\n\nPlease find your latest financial insight report from {{company_name}}.\n\nYou can view your report by logging into the client portal:\n{{portal_url}}\n\nBest regards,\n{{partner_name}}",
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
