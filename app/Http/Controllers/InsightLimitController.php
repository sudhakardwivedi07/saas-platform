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

    public function setBulkLimits(Request $request)
    {
        abort_403(!auth()->user()->can('edit_settings'));

        $request->validate([
            'companies'                 => 'required|array|min:1',
            'companies.*.company_id'    => 'required|integer',
            'companies.*.limit'         => 'required|integer|min:1|max:99999',
            'companies.*.period'        => 'required|in:monthly,weekly',
        ]);

        $adminId      = company()->id;
        $adminCompany = Company::withoutGlobalScopes()->find($adminId);
        $adminPlan    = $adminCompany?->activePlan();
        $adminBudget  = $adminPlan
            ? (int) ($adminPlan->max_insights_per_month ?? $adminPlan->insights_per_month ?? 0)
            : 0;

        $allClientCompanies = Company::withoutGlobalScopes()
            ->where('created_by', $adminId)
            ->get(['id', 'custom_insight_limit']);

        $validIds = $allClientCompanies->pluck('id')->map(fn ($i) => (int) $i)->all();

        // Build the incoming map: company_id => new limit
        $incoming = collect($request->companies)
            ->filter(fn ($item) => in_array((int) $item['company_id'], $validIds, true))
            ->keyBy(fn ($item) => (int) $item['company_id']);

        // Compute total allocation after this save
        if ($adminBudget > 0) {
            $totalAfterSave = $allClientCompanies->sum(function ($co) use ($incoming) {
                $coId = (int) $co->id;
                return $incoming->has($coId)
                    ? (int) $incoming[$coId]['limit']
                    : (int) ($co->custom_insight_limit ?? 0);
            });

            if ($totalAfterSave > $adminBudget) {
                return Reply::error('Total allocation (' . $totalAfterSave . ') would exceed your plan budget of ' . $adminBudget . ' insights.');
            }
        }

        foreach ($incoming as $coId => $item) {
            Company::withoutGlobalScopes()->where('id', $coId)->update([
                'custom_insight_limit' => (int) $item['limit'],
                'insight_limit_period' => $item['period'],
            ]);
        }

        return Reply::success('Insight limits saved.');
    }
}
