<?php

namespace App\Models;

class EmailSmtpSetting extends BaseModel
{
    protected $table = 'email_smtp_settings';

    protected $fillable = [
        'company_id',
        'driver',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'api_key',
        'from_name',
        'from_email',
        'is_active',
    ];

    protected $hidden = ['password', 'api_key'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
