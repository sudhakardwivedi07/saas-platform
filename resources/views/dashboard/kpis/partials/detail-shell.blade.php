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
    @media (max-width: 640px) {
        .kpi-detail-metrics-row { grid-template-columns: 1fr; }
        .kpi-detail-page-head h2 { font-size: 18px; }
        .kpi-detail-page-head { padding: 10px 12px; }
        .kpi-section-card { overflow-x: auto; }
        .kpi-mini-chart { height: 180px; }
        .kpi-chart-bar-wrap { height: 130px; }
    }
    @keyframes kpiSpin { to { transform: rotate(360deg); } }
    /* Synopsis edit modal */
    .synopsis-edit-popup .swal2-title { font-size: 18px !important; padding-bottom: 0 !important; }
    .synopsis-edit-popup .swal2-html-container { margin-top: 4px !important; }
    .synopsis-edit-popup .swal2-actions { gap: 10px; }
    .synopsis-edit-popup .swal2-confirm,
    .synopsis-edit-popup .swal2-cancel { min-width: 120px; border-radius: 7px !important; font-weight: 600 !important; }
    .synopsis-tb-btn:hover { background: #f1f5f9 !important; }
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

    // Renders stored value as HTML — handles both plain text and saved rich HTML
    function renderRaw(text) {
        if (!text) return '';
        if (text.trimStart().charAt(0) === '<') return text;
        return textToHtml(text);
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
                if ($s)  { $s.innerHTML  = d.strength    ? renderRaw(d.strength)    : '<p>No data available.</p>'; __raw.strength     = d.strength    || ''; }
                if ($a)  { $a.innerHTML  = d.shortcoming ? renderRaw(d.shortcoming) : '<p>No data available.</p>'; __raw.shortcoming  = d.shortcoming || ''; }
                if ($ac) { $ac.innerHTML = d.action_plan ? renderRaw(d.action_plan) : '<p>No data available.</p>'; __raw.action_plan  = d.action_plan || ''; }
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

    var sectionTitles = { strength: 'Key Strengths', shortcoming: 'Areas of Attention', action_plan: 'Recommended Actions' };

    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest && e.target.closest('.kpi-synopsis-edit-btn');
        if (!editBtn) return;

        var key = editBtn.dataset.key;
        var currentHtml = renderRaw(__raw[key] || '');

        // Build toolbar button helper
        var BS = 'width:28px;height:28px;border:none;border-radius:5px;background:transparent;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;color:#374151;flex-shrink:0;';
        var SS = 'width:1px;height:18px;background:#e5e7eb;margin:0 4px;flex-shrink:0;display:inline-block;vertical-align:middle;';
        function svgTb(cmd, path, title) {
            return '<button type="button" class="synopsis-tb-btn" title="' + title + '" style="' + BS + '" onmousedown="event.preventDefault()" onclick="document.execCommand(\'' + cmd + '\',false,null);document.getElementById(\'synopsisEditor\').focus()">'
                + '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">' + path + '</svg></button>';
        }
        function sep() { return '<span style="' + SS + '"></span>'; }

        var toolbar = '<div style="display:flex;align-items:center;gap:2px;padding:6px 10px;border-bottom:1px solid #e5e7eb;flex-wrap:wrap;background:#fafafa;">'
            + svgTb('bold',                '<path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/>', 'Bold')
            + svgTb('italic',              '<line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/>', 'Italic')
            + svgTb('underline',           '<path d="M6 3v7a6 6 0 006 6 6 6 0 006-6V3"/><line x1="4" y1="21" x2="20" y2="21"/>', 'Underline')
            + sep()
            + svgTb('insertUnorderedList', '<line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/>', 'Bullet list')
            + svgTb('insertOrderedList',   '<line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><path d="M4 6h1v4" stroke="currentColor" fill="none"/><path d="M4 10h2" stroke="currentColor" fill="none"/>', 'Numbered list')
            + sep()
            + svgTb('justifyLeft',         '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/>', 'Align left')
            + svgTb('justifyCenter',       '<line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/>', 'Align center')
            + svgTb('justifyRight',        '<line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/>', 'Align right')
            + sep()
            + svgTb('removeFormat',        '<path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/><line x1="3" y1="21" x2="21" y2="3" stroke-width="1.8" opacity=".5"/>', 'Clear formatting')
            + '</div>';

        var editorHtml = toolbar
            + '<div id="synopsisEditor" contenteditable="true" spellcheck="false" style="min-height:160px;max-height:280px;overflow-y:auto;padding:12px 14px;font-size:13.5px;font-family:inherit;line-height:1.75;color:#1a202c;outline:none;text-align:left;"></div>';

        Swal.fire({
            title: 'Edit ' + (sectionTitles[key] || key),
            html: '<p style="font-size:13px;color:#6b7280;margin:0 0 10px;text-align:left;">Modify the AI-generated text before publishing</p>'
                + '<div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;text-align:left;">' + editorHtml + '</div>'
                + '<p style="font-size:11px;color:#9ca3af;margin:6px 0 0;text-align:left;">Press Enter for new line. Use toolbar for bold, italic, bullet points, and numbered lists.</p>',
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            cancelButtonText: 'Cancel',
            confirmButtonColor: 'var(--orange, #f06b25)',
            cancelButtonColor: '#6b7280',
            reverseButtons: true,
            showCloseButton: true,
            width: 660,
            customClass: { popup: 'synopsis-edit-popup' },
            didOpen: function () {
                var ed = document.getElementById('synopsisEditor');
                if (ed) { ed.innerHTML = currentHtml || '<p><br></p>'; setTimeout(function () { ed.focus(); }, 20); }
            },
            preConfirm: function () {
                var ed = document.getElementById('synopsisEditor');
                return ed ? ed.innerHTML : '';
            }
        }).then(function (result) {
            if (!result.isConfirmed) return;
            __raw[key] = result.value;

            Swal.fire({ title: 'Saving…', allowOutsideClick: false, didOpen: function () { Swal.showLoading(); } });

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
                    if (contentEl) contentEl.innerHTML = __raw[key] ? renderRaw(__raw[key]) : '<p>No data available.</p>';
                    Swal.fire({ icon: 'success', title: 'Saved!', text: 'Synopsis updated successfully.', timer: 1800, showConfirmButton: false });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Failed to save. Please try again.' });
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save. Please try again.' });
            });
        });
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
