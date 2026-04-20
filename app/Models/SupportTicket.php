<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends BaseModel
{
    protected $table = 'support_tickets';

    protected $fillable = [
        'company_id', 'user_id', 'subject', 'priority', 'message', 'attachment', 'notes', 'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
