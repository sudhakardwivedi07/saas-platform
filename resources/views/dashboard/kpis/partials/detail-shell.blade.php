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
    @keyframes kpiSpin { to { transform: rotate(360deg); } }
    .kpi-synopsis-edit-area { margin-top: 2px; }
    .kpi-synopsis-edit-area textarea { width: 100%; box-sizing: border-box; font-size: 11px; font-family: inherit; color: #374151; border: 1px solid #d1d5db; border-radius: 5px; padding: 7px; resize: vertical; line-height: 1.6; }
    .kpi-edit-actions { display: flex; gap: 6px; margin-top: 6px; }
    .kpi-synopsis-save-btn { background: #0788be; color: #fff; border: none; border-radius: 5px; padding: 4px 14px; font-size: 11px; font-weight: 600; cursor: pointer; }
    .kpi-synopsis-save-btn:disabled { opacity: .6; cursor: not-allowed; }
    .kpi-synopsis-cancel-btn { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; border-radius: 5px; padding: 4px 10px; font-size: 11px; cursor: pointer; }
    #kpiDetailToast { position: fixed; bottom: 24px; right: 24px; z-index: 99999; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; opacity: 0; transform: translateY(8px); transition: opacity .2s ease, transform .2s ease; pointer-events: none; min-width: 200px; }
    #kpiDetailToast.success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
    #kpiDetailToast.error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
</style>
@endpush

@section('content')
<div id="kpiDetailToast"></div>
<a href="{{ $kpiListBackUrl ?? route('dashboard') }}" class="kpi-generic-back">
    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Back
</a>
<div id="kpiStandaloneDetail" class="kpi-generic-wrap"></div>
@endsection

@push('scripts')
@include('dashboard.partials.kpi-detail-markup-script')
<script>
(function () {
    var kpiType      = @json($kpiType ?? ($detailKpiType ?? ''));
    var kpiDataUrl   = @json($kpiDataUrl ?? ($detailApiRoute ?? ''));
    var companyId    = @json($selectedCompanyId ?? null);
    var clientUserId = @json($clientId ?? null);
    var SAVE_URL     = '{{ route("dashboard-kpi.kpi-synopsis-save") }}';
    var mount = document.getElementById('kpiStandaloneDetail');
    if (!mount || !kpiType || !kpiDataUrl) return;

    var kpiIdMap = {
        'runway-burn': 1, 'working-capital': 2, 'operating-cashflow': 3,
        'revenue': 4, 'ebitda': 5, 'margin-trends': 6,
        'ar-aging': 7, 'ap-leverage': 8, 'revenue-concentration': 9
    };

    var __raw = { strength: '', shortcoming: '', action_plan: '' };
    var __contentIds = { strength: 'kpiSynopsisStrength', shortcoming: 'kpiSynopsisAttention', action_plan: 'kpiSynopsisActions' };

    function textToHtml(text) {
        if (!text) return '';
        var lines = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n')
            .split('\n').filter(function (l) { return l.trim(); });
        if (!lines.length) return '<p>' + text + '</p>';
        return lines.map(function (l) { return '<p style="margin:0 0 6px;">' + l.trim() + '</p>'; }).join('');
    }

    function showToast(msg, type) {
        var el = document.getElementById('kpiDetailToast');
        if (!el) return;
        el.textContent = msg;
        el.className = type || 'success';
        requestAnimationFrame(function () {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        });
        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
        }, 3200);
    }

    function fetchKpiSynopsis(kpiId) {
        var loading = document.getElementById('kpiSynopsisLoading');
        var cards   = document.getElementById('kpiSynopsisCards');
        var errEl   = document.getElementById('kpiSynopsisError');
        if (!loading) return;

        var fd = new FormData();
        fd.append('kpi_id', kpiId);
        fd.append('_token', '{{ csrf_token() }}');
        if (companyId) fd.append('company_id', String(companyId));
        if (clientUserId) fd.append('client_user_id', String(clientUserId));

        fetch('{{ route("dashboard-kpi.kpi-synopsis-api") }}', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (loading) loading.style.display = 'none';
            var d = (res.success && res.data) ? res.data : null;
            if (d && d.success === true && (d.strength || d.shortcoming || d.action_plan)) {
                var $s  = document.getElementById('kpiSynopsisStrength');
                var $a  = document.getElementById('kpiSynopsisAttention');
                var $ac = document.getElementById('kpiSynopsisActions');
                if ($s)  { $s.innerHTML  = d.strength    ? textToHtml(d.strength)    : '<p>No data available.</p>'; __raw.strength     = d.strength    || ''; }
                if ($a)  { $a.innerHTML  = d.shortcoming ? textToHtml(d.shortcoming) : '<p>No data available.</p>'; __raw.shortcoming  = d.shortcoming || ''; }
                if ($ac) { $ac.innerHTML = d.action_plan ? textToHtml(d.action_plan) : '<p>No data available.</p>'; __raw.action_plan  = d.action_plan || ''; }
                if (cards) cards.style.display = '';
                if (clientUserId) {
                    document.querySelectorAll('.kpi-synopsis-edit-btn').forEach(function (b) { b.style.display = ''; });
                }
            } else {
                if (errEl) errEl.style.display = '';
            }
        })
        .catch(function () {
            if (loading) loading.style.display = 'none';
            if (errEl) errEl.style.display = '';
        });
    }

    document.addEventListener('click', function (e) {
        var editBtn   = e.target.closest && e.target.closest('.kpi-synopsis-edit-btn');
        var saveBtn   = e.target.closest && e.target.closest('.kpi-synopsis-save-btn');
        var cancelBtn = e.target.closest && e.target.closest('.kpi-synopsis-cancel-btn');

        if (editBtn) {
            var key = editBtn.dataset.key;
            var contentEl = document.getElementById(__contentIds[key]);
            if (!contentEl) return;
            var cardEl = editBtn.closest('.kpi-detail-note');
            editBtn.style.display = 'none';
            contentEl.style.display = 'none';

            var area = document.createElement('div');
            area.className = 'kpi-synopsis-edit-area';
            area.dataset.key = key;

            var ta = document.createElement('textarea');
            ta.rows = 6;
            ta.value = __raw[key] || '';

            var actDiv = document.createElement('div');
            actDiv.className = 'kpi-edit-actions';

            var sb = document.createElement('button');
            sb.className = 'kpi-synopsis-save-btn';
            sb.dataset.key = key;
            sb.textContent = 'Save';

            var cb = document.createElement('button');
            cb.className = 'kpi-synopsis-cancel-btn';
            cb.dataset.key = key;
            cb.textContent = 'Cancel';

            actDiv.appendChild(sb);
            actDiv.appendChild(cb);
            area.appendChild(ta);
            area.appendChild(actDiv);
            cardEl.appendChild(area);
            ta.focus();
        }

        if (saveBtn) {
            var key  = saveBtn.dataset.key;
            var area = saveBtn.closest('.kpi-synopsis-edit-area');
            var ta   = area ? area.querySelector('textarea') : null;
            if (!ta) return;
            __raw[key] = ta.value;

            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving…';

            var fd = new FormData();
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('company_id', String(companyId || ''));
            fd.append('kpi_type', kpiType);
            fd.append('strength',    __raw.strength);
            fd.append('shortcoming', __raw.shortcoming);
            fd.append('action_plan', __raw.action_plan);

            fetch(SAVE_URL, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.success) {
                    var contentEl = document.getElementById(__contentIds[key]);
                    if (contentEl) {
                        contentEl.innerHTML = __raw[key] ? textToHtml(__raw[key]) : '<p>No data available.</p>';
                        contentEl.style.display = '';
                    }
                    var cardEl = area.closest('.kpi-detail-note');
                    var eb = cardEl ? cardEl.querySelector('.kpi-synopsis-edit-btn[data-key="' + key + '"]') : null;
                    if (eb) eb.style.display = '';
                    area.remove();
                    showToast('✓ Synopsis saved successfully', 'success');
                } else {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Save';
                    showToast(res.message || 'Failed to save. Please try again.', 'error');
                }
            })
            .catch(function () {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save';
                showToast('Failed to save. Please try again.', 'error');
            });
        }

        if (cancelBtn) {
            var key  = cancelBtn.dataset.key;
            var area = cancelBtn.closest('.kpi-synopsis-edit-area');
            if (!area) return;
            var contentEl = document.getElementById(__contentIds[key]);
            if (contentEl) contentEl.style.display = '';
            var cardEl = area.closest('.kpi-detail-note');
            var eb = cardEl ? cardEl.querySelector('.kpi-synopsis-edit-btn[data-key="' + key + '"]') : null;
            if (eb) eb.style.display = '';
            area.remove();
        }
    });

    mount.innerHTML = '';
    if (window.showAppLoading) window.showAppLoading('Loading KPI details', 'Fetching the latest metric data...');
    var u = new URL(kpiDataUrl, window.location.origin);
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
                var kpiId = kpiIdMap[kpiType] || null;
                if (kpiId) fetchKpiSynopsis(kpiId);
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
