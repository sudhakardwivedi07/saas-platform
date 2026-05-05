@extends('layouts.app')
@section('title', ($kpiPageTitle ?? 'KPI') . ' — Details')
@section('page-title', ($kpiPageTitle ?? 'KPI') . ' — Details')

@push('styles')
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
    .kpi-generic-wrap { width: 100%; max-width: 960px; }
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
    const kpiType = @json($kpiType ?? '');
    const kpiDataUrl = @json($kpiDataUrl ?? '');
    const companyId = @json($selectedCompanyId ?? null);
    const mount = document.getElementById('kpiStandaloneDetail');
    if (!mount || !kpiType || !kpiDataUrl) return;
    mount.innerHTML = '<div class="kpi-detail-loading">Loading…</div>';
    const u = new URL(kpiDataUrl, window.location.origin);
    u.searchParams.set('company_id', String(companyId || ''));
    u.searchParams.set('kpi', kpiType);
    fetch(u.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function (r) { return r.json(); })
        .then(function (response) {
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
            mount.innerHTML = '<div class="alert alert-danger">Could not load KPI details.</div>';
        });
})();
</script>
@endpush
