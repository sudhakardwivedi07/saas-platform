<?php

namespace App\Http\Controllers;

use App\Models\SmtpSetting;
use App\Models\SmsSetting;
use App\Models\TenantPaymentGateway;
use App\Models\ClientPackage;

class SettingsController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
        $this->pageTitle = 'Settings';
    }

    public function index()
    {
        $companyId = auth()->user()->company_id;

        $this->smtpSetting    = SmtpSetting::where('company_id', $companyId)->first();
        $this->smsSetting     = SmsSetting::where('company_id', $companyId)->first();
        $this->paymentGateway = TenantPaymentGateway::where('company_id', $companyId)->first();
        $this->clientPackages = ClientPackage::forCompany($companyId)->orderBy('sort')->get();
        $this->branding       = company()?->branding;

        return view('settings.index', $this->data);
    }
}
