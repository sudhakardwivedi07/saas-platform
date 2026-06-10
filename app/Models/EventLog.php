<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EventLog extends BaseModel
{
    public $timestamps = false;

    protected $table = 'event_logs';

    protected $fillable = [
        'company_id',
        'user_id',
        'event_type',
        'description',
        'metadata',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convenience method to record an audit event.
     */
    public static function record(string $type, string $description, array $meta = []): void
    {
        try {
            $currentCompany = company();

            self::create([
                'company_id'  => $currentCompany ? $currentCompany->getAttribute('id') : null,
                'user_id'     => Auth::id(),
                'event_type'  => $type,
                'description' => $description,
                'metadata'    => $meta,
                'ip_address'  => request()->ip(),
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('EventLog::record failed: ' . $e->getMessage());
        }
    }

    public function getDisplayEventAttribute(): string
    {
        $eventType = Str::lower((string) $this->event_type);
        if ($eventType === '') {
            return 'Activity';
        }

        $explicitMap = [
            'login'                    => 'Login',
            'logout'                   => 'Logout',
            'quickbooks_connected'     => 'QuickBooks Connected',
            'quickbooks_reconnected'   => 'QuickBooks Reconnected',
            'quickbooks_disconnected'  => 'QuickBooks Disconnected',
            'ticket_created'           => 'Ticket Created',
            'ticket_updated'           => 'Ticket Updated',
            'ticket_deleted'           => 'Ticket Deleted',
            'insight_generated'        => 'Insight Generated',
            'insight_saved'            => 'Insight Saved',
            'insight_published'        => 'Insight Published',
            'ticket_status_changed'    => 'Ticket Status Changed',
            'subscription_canceled'    => 'Subscription Canceled',
            'subscription_reactivated' => 'Subscription Reactivated',
            'account_settings_updated' => 'Account Settings Updated',
            'account_profile_updated'  => 'Profile Updated',
            'account_password_changed' => 'Password Changed',
            'company_registered'       => 'Company Registered',
            'users_created'            => 'User Created',
            'users_updated'            => 'User Updated',
            'users_deleted'            => 'User Deleted',
            'users_status_changed'     => 'User Status Changed',
        ];

        if (isset($explicitMap[$eventType])) {
            return $explicitMap[$eventType];
        }

        $parts = collect(explode('_', $eventType))->filter()->values();
        if ($parts->count() < 2) {
            return Str::title(str_replace('_', ' ', $eventType));
        }

        $action = $parts->last();
        $resource = $parts->slice(0, $parts->count() - 1)->implode('_');

        $resourceLabelMap = [
            'support' => 'Ticket',
            'ticket' => 'Ticket',
            'settings_roles' => 'Role',
            'settings_branding' => 'Branding',
            'settings_email' => 'Email Settings',
            'settings_sms' => 'SMS Settings',
            'settings_payment_gateway' => 'Payment Gateway',
            'my_companies' => 'Company',
            'clients' => 'Client',
            'users' => 'User',
            'documents' => 'Document',
            'plans' => 'Plan',
            'profile' => 'Profile',
            'account_password' => 'Password',
            'account_profile' => 'Profile',
        ];

        $actionLabelMap = [
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'submitted' => 'Submitted',
            'uploaded' => 'Uploaded',
            'changed' => 'Changed',
            'connected' => 'Connected',
            'reconnected' => 'Reconnected',
            'disconnected' => 'Disconnected',
            'assign' => 'Assigned',
            'assigned' => 'Assigned',
            'status' => 'Status Updated',
        ];

        $resourceLabel = $resourceLabelMap[$resource] ?? Str::title(str_replace('_', ' ', $resource));
        $actionLabel = $actionLabelMap[$action] ?? Str::title(str_replace('_', ' ', $action));

        return trim($resourceLabel . ' ' . $actionLabel);
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $description = trim((string) $this->description);
        if ($description === '') {
            return $this->display_event;
        }

        if (preg_match('/\b(store|update|destroy|delete|toggle|callback|connect|disconnect|sync)\b/i', $description)) {
            return $this->display_event;
        }

        return $description;
    }
}
