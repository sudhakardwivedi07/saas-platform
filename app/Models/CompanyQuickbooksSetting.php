<?php

namespace App\Models;

class CompanyQuickbooksSetting extends BaseModel
{
    protected $table = 'company_quickbooks_settings';

    protected $fillable = [
        'company_id',
        'client_id',
        'client_secret',
        'redirect_uri',
        'base_url',
    ];

    protected $hidden = ['client_secret'];
}
