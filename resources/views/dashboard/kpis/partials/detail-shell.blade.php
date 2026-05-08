@extends('layouts.app')
@section('title', ($detailTitle ?? ($kpiPageTitle ?? 'KPI')) . ' — Details')
@section('page-title', ($detailTitle ?? ($kpiPageTitle ?? 'KPI')) . ' — Details')

@push('styles')
@include('dashboard.partials.kpi-dashboard-styles')
<style>
    .kpi-generic-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        font-weight: 600;
        font-size: 14px;
        color: #0788be;
    }
    .kpi-generic-back:hover { text-decoration: underline; color: #0369a1; }
    .kpi-generic-wrap { width: 100%; max-width: none; }
    .kpi-detail-page { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 10px; padding: 10px; }
    .kpi-detail-page-head { background: #0788be; color: #fff; padding: 12px 16px; border-radius: 6px; }
    .kpi-detail-breadcrumb { font-size: 10px; opacity: .9; margin-bottom: 5px; }
    .kpi-detail-page-head h2 { margin: 0; font-size: 30px; font-weight: 700; line-height: 1.1; }
    .kpi-detail-page-head p { margin: 4px 0 0; opacity: .92; font-size: 12px; }
    .kpi-detail-metrics-row { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-top: 10px; }
    .kpi-detail-metric-card { background: #fff; border: 1px solid #e5e7eb; border-top: 3px solid #18a8c9; border-radius: 8px; padding: 10px 12px; min-height: 78px; }
    .kpi-detail-metric-card .lbl { font-size: 10px; letter-spacing: .07em; font-weight: 700; color: #9ca3af; text-transform: uppercase; display: block; margin-bottom: 5px; }
    .kpi-detail-metric-card .val { font-size: 29px; font-weight: 700; color: #111827; line-height: 1.12; }
    .kpi-detail-metric-card .sub { margin-top: 3px; font-size: 11px; color: #9ca3af; }
    .kpi-detail-synopsis { margin-top: 10px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
    .kpi-detail-synopsis h3 { margin: 0 0 1px; font-size: 22px; color: #111827; line-height: 1.1; }
    .kpi-detail-synopsis p { margin: 0 0 10px; font-size: 12px; color: #9ca3af; }
    .kpi-detail-synopsis-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
    .kpi-detail-note { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; min-height: 188px; }
    .kpi-detail-note h4 { margin: 0 0 7px; font-size: 14px; font-weight: 700; color: #111827; }
    .kpi-detail-note ul { margin: 0; padding-left: 16px; color: #4b5563; font-size: 11px; line-height: 1.55; }
    .kpi-detail-note li { margin: 0 0 5px; }
    .kpi-note-strength { border-left: 3px solid #f59e0b; background: #fffef7; }
    .kpi-note-attention { border-left: 3px solid #ef4444; background: #fff7f7; }
    .kpi-note-actions { border-left: 3px solid #60a5fa; background: #f7fbff; }
    .kpi-section-card { margin-top: 10px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
    .kpi-section-card h4 { margin: 0; font-size: 22px; font-weight: 700; color: #111827; line-height: 1.1; }
    .kpi-section-card p { margin: 2px 0 8px; color: #9ca3af; font-size: 11px; }
    .kpi-mini-chart { height: 235px; border: 1px solid #f3f4f6; border-radius: 6px; padding: 8px; display: flex; gap: 12px; align-items: flex-end; overflow: hidden; }
    .kpi-chart-col { flex: 1; min-width: 0; text-align: center; }
    .kpi-chart-bar-wrap { height: 180px; display: flex; align-items: flex-end; justify-content: center; }
    .kpi-chart-bar { width: 28px; border-radius: 4px 4px 0 0; background: #0788be; min-height: 2px; }
    .kpi-chart-bar.neg { background: #f97316; }
    .kpi-chart-label { margin-top: 6px; font-size: 10px; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .kpi-detail-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .kpi-detail-table th { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: .06em; text-align: left; background: #f9fafb; border-bottom: 1px solid #e5e7eb; padding: 9px 10px; }
    .kpi-detail-table td { border-bottom: 1px solid #f3f4f6; color: #374151; padding: 9px 10px; }
    .kpi-detail-table tbody tr:last-child td { border-bottom: 0; background: #ecfdf3; font-weight: 700; }
    .kpi-explain-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .kpi-explain-card { border: 1px solid #e5e7eb; border-left: 3px solid #10b981; border-radius: 8px; background: #fff; padding: 10px; min-height: 92px; }
    .kpi-explain-card.alt { border-left-color: #f59e0b; background: #fffbeb; }
    .kpi-explain-card h5 { margin: 0 0 5px; font-size: 11px; font-weight: 700; color: #111827; text-transform: uppercase; letter-spacing: .06em; }
    .kpi-explain-card p { margin: 0; color: #6b7280; font-size: 11px; line-height: 1.45; }
    @media (max-width: 1100px) {
        .kpi-detail-page-head h2 { font-size: 23px; }
        .kpi-detail-metrics-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .kpi-detail-synopsis-grid { grid-template-columns: 1fr; }
        .kpi-explain-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<a href="{{ $kpiListBackUrl ?? route('dashboard') }}" class="kpi-generic-back">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Back to dashboard
</a>
<div id="kpiStandaloneDetail" class="kpi-generic-wrap"></div>
@endsection

@push('scripts')
@include('dashboard.partials.kpi-detail-markup-script')
<script>
(function () {
    const kpiType = @json($kpiType ?? ($detailKpiType ?? ''));
    const kpiDataUrl = @json($kpiDataUrl ?? ($detailApiRoute ?? ''));
    const companyId = @json($selectedCompanyId ?? null);
    const mount = document.getElementById('kpiStandaloneDetail');
    if (!mount || !kpiType || !kpiDataUrl) return;
    mount.innerHTML = '';
    if (window.showAppLoading) window.showAppLoading('Loading KPI details', 'Fetching the latest metric data...');
    const u = new URL(kpiDataUrl, window.location.origin);
    u.searchParams.set('company_id', String(companyId || ''));
    u.searchParams.set('kpi', kpiType);
    fetch(u.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (response) {
            if (window.hideAppLoading) window.hideAppLoading();
            if (response.success && response.kpis_unavailable) {
                mount.innerHTML = '<p class="kpi-detail-fallback">No QuickBooks connection or analyzed insights for this company yet.</p>';
                return;
            }
            if (response.success && typeof window.__kpiBuildDetailMarkup === 'function') {
                mount.innerHTML = window.__kpiBuildDetailMarkup(kpiType, response.data);
            } else {
                mount.innerHTML = '<div class="alert alert-danger">Could not load KPI details.</div>';
            }
        })
        .catch(function () {
            if (window.hideAppLoading) window.hideAppLoading();
            mount.innerHTML = '<div class="alert alert-danger">Could not load KPI details.</div>';
        });
})();
</script>
@endpush
