<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * GlobalSetting — SuperAdmin-level platform settings singleton.
 *
 * @property int    $id
 * @property string $global_app_name
 * @property string $logo
 * @property string $favicon
 * @property string $company_email
 * @property string $timezone
 * @property string $date_format
 * @property string $locale
 * @property bool   $allow_registration
 * @property bool   $email_verification
 * @property bool   $maintenance_mode
 * @property string $currency_code
 */
class GlobalSetting extends BaseModel
{
    protected $table = 'global_settings';

    protected $fillable = [
        'global_app_name',
        'logo',
        'favicon',
        'login_background',
        'company_email',
        'company_phone',
        'address',
        'website',
        'timezone',
        'date_format',
        'time_format',
        'locale',
        'currency_code',
        'allow_registration',
        'email_verification',
        'maintenance_mode',
        'primary_color',
        'auth_css',
    ];

    protected $casts = [
        'allow_registration' => 'boolean',
        'email_verification' => 'boolean',
        'maintenance_mode'   => 'boolean',
    ];

    /**
     * Logo URL accessor.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->logo
            ? asset('storage/' . $this->logo)
            : asset('images/default-logo.png')
        );
    }

    /**
     * Favicon URL accessor.
     */
    protected function faviconUrl(): Attribute
    {
        return Attribute::get(fn () => $this->favicon
            ? asset('storage/' . $this->favicon)
            : asset('images/favicon.ico')
        );
    }
}
