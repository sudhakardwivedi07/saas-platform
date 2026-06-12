{{--
  Reusable KPI grid + loaders. Expects:
  - $kpiDataUrl (string)
  - $kpiDetailBaseUrl (string, no trailing slash) e.g. url('/dashboard/kpi-detail')
  - $companies (Collection of Company)
  - $selectedCompanyId (int|null)
--}}
@once
@push('styles')
@include('dashboard.partials.kpi-dashboard-styles')
@endpush
@endonce

@php
    $__kpiCompanies = $companies ?? collect();
    $__sel = $selectedCompanyId ?? $__kpiCompanies->first()?->id;
@endphp

<div class="kpi-container">
 
    <div id="kpiDashboard"></div>

    <div id="kpiDetailDrawer" class="kpi-detail-drawer-root" hidden aria-hidden="true">
        <div class="kpi-detail-drawer-backdrop" data-kpi-drawer-close tabindex="-1"></div>
        <div class="kpi-detail-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="kpiDetailDrawerTitle">
            <div class="kpi-detail-drawer-head">
                <h2 id="kpiDetailDrawerTitle" class="kpi-detail-drawer-title">KPI details</h2>
                <button type="button" class="kpi-detail-drawer-close" data-kpi-drawer-close aria-label="Close">&times;</button>
            </div>
            <div class="kpi-detail-drawer-actions">
                <button type="button" class="kpi-detail-open-page" id="kpiDetailOpenFull">Open full page</button>
            </div>
            <div id="kpiDetailDrawerBody" class="kpi-detail-drawer-body"></div>
        </div>
    </div>
</div>

