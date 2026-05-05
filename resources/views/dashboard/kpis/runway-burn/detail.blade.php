@extends('layouts.app')
@section('title', 'Runway & Burn Details')
@section('page-title', 'Runway & Burn Details')

@push('styles')
<style>
    .detail-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .detail-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .metric-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
    }

    .metric-card .value {
        font-size: 32px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }

    .metric-card .label {
        font-size: 14px;
        color: #6b7280;
        font-weight: 500;
    }

    .chart-container {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #1f2937;
    }
</style>
@endpush

@section('content')

{{-- Back Button --}}
<a href="{{ $kpiListBackUrl ?? route('clients.kpi-dashboard', $clientId) }}" class="back-btn">
    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
    </svg>
    Back to KPI Dashboard
</a>

<div class="detail-container">
    <div class="detail-header">
        <h1>Runway & Burn Analysis</h1>
        <div id="companySelector">
            <!-- Company selector will be populated by JS -->
        </div>
    </div>

    <div id="detailContent">
        <div class="loading-spinner">
            <div class="spinner-border" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p>Loading runway & burn details...</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const kpiDataUrl = @json($kpiDataUrl ?? route('clients.kpi-data', $clientId));
    const selectedCompanyId = @json($selectedCompanyId);

    function loadCompanySelector() {
        // Reserved for future company switcher on detail page
    }

    async function loadRunwayBurnDetails() {
        const wrap = document.getElementById('detailContent');
        if (!wrap) return;

        wrap.innerHTML = `
        <div class="detail-metrics">
            <div class="metric-card">
                <div class="value" id="runwayMonths">—</div>
                <div class="label">Runway (Months)</div>
            </div>
            <div class="metric-card">
                <div class="value" id="cashBalance">$—</div>
                <div class="label">Current Cash Balance</div>
            </div>
            <div class="metric-card">
                <div class="value" id="monthlyBurn">$—</div>
                <div class="label">Monthly Burn Rate</div>
            </div>
            <div class="metric-card">
                <div class="value" id="burnType">—</div>
                <div class="label">Burn Type</div>
            </div>
        </div>
        <div class="chart-container">
            <div class="chart-title">Cash Burn Trend (Last 12 Months)</div>
            <canvas id="burnTrendChart" width="400" height="200"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">Runway Projection</div>
            <canvas id="runwayProjectionChart" width="400" height="200"></canvas>
        </div>`;

        try {
            const u = new URL(kpiDataUrl, window.location.origin);
            u.searchParams.set('company_id', String(selectedCompanyId ?? ''));
            u.searchParams.set('kpi', 'runway-burn');
            const r = await fetch(u.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const response = await r.json();
            if (response.success) {
                updateRunwayBurnDetails(response.data);
            } else {
                wrap.innerHTML = '<div class="alert alert-danger">Failed to load data</div>';
            }
        } catch (e) {
            console.error('Error loading runway burn details:', e);
            wrap.innerHTML = '<div class="alert alert-danger">Failed to load data</div>';
        }
    }

    function setText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function updateRunwayBurnDetails(data) {
        setText('runwayMonths', data.runway_months ?? '—');
        setText('cashBalance', data.cash_balance ?? '—');
        setText('monthlyBurn', data.monthly_burn ?? '—');
        setText('burnType', data.burn_type === 'burn' ? 'Burning Cash' : 'Building Cash');
    }

    document.addEventListener('DOMContentLoaded', function () {
        loadCompanySelector();
        loadRunwayBurnDetails();
    });
})();
</script>
@endpush

@endsection