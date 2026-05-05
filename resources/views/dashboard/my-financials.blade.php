@extends('layouts.app')
@section('page-title', 'My Financials')

@section('content')
<div id="insights-refresh-target" data-refresh="1" style="display:none"></div>

<style>
    .mf-wrap { padding: 8px 0 20px; }
    .mf-header {
        margin-bottom: 16px;
        padding: 16px 20px;
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 10px;
    }
    .mf-header h2 { margin: 0; font-size: 20px; font-weight: 700; color: #1f2937; }
    .mf-header p { margin: 6px 0 0; font-size: 13px; color: #6b7280; }
    .mf-empty {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 10px;
        padding: 24px;
    }
    .mf-empty h3 { margin: 0 0 8px; font-size: 17px; font-weight: 700; color: #111827; }
    .mf-empty p { margin: 0; font-size: 14px; color: #6b7280; line-height: 1.6; }
    .mf-empty-actions { margin-top: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
    .mf-btn {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 9px 14px; border-radius: 8px; text-decoration: none;
        font-size: 13px; font-weight: 600;
    }
    .mf-btn.primary { background: #f97316; color: #fff; border: 1px solid #f97316; }
    .mf-btn.ghost { background: #fff; color: #f97316; border: 1px solid #f97316; }
</style>

<div class="mf-wrap">
    <div class="mf-header">
        <h2>My Financials</h2>
        <p>Your KPI dashboard based on QuickBooks and analyzed insights.</p>
    </div>

    @if(!empty($showKpiDashboard) && ($kpiCompanies ?? collect())->isNotEmpty() && !empty($selectedKpiCompanyId))
        <div class="user-kpi-fullbleed">
            @include('dashboard.partials.kpi-dashboard-embed', [
                'kpiDataUrl' => $kpiDataUrl,
                'kpiDetailBaseUrl' => $kpiDetailBaseUrl,
                'companies' => $kpiCompanies,
                'selectedCompanyId' => $selectedKpiCompanyId,
            ])
        </div>
    @else
        <div class="mf-empty">
            <h3>No analyzed insights yet</h3>
            <p>
                KPI counters will appear here after your insights are generated and analyzed.
                @if(empty($isQuickbooksConnected))
                    Connect QuickBooks first, then generate insights.
                @endif
            </p>
            <div class="mf-empty-actions">
                @if(empty($isQuickbooksConnected))
                    <a href="{{ route('quickbooks.index') }}" class="mf-btn primary">Connect QuickBooks</a>
                @endif
                <a href="{{ route('firm-insights.index') }}" class="mf-btn ghost">Go to Insights</a>
            </div>
        </div>
    @endif
</div>
@endsection