@once
@push('scripts')
@include('dashboard.partials.kpi-detail-markup-script')
<script>
(function () {
    const KPI_DATA_URL       = @json($kpiDataUrl);
    const KPI_DETAIL_BASE    = @json(rtrim($kpiDetailBaseUrl, '/'));
    const CLIENT_HIDDEN_KPIS = @json(array_values($clientHiddenKpis ?? []));
    // Forward _imp token so impersonation AJAX requests authenticate as the client, not the admin.
    const _IMP_TOKEN = new URLSearchParams(window.location.search).get('_imp');
    // Build detail routes from the context-aware base URL so admin view uses /clients/{id}/kpi-detail/...
    const KPI_DETAIL_ROUTES = (function (base) {
        return {
            'runway-burn':           base + '/runway-burn',
            'working-capital':       base + '/working-capital',
            'operating-cashflow':    base + '/operating-cashflow',
            'revenue':               base + '/revenue',
            'ebitda':                base + '/ebitda',
            'margin-trends':         base + '/margin-trends',
            'ar-aging':              base + '/ar-aging',
            'ap-leverage':           base + '/ap-leverage',
            'revenue-concentration': base + '/revenue-concentration',
        };
    }(KPI_DETAIL_BASE));
    let selectedCompanyId = @json($__sel);

    const KPI_DETAIL_TITLES = {
        'runway-burn': 'Runway & Burn',
        'working-capital': 'Working Capital',
        'operating-cashflow': 'Operating Cash Flow',
        'revenue': 'Revenue',
        'ebitda': 'EBITDA',
        'margin-trends': 'Margin Trends',
        'ar-aging': 'AR Aging',
        'ap-leverage': 'AP Leverage',
        'revenue-concentration': 'Revenue Concentration',
    };

    function kpiDashEl() { return document.getElementById('kpiDashboard'); }

    let kpiNavBound = false;
    let kpiDrawerUiBound = false;
    let currentDetailKpi = null;

    function bindKpiCardNav() {
        const dash = kpiDashEl();
        if (!dash || kpiNavBound) return;
        kpiNavBound = true;
        dash.addEventListener('click', function (e) {
            if (e.target.closest('.kpi-rc-info')) return;
            const c = e.target.closest('[data-kpi-card]');
            if (!c || !dash.contains(c)) return;
            if (e.target.closest('a,button,input,select,textarea')) return;
            window.__kpiOpenFullPage(c.getAttribute('data-kpi-card'));
        });
        dash.addEventListener('keydown', function (e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            const c = e.target.closest('[data-kpi-card]');
            if (!c || !dash.contains(c) || c !== e.target) return;
            e.preventDefault();
            window.__kpiOpenFullPage(c.getAttribute('data-kpi-card'));
        });
    }

    function bindKpiDrawerUi() {
        const root = document.getElementById('kpiDetailDrawer');
        if (!root || kpiDrawerUiBound) return;
        kpiDrawerUiBound = true;
        root.addEventListener('click', function (e) {
            if (e.target.closest('[data-kpi-drawer-close]')) closeKpiDetailDrawer();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && root.classList.contains('is-open')) closeKpiDetailDrawer();
        });
        const fullBtn = document.getElementById('kpiDetailOpenFull');
        if (fullBtn) {
            fullBtn.addEventListener('click', function () {
                if (window.__kpiOpenFullPage) window.__kpiOpenFullPage();
            });
        }
    }

    function openKpiDetailDrawer(kpiType) {
        if (!kpiType) return;
        currentDetailKpi = kpiType;
        bindKpiDrawerUi();
        const root = document.getElementById('kpiDetailDrawer');
        const body = document.getElementById('kpiDetailDrawerBody');
        const title = document.getElementById('kpiDetailDrawerTitle');
        if (!root || !body || !title) return;
        title.textContent = KPI_DETAIL_TITLES[kpiType] || kpiType;
        body.innerHTML = '';
        root.hidden = false;
        root.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(function () { root.classList.add('is-open'); });
        document.body.style.overflow = 'hidden';
        if (window.showAppLoading) window.showAppLoading('Loading KPI details', 'Fetching the latest metric data...');

        const u = new URL(KPI_DATA_URL, window.location.origin);
        u.searchParams.set('company_id', String(selectedCompanyId ?? ''));
        u.searchParams.set('kpi', kpiType);
        if (_IMP_TOKEN) u.searchParams.set('_imp', _IMP_TOKEN);
        fetch(u.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (response) {
                if (window.hideAppLoading) window.hideAppLoading();
                if (response.success && response.kpis_unavailable) {
                    body.innerHTML = '<p class="kpi-detail-fallback">No QuickBooks connection or analyzed insights for this company yet.</p>';
                    return;
                }
                if (response.success && typeof window.__kpiBuildDetailMarkup === 'function') {
                    body.innerHTML = window.__kpiBuildDetailMarkup(kpiType, response.data);
                } else {
                    body.innerHTML = '<div class="alert alert-danger">Could not load KPI details.</div>';
                }
            })
            .catch(function () {
                if (window.hideAppLoading) window.hideAppLoading();
                body.innerHTML = '<div class="alert alert-danger">Could not load KPI details.</div>';
            });
    }

    function closeKpiDetailDrawer() {
        const root = document.getElementById('kpiDetailDrawer');
        if (!root) return;
        root.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(function () {
            if (!root.classList.contains('is-open')) {
                root.hidden = true;
                root.setAttribute('aria-hidden', 'true');
            }
        }, 320);
    }

    window.__kpiOpenFullPage = function (kpiType) {
        if (kpiType) {
            currentDetailKpi = kpiType;
        }
        if (!currentDetailKpi) return;
        const params = new URLSearchParams(window.location.search);
        if (selectedCompanyId) params.set('company_id', selectedCompanyId);
        const q = params.toString();
        const detailRoute = KPI_DETAIL_ROUTES[currentDetailKpi] || (KPI_DETAIL_BASE + '/' + encodeURIComponent(currentDetailKpi));
        window.location.href = detailRoute + (q ? '?' + q : '');
    };

    window.__kpiEmbedChangeCompany = function (companyId) {
        const id = parseInt(String(companyId), 10);
        selectedCompanyId = Number.isFinite(id) && id > 0 ? id : null;
        closeKpiDetailDrawer();
        loadKpiDashboard();
    };

    function loadKpiDashboard() {
        const dashboardContainer = kpiDashEl();
        if (!dashboardContainer) return;
        dashboardContainer.innerHTML = `
        <div class="kpi-dashboard-root">
        <div class="kpi-tier-block">
            <div class="kpi-tier-section-title">Survival metrics</div>
            <div class="survival-metrics">
            <div class="metrics-grid">
                <div class="kpi-card" data-kpi-card="runway-burn" tabindex="0" role="button" aria-label="Open Runway and Burn details">
                    <div class="kpi-card-inner">
                        <div class="kpi-header">
                            <div class="kpi-left">
                                <div class="metric-label">Runway &amp; Burn</div>
                                <div class="kpi-hero-value" data-hero><span class="kpi-hero-num">—</span><span class="unit" data-runway-unit>Months</span></div>
                                <div class="kpi-left-meta"><span class="status-badge warning-zone"><i class="fa fa-info-circle"></i> —</span></div>
                            </div>
                            <div class="kpi-right">
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Cash balance</span><span class="kpi-mini-value" data-m="rb-cash">$—</span></div>
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Net burn (3M avg)</span><span class="kpi-mini-value" data-m="rb-burn">$—</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-card-footer">
                        <div class="runway-bar" data-rb-bar style="--runway-percent:0%;"></div>
                        <div class="runway-scale"><span>0 Month</span><span>12 Months (Target)</span><span>24 Months</span></div>
                    </div>
                </div>
                <div class="kpi-card" data-kpi-card="working-capital" tabindex="0" role="button" aria-label="Open Working Capital details">
                    <div class="kpi-card-inner">
                        <div class="kpi-header">
                            <div class="kpi-left">
                                <div class="metric-label">Working Capital</div>
                                <div class="kpi-hero-value" data-hero>$—</div>
                                <div class="kpi-left-meta"><span class="kpi-mom-pill" data-wc-mom>— vs last month</span></div>
                            </div>
                            <div class="kpi-right">
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Current assets</span><span class="kpi-mini-value" data-m="wc-ca">$—</span></div>
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Current liabilities</span><span class="kpi-mini-value" data-m="wc-cl">$—</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-card-footer">
                        <div class="kpi-wc-dual">
                            <div class="kpi-wc-dual-row">
                                <div class="kpi-wc-dual-top"><span>Current assets</span><span class="kpi-wc-amt" data-wc-assets-lbl>—</span></div>
                                <div class="kpi-wc-track assets" data-wc-assets-track><span></span></div>
                            </div>
                            <div class="kpi-wc-dual-row">
                                <div class="kpi-wc-dual-top"><span>Current liabilities</span><span class="kpi-wc-amt" data-wc-liab-lbl>—</span></div>
                                <div class="kpi-wc-track liabilities" data-wc-liab-track><span></span></div>
                            </div>
                        </div>
                        <p class="kpi-footer-desc">Current assets vs current liabilities (relative scale).</p>
                    </div>
                </div>
                <div class="kpi-card" data-kpi-card="operating-cashflow" tabindex="0" role="button" aria-label="Open Operating Cash Flow details">
                    <div class="kpi-card-inner">
                        <div class="kpi-header">
                            <div class="kpi-left">
                                <div class="metric-label">Operating cash flow (L12M)</div>
                                <div class="kpi-hero-value" data-hero>$—</div>
                                <div class="kpi-left-meta"><span class="kpi-mom-pill">L12M trend</span></div>
                            </div>
                            <div class="kpi-right">
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Data points</span><span class="kpi-mini-value" data-m="ocf-n">—</span></div>
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Peak (12 mo)</span><span class="kpi-mini-value" data-m="ocf-peak">$—</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-card-footer">
                        <div class="kpi-ocf-bars" data-ocf-bars></div>
                        <p class="kpi-footer-desc">Trailing monthly operating cash flow (last 12 months).</p>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="kpi-tier-block">
            <div class="kpi-tier-section-title">Performance &amp; profitability</div>
            <div class="survival-metrics">
            <div class="metrics-grid">
                <div class="kpi-card" data-kpi-card="revenue" tabindex="0" role="button" aria-label="Open Revenue details">
                    <div class="kpi-card-inner">
                        <div class="kpi-header">
                            <div class="kpi-left">
                                <div class="metric-label">MRR / Revenue</div>
                                <div class="kpi-hero-value" data-hero>$—</div>
                                <div class="kpi-left-meta"><span class="kpi-mom-pill" data-rev-pill>YoY —</span></div>
                            </div>
                            <div class="kpi-right">
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">YoY growth</span><span class="kpi-mini-value" data-m="rev-yoy">—</span></div>
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Trend months</span><span class="kpi-mini-value" data-m="rev-n">—</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-card-footer">
                        <div class="kpi-mini-chart-wrap" data-rev-chart></div>
                        <div class="kpi-rev-legend"><span><i class="cy"></i> Current year</span><span><i class="ly"></i> Last year</span></div>
                        <p class="kpi-footer-desc">Recognized revenue trend.</p>
                    </div>
                </div>
                <div class="kpi-card" data-kpi-card="ebitda" tabindex="0" role="button" aria-label="Open EBITDA details">
                    <div class="kpi-card-inner">
                        <div class="kpi-header">
                            <div class="kpi-left">
                                <div class="metric-label metric-label--blue">EBITDA</div>
                                <div class="kpi-hero-value" data-hero>$—</div>
                                <div class="kpi-left-meta"><span class="kpi-mom-pill" data-ebitda-pill>—</span></div>
                            </div>
                            <div class="kpi-right">
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Margin</span><span class="kpi-mini-value kpi-mini-value--accent" data-m="ebitda-margin">—</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-card-footer">
                        <div class="kpi-ebitda-bars" data-ebitda-chart></div>
                        <p class="kpi-footer-desc">EBITDA by month (last 12).</p>
                    </div>
                </div>
                <div class="kpi-card" data-kpi-card="margin-trends" tabindex="0" role="button" aria-label="Open Margin Trends details">
                    <div class="kpi-card-inner">
                        <div class="kpi-header">
                            <div class="kpi-left">
                                <div class="metric-label metric-label--caps">Margin trends</div>
                                <div class="kpi-hero-value" data-hero>—%</div>
                                <div class="kpi-left-meta"><span class="kpi-mom-pill" data-mt-pill>—</span></div>
                            </div>
                            <div class="kpi-right">
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Net margin</span><span class="kpi-mini-value" data-m="mt-net">—</span></div>
                                <div class="kpi-mini-stat"><span class="kpi-mini-label">Periods</span><span class="kpi-mini-value" data-m="mt-n">—</span></div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-card-footer">
                        <div class="kpi-rev-legend kpi-mt-legend" style="margin-bottom:6px">
                            <span><i class="cy"></i> Gross: <strong data-mt-lg-gross>—</strong></span>
                            <span><i class="mt-legend-net"></i> Net: <strong data-mt-lg-net>—</strong></span>
                        </div>
                        <div class="kpi-mt-chart" data-mt-chart></div>
                        <p class="kpi-footer-desc">Gross vs net margin by month.</p>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="kpi-tier-block">
            <div class="kpi-tier-section-title">Efficiency (AR/AP)</div>
            <div class="kpi-efficiency-grid">
                <div class="kpi-card kpi-counter-card" data-kpi-card="ar-aging" tabindex="0" role="button" aria-label="Open AR Aging details">
                    <div class="kpi-card-inner kpi-counter-card-inner">
                        <div class="kpi-counter-head">
                            <span class="metric-label">AR Aging</span>
                            <span class="kpi-counter-pill" data-ar-pill>DSO: —</span>
                        </div>
                        <div class="kpi-counter-body">
                            <span class="kpi-counter-sub">Total outstanding</span>
                            <div class="kpi-hero-value kpi-counter-hero" data-hero>$—</div>
                        </div>
                    </div>
                    <div class="kpi-card-footer kpi-counter-footer"><div class="kpi-hbar-chart" data-ar-chart></div></div>
                </div>
                <div class="kpi-card kpi-counter-card" data-kpi-card="ap-leverage" tabindex="0" role="button" aria-label="Open AP Leverage details">
                    <div class="kpi-card-inner kpi-counter-card-inner">
                        <div class="kpi-counter-head">
                            <span class="metric-label">AP Leverage</span>
                            <span class="kpi-counter-pill" data-ap-pill>DPO: —</span>
                        </div>
                        <div class="kpi-counter-body">
                            <span class="kpi-counter-sub">Total payables</span>
                            <div class="kpi-hero-value kpi-counter-hero" data-hero>$—</div>
                        </div>
                    </div>
                    <div class="kpi-card-footer kpi-counter-footer"><div class="kpi-hbar-chart" data-ap-chart></div></div>
                </div>
            </div>
        </div>
        <div class="kpi-tier-block">
            <div class="kpi-tier-section-title">Risk exposure</div>
            <div class="kpi-risk-grid">
                <div class="kpi-card kpi-counter-card" data-kpi-card="revenue-concentration" tabindex="0" role="button" aria-label="Open Revenue Concentration details">
                    <div class="kpi-card-inner kpi-counter-card-inner">
                        <div class="kpi-counter-head kpi-counter-head--rc">
                            <div>
                                <span class="kpi-rc-title kpi-rc-title--blue">Revenue Concentration</span>
                                <span class="kpi-counter-sub" data-rc-sub>Top 5 Clients % of Total ARR</span>
                            </div>
                            <span class="kpi-rc-info" title="Share of revenue (or ARR, when modeled) attributed to each of your top customers. Higher concentration increases customer risk."><i class="fa fa-info-circle"></i></span>
                        </div>
                    </div>
                    <div class="kpi-card-footer kpi-counter-footer">
                        <div class="kpi-hbar-chart kpi-rc-concentration" data-rc-chart></div>
                        <div class="kpi-rc-static-legend" aria-hidden="true">
                            <span><i class="sw-top5"></i> Top 5</span>
                            <span><i class="sw-oth"></i> Others</span>
                        </div>
                        <p class="kpi-rc-legend" data-rc-legend style="font-size:12px;font-weight:700;color:#334155;margin:10px 0 0;text-align:center"></p>
                    </div>
                </div>
            </div>
        </div>
        <p class="kpi-dash-footer-copy" style="text-align:right;font-size:11px;color:#94a3b8;margin:24px 0 0">© {{ date('Y') }} {{ config('app.name', 'CFO Insights') }}. All rights reserved.</p>
        </div>`;

        bindKpiCardNav();

        // Hide KPI cards the admin has published as hidden (client view only)
        if (CLIENT_HIDDEN_KPIS.length) {
            CLIENT_HIDDEN_KPIS.forEach(function (kpiType) {
                var card = document.querySelector('[data-kpi-card="' + kpiType + '"]');
                if (card) card.style.display = 'none';
            });
            // Hide entire tier blocks whose every card is hidden
            document.querySelectorAll('.kpi-tier-block').forEach(function (block) {
                var cards = block.querySelectorAll('[data-kpi-card]');
                if (cards.length && Array.from(cards).every(function (c) { return c.style.display === 'none'; })) {
                    block.style.display = 'none';
                }
            });
        }

        // Notify the page that KPI cards are in the DOM (used by admin edit-mode)
        if (typeof window.__kpiReadyCallback === 'function') window.__kpiReadyCallback();

        if (window.showAppLoading) window.showAppLoading('Loading KPI dashboard', 'Fetching the latest metrics...');
        loadTier1Metrics().finally(function () {
            if (window.hideAppLoading) window.hideAppLoading();
        });
    }

    function setMini(card, key, val) {
        if (!card) return;
        const el = card.querySelector('[data-m="' + key + '"]');
        if (el) el.textContent = val;
    }

    function setHeroPlain(card, text) {
        const h = card?.querySelector('[data-hero]');
        if (!h) return;
        const num = h.querySelector('.kpi-hero-num');
        if (num) num.textContent = text;
        else h.textContent = text;
    }

    function renderAgingBarChart(container, buckets) {
        if (!container) return;
        container.innerHTML = '';
        if (!buckets || !buckets.length) return;
        buckets.forEach(function (b) {
            const col = document.createElement('div');
            col.className = 'kpi-hbar-col';
            const wrap = document.createElement('div');
            wrap.className = 'kpi-hbar-bar-wrap';
            const bar = document.createElement('div');
            bar.className = 'kpi-hbar-bar';
            const scale = typeof b.bar_scale_pct === 'number' ? b.bar_scale_pct : 0;
            bar.style.height = Math.max(10, Math.min(100, scale)) + '%';
            wrap.appendChild(bar);
            const val = document.createElement('span');
            val.className = 'kpi-hbar-val';
            val.textContent = b.percentage || '—';
            const lbl = document.createElement('span');
            lbl.className = 'kpi-hbar-lbl';
            lbl.textContent = b.label || b.range || '';
            col.appendChild(wrap);
            col.appendChild(val);
            col.appendChild(lbl);
            container.appendChild(col);
        });
    }

    function renderConcentrationBarChart(container, customers) {
        if (!container) return;
        container.innerHTML = '';
        const all = customers || [];
        const top = all.filter(function (c) {
            return String(c.name || '').toLowerCase() !== 'others';
        }).slice(0, 5);
        const others = all.find(function (c) {
            return String(c.name || '').toLowerCase() === 'others';
        });
        const rows = top.map(function (c, i) {
            return { c: c, rank: i + 1, isOthers: false };
        });
        if (others) rows.push({ c: others, rank: null, isOthers: true });
        if (!rows.length) return;
        let maxShare = 0;
        rows.forEach(function (r) {
            const p = Number(r.c.share_pct);
            if (!isNaN(p)) maxShare = Math.max(maxShare, p);
        });
        if (maxShare <= 0) maxShare = 1;
        const axisMax = Math.min(100, Math.max(10, Math.ceil(maxShare / 10) * 10));
        const axis = document.createElement('div');
        axis.className = 'kpi-rc-axis';
        [axisMax, Math.round(axisMax * 0.66), Math.round(axisMax * 0.33), 0].forEach(function (v) {
            const s = document.createElement('span');
            s.textContent = v + '%';
            axis.appendChild(s);
        });

        const barsWrap = document.createElement('div');
        barsWrap.className = 'kpi-rc-bars';

        rows.forEach(function (r, idx) {
            const c = r.c;
            const col = document.createElement('div');
            col.className = 'kpi-hbar-col';
            const wrap = document.createElement('div');
            wrap.className = 'kpi-hbar-bar-wrap';
            const bar = document.createElement('div');
            bar.className = 'kpi-hbar-bar';
            const share = Number(c.share_pct);
            const scale = !isNaN(share) ? (share / axisMax) * 100 : (typeof c.bar_scale_pct === 'number' ? c.bar_scale_pct : 0);
            bar.style.height = Math.max(6, Math.min(100, scale)) + '%';
            if (r.isOthers) bar.classList.add('is-others');
            else bar.classList.add('is-accent');
            wrap.appendChild(bar);

            const val = document.createElement('span');
            val.className = 'kpi-hbar-val';
            val.textContent = c.percentage || '—';
            const lbl = document.createElement('span');
            lbl.className = 'kpi-hbar-lbl';
            lbl.textContent = r.isOthers ? 'Others' : ('Rank ' + r.rank);
            col.appendChild(wrap);
            col.appendChild(val);
            col.appendChild(lbl);
            barsWrap.appendChild(col);
        });

        const row = document.createElement('div');
        row.className = 'kpi-rc-chart-row';
        row.appendChild(axis);
        row.appendChild(barsWrap);
        container.appendChild(row);
    }

    function formatMoShort(ym) {
        if (!ym || typeof ym !== 'string') return '';
        const p = ym.split('-');
        if (p.length < 2) return ym;
        const d = new Date(parseInt(p[0], 10), parseInt(p[1], 10) - 1, 1);
        return d.toLocaleString('en-US', { month: 'short' });
    }

    function renderOcfBars(container, monthlyData) {
        if (!container) return;
        container.innerHTML = '';
        const md = (monthlyData || []).slice(-12);
        if (!md.length) return;
        const raws = md.map(function (pt) {
            if (typeof pt.raw_amount === 'number') return pt.raw_amount;
            return 0;
        });
        const mx = Math.max.apply(null, raws.map(function (r) { return Math.abs(r); }).concat([1]));
        let peakI = 0;
        let peakAbs = -1;
        raws.forEach(function (r, i) {
            const a = Math.abs(r);
            if (a >= peakAbs) {
                peakAbs = a;
                peakI = i;
            }
        });
        md.forEach(function (pt, idx) {
            const col = document.createElement('div');
            col.className = 'ocf-col';
            const wrap = document.createElement('div');
            wrap.className = 'ocf-bar-wrap';
            const bar = document.createElement('div');
            bar.className = 'ocf-bar';
            const raw = raws[idx];
            if (raw < 0) bar.classList.add('is-negative');
            else bar.classList.add('is-positive');
            const h = Math.max(6, Math.round((Math.abs(raw) / mx) * 48));
            bar.style.height = h + '%';
            if (raw >= 0) {
                bar.style.top = (50 - h) + '%';
            } else {
                bar.style.top = '50%';
            }
            wrap.appendChild(bar);
            const mo = document.createElement('span');
            mo.className = 'ocf-mo';
            mo.textContent = formatMoShort(pt.month || pt.label) || '';
            col.appendChild(wrap);
            col.appendChild(mo);
            container.appendChild(col);
        });
    }

    function renderRevenueAreaChart(container, trend) {
        if (!container) return;
        container.innerHTML = '';
        const t = (trend || []).slice(-12);
        if (!t.length) return;
        const W = 360;
        const H = 96;
        const pad = 6;
        const cy = t.map(function (r) { return Number(r.current_year) || 0; });
        const ly = t.map(function (r) { return Number(r.last_year) || 0; });
        const mx = Math.max.apply(null, cy.concat(ly).concat([1]));
        const n = t.length;
        const xstep = n > 1 ? (W - pad * 2) / (n - 1) : 0;
        function y(v) { return H - pad - (v / mx) * (H - pad * 2); }
        let dLine = '';
        let dFill = '';
        t.forEach(function (row, i) {
            const xi = pad + i * xstep;
            const yi = y(cy[i]);
            dLine += (i ? ' L' : 'M') + xi.toFixed(1) + ',' + yi.toFixed(1);
            dFill += (i ? ' L' : 'M') + xi.toFixed(1) + ',' + yi.toFixed(1);
        });
        for (let i = t.length - 1; i >= 0; i--) {
            const xi = pad + i * xstep;
            dFill += ' L' + xi.toFixed(1) + ',' + (H - pad).toFixed(1);
        }
        dFill += ' Z';
        let dDash = '';
        t.forEach(function (row, i) {
            const xi = pad + i * xstep;
            const yi = y(ly[i]);
            dDash += (i ? ' L' : 'M') + xi.toFixed(1) + ',' + yi.toFixed(1);
        });
        const gid = 'revFill' + Math.random().toString(36).slice(2, 9);
        container.innerHTML = '<svg width="' + W + '" height="' + H + '" viewBox="0 0 ' + W + ' ' + H + '" preserveAspectRatio="none" role="img" aria-label="Revenue trend"><defs><linearGradient id="' + gid + '" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f97316" stop-opacity="0.35"/><stop offset="100%" stop-color="#f97316" stop-opacity="0"/></linearGradient></defs><path d="' + dFill + '" fill="url(#' + gid + ')" stroke="none"/><path d="' + dLine + '" fill="none" stroke="#f97316" stroke-width="2.2" stroke-linejoin="round"/><path d="' + dDash + '" fill="none" stroke="#9ca3af" stroke-width="1.8" stroke-dasharray="4 4" stroke-linejoin="round"/></svg>';
    }

    function renderEbitdaMiniBars(container, bars) {
        if (!container) return;
        container.innerHTML = '';
        const arr = Array.isArray(bars) ? bars : [];
        if (!arr.length) return;
        const mx = Math.max.apply(null, arr.concat([1]));
        arr.forEach(function (v) {
            const col = document.createElement('div');
            col.className = 'eb-col';
            const bar = document.createElement('div');
            bar.className = 'eb-bar';
            bar.style.height = Math.max(12, Math.round((v / mx) * 100)) + '%';
            col.appendChild(bar);
            container.appendChild(col);
        });
    }

    function renderMarginDots(container, monthlyData) {
        if (!container) return;
        container.innerHTML = '';
        const md = (monthlyData || []).slice(-12);
        if (!md.length) return;
        md.forEach(function (row) {
            const g = row.gross_margin != null ? Number(row.gross_margin) : NaN;
            const n = row.net_margin != null ? Number(row.net_margin) : NaN;
            const gb = Math.min(100, Math.max(0, isNaN(g) ? 0 : g));
            const nb = Math.min(100, Math.max(0, isNaN(n) ? 0 : n));
            const col = document.createElement('div');
            col.className = 'mt-col';
            const plot = document.createElement('div');
            plot.className = 'mt-plot';
            const dg = document.createElement('span');
            dg.className = 'mt-dot-pos is-gross';
            dg.innerHTML = '<span class="mt-dot gross"></span>';
            dg.style.bottom = gb + '%';
            const dn = document.createElement('span');
            dn.className = 'mt-dot-pos is-net';
            dn.innerHTML = '<span class="mt-dot net"></span>';
            dn.style.bottom = nb + '%';
            plot.appendChild(dg);
            plot.appendChild(dn);
            const lbl = document.createElement('span');
            lbl.className = 'mt-lbl';
            lbl.textContent = formatMoShort(row.month) || '';
            col.appendChild(plot);
            col.appendChild(lbl);
            container.appendChild(col);
        });
    }

    function loadTier1Metrics() {
        return Promise.all([
            loadKpiData('runway-burn'),
            loadKpiData('working-capital'),
            loadKpiData('operating-cashflow')
        ]).then(() => loadTier2Metrics()).catch(() => showError('Failed to load dashboard data'));
    }

    function loadTier2Metrics() {
        return Promise.all([
            loadKpiData('revenue'),
            loadKpiData('ebitda'),
            loadKpiData('margin-trends')
        ]).then(() => loadTier3Metrics()).catch(() => {});
    }

    function loadTier3Metrics() {
        return Promise.all([
            loadKpiData('ar-aging'),
            loadKpiData('ap-leverage'),
            loadKpiData('revenue-concentration')
        ]).catch(() => {});
    }

    async function loadKpiData(kpiType) {
        try {
            const u = new URL(KPI_DATA_URL, window.location.origin);
            u.searchParams.set('company_id', String(selectedCompanyId ?? ''));
            u.searchParams.set('kpi', kpiType);
            if (_IMP_TOKEN) u.searchParams.set('_imp', _IMP_TOKEN);
            const r = await fetch(u.toString(), { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!r.ok) return;
            const response = await r.json();
            if (!response.success) return;
            if (response.kpis_unavailable) {
                resetKpiCardVisual(kpiType);
                return;
            }
            updateKpiCard(kpiType, response.data);
        } catch (e) {
            console.error('KPI load failed:', kpiType, e);
        }
    }

    function updateKpiCard(kpiType, data) {
        switch (kpiType) {
            case 'runway-burn': updateRunwayBurnCard(data); break;
            case 'working-capital': updateWorkingCapitalCard(data); break;
            case 'operating-cashflow': updateOperatingCashFlowCard(data); break;
            case 'revenue': updateRevenueCard(data); break;
            case 'ebitda': updateEbitdaCard(data); break;
            case 'margin-trends': updateMarginTrendsCard(data); break;
            case 'ar-aging': updateArAgingCard(data); break;
            case 'ap-leverage': updateApLeverageCard(data); break;
            case 'revenue-concentration': updateRevenueConcentrationCard(data); break;
        }
    }

    function cardEl(type) {
        return document.querySelector('[data-kpi-card="' + type + '"]');
    }

    function resetKpiCardVisual(type) {
        const card = cardEl(type);
        if (!card) return;
        if (type === 'runway-burn') {
            const runwaySpan = card.querySelector('.kpi-hero-num');
            if (runwaySpan) runwaySpan.textContent = '—';
            const unitEl = card.querySelector('[data-runway-unit]');
            if (unitEl) unitEl.textContent = ' Months';
            setMini(card, 'rb-cash', '$—');
            setMini(card, 'rb-burn', '$—');
            const bar = card.querySelector('[data-rb-bar]');
            if (bar) bar.style.setProperty('--runway-percent', '0%');
            const badge = card.querySelector('.status-badge');
            if (badge) {
                badge.className = 'status-badge warning-zone';
                badge.style.opacity = '';
                badge.innerHTML = '<i class="fa fa-info-circle"></i> No data for this company';
            }
        } else if (type === 'working-capital') {
            setHeroPlain(card, '$—');
            setMini(card, 'wc-ca', '$—');
            setMini(card, 'wc-cl', '$—');
            const al = card.querySelector('[data-wc-assets-lbl]');
            const ll = card.querySelector('[data-wc-liab-lbl]');
            if (al) al.textContent = '—';
            if (ll) ll.textContent = '—';
            const mom = card.querySelector('[data-wc-mom]');
            if (mom) {
                mom.textContent = '— vs last month';
                mom.classList.remove('positive', 'negative');
            }
            const ta = card.querySelector('.kpi-wc-track.assets');
            const tl = card.querySelector('.kpi-wc-track.liabilities');
            if (ta) ta.style.setProperty('--wc-assets-pct', '50%');
            if (tl) tl.style.setProperty('--wc-liab-pct', '20%');
        } else if (type === 'operating-cashflow') {
            setHeroPlain(card, '$—');
            setMini(card, 'ocf-n', '—');
            setMini(card, 'ocf-peak', '$—');
            const bars = card.querySelector('[data-ocf-bars]');
            if (bars) bars.innerHTML = '';
        } else if (type === 'revenue') {
            setHeroPlain(card, '$—');
            const pill = card.querySelector('[data-rev-pill]');
            if (pill) {
                pill.textContent = 'YoY —';
                pill.classList.remove('positive', 'negative');
            }
            setMini(card, 'rev-yoy', '—');
            setMini(card, 'rev-n', '0');
            const rc = card.querySelector('[data-rev-chart]');
            if (rc) rc.innerHTML = '';
        } else if (type === 'ebitda') {
            setHeroPlain(card, '$—');
            setMini(card, 'ebitda-margin', '—');
            const pill = card.querySelector('[data-ebitda-pill]');
            if (pill) pill.textContent = '—';
            const eb = card.querySelector('[data-ebitda-chart]');
            if (eb) eb.innerHTML = '';
        } else if (type === 'margin-trends') {
            setHeroPlain(card, '—%');
            setMini(card, 'mt-net', '—');
            setMini(card, 'mt-n', '0');
            const pill = card.querySelector('[data-mt-pill]');
            if (pill) pill.textContent = '—';
            const lg = card.querySelector('[data-mt-lg-gross]');
            const ln = card.querySelector('[data-mt-lg-net]');
            if (lg) lg.textContent = '—';
            if (ln) ln.textContent = '—';
            const mt = card.querySelector('[data-mt-chart]');
            if (mt) mt.innerHTML = '';
        } else if (type === 'ar-aging') {
            const pill = card.querySelector('[data-ar-pill]');
            if (pill) pill.textContent = 'DSO: —';
            setHeroPlain(card, '$—');
            renderAgingBarChart(card.querySelector('[data-ar-chart]'), []);
        } else if (type === 'ap-leverage') {
            const pill = card.querySelector('[data-ap-pill]');
            if (pill) pill.textContent = 'DPO: —';
            setHeroPlain(card, '$—');
            renderAgingBarChart(card.querySelector('[data-ap-chart]'), []);
        } else if (type === 'revenue-concentration') {
            renderConcentrationBarChart(card.querySelector('[data-rc-chart]'), []);
            const leg = card.querySelector('[data-rc-legend]');
            if (leg) leg.textContent = '';
        }
    }

    function updateRunwayBurnCard(data) {
        const card = cardEl('runway-burn');
        if (!card) return;
        const runwaySpan = card.querySelector('.kpi-hero-num');
        const unitEl = card.querySelector('[data-runway-unit]');
        const rawM = parseFloat(String(data.runway_months ?? '').replace(/,/g, ''));
        if (runwaySpan && !isNaN(rawM) && rawM > 0) {
            if (rawM >= 12) {
                runwaySpan.textContent = (rawM / 12).toFixed(1);
                if (unitEl) unitEl.textContent = ' Years';
            } else {
                runwaySpan.textContent = data.runway_months;
                if (unitEl) unitEl.textContent = ' Months';
            }
        } else if (runwaySpan) {
            runwaySpan.textContent = '—';
            if (unitEl) unitEl.textContent = ' Months';
        }
        setMini(card, 'rb-cash', data.cash_balance ?? '$—');
        setMini(card, 'rb-burn', data.monthly_burn ?? '$—');
        const bar = card.querySelector('[data-rb-bar]');
        if (bar) bar.style.setProperty('--runway-percent', (data.runway_percent || 0) + '%');
        const st = data.status;
        const badge = card.querySelector('.status-badge');
        if (st && st.class && badge) {
            badge.style.opacity = '';
            badge.classList.remove('safe-zone', 'warning-zone', 'critical-zone');
            badge.classList.add(st.class);
            if (st.class === 'safe-zone' && !isNaN(rawM) && rawM >= 18) {
                badge.innerHTML = '<i class="fa fa-arrow-up"></i> Cash Building';
            } else {
                badge.innerHTML = '<i class="fa ' + (st.icon || 'fa-info-circle') + '"></i> ' + (st.text || '');
            }
        }
    }

    function updateWorkingCapitalCard(data) {
        const card = cardEl('working-capital');
        if (!card) return;
        setHeroPlain(card, data.working_capital ?? '—');
        setMini(card, 'wc-ca', data.current_assets ?? '$—');
        setMini(card, 'wc-cl', data.current_liabilities ?? '$—');
        const al = card.querySelector('[data-wc-assets-lbl]');
        const ll = card.querySelector('[data-wc-liab-lbl]');
        if (al) al.textContent = data.current_assets ?? '—';
        if (ll) ll.textContent = data.current_liabilities ?? '—';
        const mom = card.querySelector('[data-wc-mom]');
        if (mom) {
            mom.textContent = (data.mom_percent || '—') + ' vs last month';
            mom.classList.remove('positive', 'negative');
            if (data.mom_class) mom.classList.add(data.mom_class);
        }
        const ta = card.querySelector('.kpi-wc-track.assets');
        const tl = card.querySelector('.kpi-wc-track.liabilities');
        if (ta && data.assets_share_pct != null) {
            ta.style.setProperty('--wc-assets-pct', data.assets_share_pct + '%');
        }
        if (tl && data.liabilities_share_pct != null) {
            tl.style.setProperty('--wc-liab-pct', data.liabilities_share_pct + '%');
        }
    }

    function updateOperatingCashFlowCard(data) {
        const card = cardEl('operating-cashflow');
        if (!card) return;
        const md = data.monthly_data || [];
        const latest = md.length ? md[md.length - 1] : null;
        const heroAmt = latest ? latest.amount : '$—';
        setHeroPlain(card, heroAmt);
        setMini(card, 'ocf-n', String(md.length || 0));
        let peakAmt = '$—';
        let peakRaw = 0;
        md.forEach(function (pt) {
            const r = typeof pt.raw_amount === 'number' ? pt.raw_amount : 0;
            if (r >= peakRaw) {
                peakRaw = r;
                peakAmt = pt.amount || peakAmt;
            }
        });
        setMini(card, 'ocf-peak', peakAmt);
        renderOcfBars(card.querySelector('[data-ocf-bars]'), md);
    }

    function updateRevenueCard(data) {
        const card = cardEl('revenue');
        if (!card) return;
        setHeroPlain(card, data.current_month_revenue ?? '—');
        const pill = card.querySelector('[data-rev-pill]');
        if (pill) {
            pill.textContent = 'YoY ' + (data.yoy_growth ?? '—');
            pill.classList.remove('positive', 'negative');
            if (data.yoy_class) pill.classList.add(data.yoy_class);
        }
        setMini(card, 'rev-yoy', data.yoy_growth ?? '—');
        const trend = data.monthly_trend || [];
        setMini(card, 'rev-n', String(trend.length || 0));
        renderRevenueAreaChart(card.querySelector('[data-rev-chart]'), trend);
    }

    function updateEbitdaCard(data) {
        const card = cardEl('ebitda');
        if (!card) return;
        setHeroPlain(card, data.ebitda ?? '—');
        setMini(card, 'ebitda-margin', data.margin ?? '—');
        const pill = card.querySelector('[data-ebitda-pill]');
        if (pill) pill.textContent = data.margin ? ('Margin ' + data.margin) : '—';
        renderEbitdaMiniBars(card.querySelector('[data-ebitda-chart]'), data.monthly_bars_raw || []);
    }

    function updateMarginTrendsCard(data) {
        const card = cardEl('margin-trends');
        if (!card) return;
        const md = data.monthly_data || [];
        const latest = md.length ? md[md.length - 1] : null;
        const grossVal = data.gross_margin_ltm != null ? data.gross_margin_ltm : (latest ? latest.gross_margin : null);
        const netVal = data.net_margin_ltm != null ? data.net_margin_ltm : (latest ? latest.net_margin : null);
        const gross = grossVal != null ? grossVal + '%' : '—%';
        setHeroPlain(card, gross);
        const net = netVal != null ? netVal + '%' : '—';
        setMini(card, 'mt-net', net);
        setMini(card, 'mt-n', String(md.length || 0));
        const pill = card.querySelector('[data-mt-pill]');
        if (pill) pill.textContent = grossVal != null ? ('Gross ' + grossVal + '%') : '—';
        const lg = card.querySelector('[data-mt-lg-gross]');
        const ln = card.querySelector('[data-mt-lg-net]');
        if (lg) lg.textContent = grossVal != null ? (grossVal + '%') : '—';
        if (ln) ln.textContent = netVal != null ? (netVal + '%') : '—';
        renderMarginDots(card.querySelector('[data-mt-chart]'), md);
    }

    function updateArAgingCard(data) {
        const card = cardEl('ar-aging');
        if (!card) return;
        const pill = card.querySelector('[data-ar-pill]');
        if (pill) pill.textContent = data.dso_badge || 'DSO: —';
        setHeroPlain(card, data.total_ar ?? '$—');
        renderAgingBarChart(card.querySelector('[data-ar-chart]'), data.aging_buckets || []);
    }

    function updateApLeverageCard(data) {
        const card = cardEl('ap-leverage');
        if (!card) return;
        const pill = card.querySelector('[data-ap-pill]');
        if (pill) pill.textContent = data.dpo_badge || 'DPO: —';
        setHeroPlain(card, data.total_ap ?? '$—');
        renderAgingBarChart(card.querySelector('[data-ap-chart]'), data.aging_buckets || []);
    }

    function updateRevenueConcentrationCard(data) {
        const card = cardEl('revenue-concentration');
        if (!card) return;
        const sub = card.querySelector('[data-rc-sub]');
        if (sub) sub.textContent = data.subtitle || 'Top 5 Clients % of Total ARR';
        renderConcentrationBarChart(card.querySelector('[data-rc-chart]'), data.customers || []);
        const leg = card.querySelector('[data-rc-legend]');
        if (leg) {
            const t5 = data.top5_share_pct != null ? Number(data.top5_share_pct) : null;
            if (t5 != null && !isNaN(t5)) {
                leg.textContent = 'Top 5: ' + t5.toFixed(1) + '%';
            } else {
                leg.textContent = '';
            }
        }
    }

    function showError(message) {
        const root = kpiDashEl();
        if (!root) return;
        root.insertAdjacentHTML('afterbegin', '<div class="alert alert-danger" style="margin-bottom:16px;"><i class="fa fa-exclamation-circle"></i> ' + message + '</div>');
    }

    window.showKpiDetail = function (kpiType) {
        window.__kpiOpenFullPage(kpiType);
    };

    bindKpiDrawerUi();
    loadKpiDashboard();
})();
</script>
@endpush
@endonce
