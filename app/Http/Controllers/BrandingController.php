<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\CompanyBranding;
use Illuminate\Http\Request;

class BrandingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Branding & Appearance';
    }

    public function index()
    {
        $this->branding = company()->branding
            ?? new CompanyBranding(['company_id' => company()->id]);

        return view('settings.branding', $this->data);
    }

    public function update(Request $request)
    {
        abort_403(!auth()->user()->can('edit_branding'));

        $request->validate([
            'primary_color'    => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'secondary_color'  => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'accent_color'     => 'nullable|regex:/^#[0-9a-fA-F]{6}$/',
            'font_family'      => 'nullable|string|max:100',
            'custom_css'       => 'nullable|string|max:50000',
            'login_heading'    => 'nullable|string|max:255',
            'login_subheading' => 'nullable|string|max:255',
        ]);

        $branding = CompanyBranding::firstOrNew(['company_id' => company()->id]);

        foreach (['logo', 'favicon', 'login_background', 'dark_logo'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store(
                    'companies/' . company()->id . '/branding',
                    'public'
                );
                $branding->{$field} = $path;
            }
        }

        $branding->fill($request->only(
            'primary_color', 'secondary_color', 'accent_color',
            'font_family', 'custom_css', 'login_heading', 'login_subheading'
        ));
        $branding->save();

        // Bust the parent-branding cache so sub-company users see updated colors immediately
        cache()->forget('branding_parent_' . company()->id);

        $logoUrl    = $branding->logo    ? asset('storage/' . $branding->logo)    : null;
        $faviconUrl = $branding->favicon ? asset('storage/' . $branding->favicon) : null;

        return Reply::successWithData('Branding updated successfully.', [
            'logo_url'    => $logoUrl,
            'favicon_url' => $faviconUrl,
        ]);
    }
}
