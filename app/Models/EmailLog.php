<?php

namespace App\Models;

class EmailLog extends BaseModel
{
    protected $table = 'email_logs';

    protected $fillable = [
        'company_id',
        'template_id',
        'to_email',
        'subject',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
