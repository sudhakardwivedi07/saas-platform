<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyInsightSetting extends Model
{
    protected $table = 'company_insight_settings';

    protected $fillable = [
        'company_id',
        'default_limit',
        'billing_period',
        'overage_behaviour',
    ];
}
