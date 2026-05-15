<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Company;
use App\Models\CompanyInsightSetting;
use Illuminate\Http\Request;

class InsightLimitController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    public function updateLimitSetup(Request $request)
    {
        abort_403(!auth()->user()->can('edit_settings'));

        $request->validate([
            'default_limit'      => 'required|integer|min:1|max:99999',
            'billing_period'     => 'required|in:monthly,yearly',
            'overage_behaviour'  => 'required|in:pause,allow_overage',
        ]);

        CompanyInsightSetting::updateOrCreate(
            ['company_id' => company()->id],
            $request->only('default_limit', 'billing_period', 'overage_behaviour')
        );

        return Reply::success('Insight limit settings saved.');
    }

    public function setCustomLimit(Request $request, $companyId)
    {
        abort_403(!auth()->user()->can('edit_settings'));

        $request->validate([
            'custom_limit' => 'required|integer|min:1|max:99999',
        ]);

        $company = Company::withoutGlobalScopes()
            ->where('id', $companyId)
            ->where('created_by', company()->id)
            ->firstOrFail();

        $company->custom_insight_limit = (int) $request->custom_limit;
        $company->save();

        return Reply::success('Custom limit updated for ' . $company->company_name . '.');
    }
}
