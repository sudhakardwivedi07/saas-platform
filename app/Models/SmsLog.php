<?php

namespace App\Models;

class SmsLog extends BaseModel
{
    protected $table = 'sms_logs';

    protected $fillable = [
        'company_id',
        'to_number',
        'message',
        'status',
        'provider_message_id',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}
