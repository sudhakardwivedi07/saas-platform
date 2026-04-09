<?php

namespace App\Models;

use App\Traits\HasTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * CompanyBranding — per-tenant white-label theming data.
 * Separated from Company to allow partial updates and clean API design.
 */
class CompanyBranding extends BaseModel
{
    use HasTenant;

    protected $table = 'company_brandings';

    protected $fillable = [
        'company_id',
        'logo',
        'dark_logo',
        'favicon',
        'login_background',
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'custom_css',
        'login_heading',
        'login_subheading',
    ];

    // ─── Accessors ─────────────────────────────────────────────────────────────

    protected function logoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->logo
            ? asset('storage/' . $this->logo)
            : asset('images/default-logo.png')
        );
    }

    protected function faviconUrl(): Attribute
    {
        return Attribute::get(fn () => $this->favicon
            ? asset('storage/' . $this->favicon)
            : asset('images/favicon.ico')
        );
    }

    protected function loginBackgroundUrl(): Attribute
    {
        return Attribute::get(fn () => $this->login_background
            ? asset('storage/' . $this->login_background)
            : asset('images/default-login-bg.jpg')
        );
    }

    /**
     * Generate CSS variables string for dynamic theming injection.
     */
    public function toCssVariables(): string
    {
        return ":root {
            --primary-color: {$this->primary_color};
            --secondary-color: {$this->secondary_color};
            --accent-color: {$this->accent_color};
            --font-family: '{$this->font_family}', sans-serif;
        }";
    }
}
