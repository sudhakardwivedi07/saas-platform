{{-- Shared KPI dashboard styles — unified card chrome across all KPIs --}}
<style>
    /* Bleed KPI grid to edges of .ca-content (padding 28px) when wrapped in this class */
    .user-kpi-fullbleed {
        width: calc(100% + 56px);
        max-width: none;
        margin-left: -28px;
        margin-right: -28px;
        padding: 8px 28px 16px;
        box-sizing: border-box;
        background: var(--cfo-bg, #f3f4f6);
    }
    @media (max-width: 900px) {
        .user-kpi-fullbleed {
            width: 100%;
            margin-left: 0;
            margin-right: 0;
            padding-left: 0;
            padding-right: 0;
        }
    }

    /* CFO Insights–style palette */
    .user-kpi-fullbleed, .kpi-dashboard-root {
        --cfo-orange: #f97316;
        --cfo-blue: #0788be;
        --cfo-slate: #1a1a2e;
        --cfo-muted: #6b7280;
        --cfo-bg: #f3f4f6;
    }
    /* ── Exec bar: 3-column layout ───────────────────────── */
    .cfo-kpi-exec-bar {
        display: grid;
        grid-template-columns: minmax(160px, 210px) 1fr auto;
        align-items: stretch;
        gap: 0;
        margin-bottom: 22px;
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        overflow: hidden;
    }
    @media (max-width: 860px) {
        .cfo-kpi-exec-bar {
            grid-template-columns: 1fr;
        }
        .cfo-kpi-exec-entity  { border-right: none !important; border-bottom: 1px solid #e8ecf1; }
        .cfo-kpi-exec-right   { border-left: none !important; border-top: 1px solid #e8ecf1; align-items: flex-start !important; }
    }

    /* Entity / company selector — left column */
    .cfo-kpi-exec-entity {
        padding: 16px 20px;
        border-right: 1px solid #e8ecf1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #fafbfc;
    }
    .cfo-kpi-exec-entity-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 7px;
    }
    .cfo-kpi-exec-entity-name {
        display: block;
        font-size: 15px;
        font-weight: 700;
        color: var(--cfo-slate);
        line-height: 1.25;
        word-break: break-word;
    }
    .cfo-kpi-exec-entity-select {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        color: var(--cfo-slate);
        background: #fff;
        cursor: pointer;
        outline: none;
        font-family: inherit;
    }
    .cfo-kpi-exec-entity-select:focus { border-color: var(--cfo-orange); box-shadow: 0 0 0 3px rgba(249,115,22,.1); }

    /* Title / subtitle — center column */
    .cfo-kpi-exec-center {
        padding: 18px 22px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
    }
    .cfo-kpi-exec-title { font-size: 18px; font-weight: 700; color: var(--cfo-slate); margin: 0 0 4px; letter-spacing: -0.02em; }
    .cfo-kpi-exec-sub   { font-size: 12.5px; color: var(--cfo-muted); margin: 0; }

    /* Last-updated + actions — right column */
    .cfo-kpi-exec-right {
        padding: 16px 20px;
        border-left: 1px solid #e8ecf1;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: center;
        gap: 10px;
        flex-shrink: 0;
    }
    .cfo-kpi-exec-updated { font-size: 11.5px; color: var(--cfo-muted); margin: 0; white-space: nowrap; }
    .cfo-kpi-exec-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }

    /* legacy left wrapper — kept in case any other template uses it */
    .cfo-kpi-exec-left { flex: 1; min-width: 0; }

    .cfo-btn-get-insights {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 22px;
        border-radius: 8px;
        border: none;
        background: var(--cfo-orange);
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        font-family: inherit;
        box-shadow: 0 2px 10px rgba(242, 113, 33, .35);
        transition: filter .15s ease, transform .1s ease;
    }
    .cfo-btn-get-insights:hover { filter: brightness(1.06); transform: translateY(-1px); }
    .cfo-btn-get-insights:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .cfo-btn-view-insights {
        display: inline-flex;
        align-items: center;
        padding: 10px 18px;
        border-radius: 8px;
        border: 2px solid var(--cfo-orange);
        background: #fff;
        color: var(--cfo-orange);
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        font-family: inherit;
    }
    .cfo-btn-view-insights:hover { background: #fff8f3; }
    .cfo-dash-gen-loader { padding: 10px 18px !important; border-radius: 8px; }
    .cfo-kpi-exec-left { flex: 1; min-width: 0; }

    .kpi-container { width: 100%; max-width: none; margin: 0; padding: 0 0 20px; box-sizing: border-box; }
    .company-selector {
        margin-bottom: 20px;
        padding: 14px 20px;
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e8ecf1;
        box-shadow: 0 1px 2px rgba(0,0,0,.04);
    }
    .company-selector label { font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: #6b7280; margin-bottom: 8px; display: block; }
    .company-selector select { width: 100%; max-width: 420px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: #fff; }
    .company-selector select:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    .kpi-dashboard-root { width: 100%; box-sizing: border-box; background: transparent; }
    .survival-metrics { margin-top: 0; width: 100%; }
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(100%, 280px), 1fr));
        gap: 20px;
        margin-bottom: 4px;
        width: 100%;
        align-items: stretch;
    }
    .metrics-grid > .kpi-card { height: 100%; align-self: stretch; }
    @media (min-width: 1200px) {
        .metrics-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    /* ── Unified KPI card shell ───────────────────────────────────────── */
    .kpi-card {
        background: #fff;
        border: 1px solid #e8ecf1;
        border-radius: 10px;
        border-left: 4px solid var(--cfo-orange, #f27121);
        padding: 0;
        box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 2px rgba(0,0,0,.04);
        transition: box-shadow .2s ease, transform .2s ease;
        display: flex;
        flex-direction: column;
        min-height: 300px;
        width: 100%;
        box-sizing: border-box;
        cursor: pointer;
        /* Allow chart SVGs / bar columns to paint past inner bounds */
        overflow: visible;
    }
    .kpi-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); transform: translateY(-2px); }
    .kpi-card-inner {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
        padding: 20px 22px 18px;
        min-height: 0;
    }
    .kpi-header {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(132px, 36%);
        column-gap: 16px;
        row-gap: 8px;
        align-items: start;
        margin-bottom: 0;
        flex-shrink: 0;
    }
    @media (max-width: 380px) {
        .kpi-header { grid-template-columns: 1fr; }
        .kpi-right { justify-self: stretch; align-items: flex-end !important; }
    }
    .kpi-left { min-width: 0; align-self: start; }
    .kpi-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: flex-start;
        gap: 14px;
        padding-top: 0;
        min-width: 0;
        justify-self: end;
        width: 100%;
        max-width: 220px;
    }
    .kpi-mini-stat {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
        width: 100%;
        max-width: 100%;
    }
    .kpi-mini-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #9ca3af;
        margin-bottom: 4px;
    }
    .kpi-mini-value {
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        line-height: 1.2;
        word-break: break-word;
        font-variant-numeric: tabular-nums;
    }
    .kpi-mini-value--accent { color: var(--cfo-orange, #f27121); }
    .metric-label { font-size: 12px; font-weight: 700; color: var(--cfo-orange, #f27121); margin-bottom: 6px; letter-spacing: .02em; text-transform: none; }
    .metric-label--blue { color: var(--cfo-blue, #007bff); }
    .metric-label--caps { text-transform: uppercase; letter-spacing: 0.1em; font-size: 11px; }
    .kpi-hero-value {
        font-size: 34px;
        font-weight: 700;
        line-height: 1.08;
        color: #111827;
        margin-bottom: 0;
        letter-spacing: -0.02em;
        overflow-wrap: anywhere;
        word-break: normal;
    }
    .kpi-hero-value .unit { font-size: 16px; font-weight: 600; margin-left: 4px; color: #6b7280; }
    .kpi-left-meta {
        min-height: 36px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 20px;
        padding: 5px 12px;
        font-size: 11px;
        font-weight: 600;
        gap: 6px;
    }
    .status-badge.safe-zone { background: #d1fae5; color: #065f46; }
    .status-badge.warning-zone { background: #fef3c7; color: #92400e; }
    .status-badge.critical-zone { background: #fee2e2; color: #991b1b; }
    .kpi-mom-pill {
        display: inline-flex;
        font-size: 11px;
        font-weight: 600;
        padding: 5px 11px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
    }
    .kpi-mom-pill.positive { background: #ecfdf5; color: #065f46; }
    .kpi-mom-pill.negative { background: #fef2f2; color: #991b1b; }

    /* Footer band — same height & rhythm for every card */
    .kpi-card-footer {
        margin-top: auto;
        padding: 16px 22px 18px;
        background: linear-gradient(180deg, #fafbfc 0%, #f4f7fa 100%);
        border-top: 1px solid #eef2f6;
        min-height: 118px;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        gap: 10px;
    }
    .kpi-footer-desc { font-size: 12.5px; color: #6b7280; line-height: 1.45; margin: 6px 0 0; }
    .kpi-footer-metrics { display: grid; grid-template-columns: 1fr 1fr; gap: 12px 16px; }
    .kpi-footer-metrics--single { grid-template-columns: 1fr; }
    .kpi-stacked-metric .burn-rate-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; margin-bottom: 4px; display: block; }
    .kpi-stacked-metric .burn-rate-value { font-size: 17px; font-weight: 700; color: #1f2937; margin: 0; }
    .burn-rate-value.positive { color: #059669; }
    .burn-rate-value.negative { color: #ea580c; }
    .runway-bar {
        height: 8px;
        border-radius: 10px;
        background: linear-gradient(to right, var(--cfo-blue, #007bff) 0%, var(--cfo-blue, #007bff) var(--runway-percent, 0%), #e2e8f0 var(--runway-percent, 0%), #e2e8f0 100%);
        margin-top: 4px;
    }
    .runway-scale {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
        font-size: 10px;
        color: #6b7280;
        margin-top: 8px;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .runway-scale span { flex: 1 1 0; min-width: 0; text-align: center; line-height: 1.25; }
    .runway-scale span:first-child { text-align: left; }
    .runway-scale span:last-child { text-align: right; }
    .kpi-wc-bar-wrap { margin-top: 4px; }
    .kpi-wc-bar-labels { display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8; margin-bottom: 4px; text-transform: uppercase; letter-spacing: .04em; }
    .kpi-wc-ratio-bar {
        height: 8px;
        border-radius: 10px;
        background: #e2e8f0;
        overflow: hidden;
    }
    .kpi-wc-ratio-bar > span {
        display: block;
        height: 100%;
        background: linear-gradient(90deg, var(--cfo-blue, #007bff), #4da3ff);
        width: var(--wc-assets-pct, 50%);
        border-radius: 10px;
        transition: width .35s ease;
    }
    /* Dual WC bars (CFO layout) */
    .kpi-wc-dual { display: flex; flex-direction: column; gap: 12px; width: 100%; }
    .kpi-wc-dual-row { display: flex; flex-direction: column; gap: 4px; }
    .kpi-wc-dual-top { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; font-size: 11px; font-weight: 600; color: #64748b; }
    .kpi-wc-dual-top span:first-child { text-align: left; flex: 1; min-width: 0; }
    .kpi-wc-dual-top .kpi-wc-amt { flex-shrink: 0; text-align: right; font-variant-numeric: tabular-nums; }
    .kpi-wc-dual-top .kpi-wc-amt { font-size: 13px; font-weight: 700; color: #111827; }
    .kpi-wc-track { height: 10px; border-radius: 8px; background: #eef2f6; overflow: hidden; }
    .kpi-wc-track > span { display: block; height: 100%; border-radius: 8px; transition: width .35s ease; }
    .kpi-wc-track.assets > span { background: var(--cfo-orange, #f27121); width: var(--wc-assets-pct, 50%); }
    .kpi-wc-track.liabilities > span { background: var(--cfo-blue, #007bff); width: var(--wc-liab-pct, 20%); }
    .ocf-spark { display: flex; align-items: flex-end; gap: 3px; height: 40px; margin-top: 6px; padding-top: 4px; }
    .ocf-spark span {
        flex: 1;
        min-width: 4px;
        background: #cbd5e1;
        border-radius: 2px 2px 0 0;
        transition: height .2s ease, background .2s ease;
    }
    .ocf-spark span.is-last { background: var(--cfo-blue, #007bff); }
    /* OCF monthly bar chart (CFO-style) */
    .kpi-ocf-bars {
        position: relative;
        display: flex;
        align-items: stretch;
        justify-content: space-between;
        gap: 4px;
        height: 124px;
        padding: 8px 2px 0;
        border-top: 1px dashed #e5e7eb;
        border-bottom: 1px dashed #e5e7eb;
        overflow: hidden;
    }
    .kpi-ocf-bars::before {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        border-top: 1px solid #d1d5db;
        z-index: 1;
    }
    .kpi-ocf-bars .ocf-col { flex: 1; min-width: 0; display: flex; flex-direction: column; align-items: center; height: 100%; position: relative; z-index: 2; }
    .kpi-ocf-bars .ocf-bar-wrap { position: relative; flex: 1; width: 100%; max-width: 28px; }
    .kpi-ocf-bars .ocf-bar {
        position: absolute;
        left: 0;
        width: 100%;
        min-height: 4px;
        background: var(--cfo-blue, #007bff);
        transition: height .3s ease, background .2s ease;
    }
    .kpi-ocf-bars .ocf-bar.is-negative { background: var(--cfo-orange, #f27121); }
    .kpi-ocf-bars .ocf-bar.is-positive { border-radius: 3px 3px 0 0; }
    .kpi-ocf-bars .ocf-bar.is-negative { border-radius: 0 0 3px 3px; }
    .kpi-ocf-bars .ocf-mo { font-size: 9px; font-weight: 600; color: #6b7280; margin-top: 6px; text-transform: uppercase; letter-spacing: .02em; }

    .insight-box { background: #fff; border: 1px solid #e8ecf1; border-left: 4px solid var(--cfo-blue, #007bff); border-radius: 10px; padding: 18px 20px; margin-top: 8px; }
    .insight-box .insight-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; color: #64748b; }
    .insight-box .insight-text { font-size: 13px; line-height: 1.6; color: #475569; }
    .tier3-tier4-container { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; width: 100%; align-items: stretch; }
    @media (max-width: 1200px) { .tier3-tier4-container { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) { .tier3-tier4-container { grid-template-columns: 1fr; } }

    /* Business sections — survival, performance, efficiency, risk */
    .kpi-tier-block { margin-top: 32px; }
    .kpi-dashboard-root > .kpi-tier-block:first-of-type { margin-top: 8px; }
    .kpi-tier-section-title {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 14px;
    }
    .kpi-efficiency-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        align-items: stretch;
        width: 100%;
    }
    .kpi-efficiency-grid > .kpi-card { height: 100%; }
    @media (max-width: 900px) { .kpi-efficiency-grid { grid-template-columns: 1fr; } }
    /* Single risk KPI: keep card readable width (not full-bleed strip like a whole row) */
    .kpi-risk-grid {
        /* display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
        width: 100%; */
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 20px;
        align-items: stretch;
        width: 100%;
    }
    .kpi-risk-grid > .kpi-card {
        height: 100%;
        width: 100%;
        max-width: min(100%, 680px);
        box-sizing: border-box;
    }

    .kpi-card.kpi-counter-card { min-height: 0; }
    .kpi-counter-card-inner {
        padding-bottom: 12px;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .kpi-counter-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
    }
    .kpi-counter-pill {
        flex-shrink: 0;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        background: #f1f5f9;
        border-radius: 999px;
        padding: 6px 12px;
        white-space: nowrap;
    }
    .kpi-counter-body { margin-top: 4px; flex: 1 1 auto; }
    .kpi-counter-sub {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 6px;
    }
    .kpi-counter-hero.kpi-hero-value { font-size: 36px; margin-bottom: 0; }

    .kpi-card-footer.kpi-counter-footer {
        min-height: 148px;
        padding-top: 14px;
        padding-bottom: 14px;
    }
    .kpi-hbar-chart {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
        width: 100%;
        min-height: 118px;
        padding: 4px 2px 0;
        box-sizing: border-box;
    }
    .kpi-hbar-col {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 118px;
    }
    .kpi-hbar-bar-wrap {
        flex: 1;
        width: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }
    .kpi-hbar-bar {
        width: 100%;
        max-width: 52px;
        min-height: 6px;
        border-radius: 6px 6px 0 0;
        background: var(--cfo-blue, #0788be);
        transition: height .35s ease;
    }
    .kpi-hbar-bar.is-accent { background: var(--cfo-orange, #f97316); }
    .kpi-hbar-bar.is-others { background: var(--cfo-blue, #0788be); }
    .kpi-hbar-val {
        font-size: 11px;
        font-weight: 800;
        color: #111827;
        margin-top: 8px;
        text-align: center;
        line-height: 1.2;
    }
    .kpi-hbar-lbl {
        font-size: 10px;
        font-weight: 600;
        color: #64748b;
        margin-top: 4px;
        text-align: center;
        line-height: 1.25;
        word-break: break-word;
    }

    /* Revenue concentration: Y-axis + bars row must span full card width */
    .kpi-hbar-chart.kpi-rc-concentration {
        display: block;
        width: 100%;
    }
    .kpi-rc-chart-row {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        width: 100%;
        min-height: 118px;
        box-sizing: border-box;
    }
    .kpi-rc-axis {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 118px;
        width: 40px;
        flex-shrink: 0;
        font-size: 10px;
        color: #9ca3af;
        text-align: right;
        line-height: 1.1;
    }
    .kpi-rc-bars {
        display: flex;
        gap: 8px;
        align-items: flex-end;
        flex: 1;
        min-width: 0;
        height: 118px;
    }
    .kpi-rc-concentration .kpi-hbar-bar {
        max-width: min(80px, 100%);
    }

    .kpi-rc-title {
        display: block;
        font-size: 15px;
        font-weight: 800;
        color: #111827;
        letter-spacing: -0.02em;
    }
    .kpi-rc-title--blue { color: var(--cfo-blue, #007bff); }
    .kpi-rc-static-legend {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 18px;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        margin-top: 8px;
    }
    .kpi-rc-static-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .kpi-rc-static-legend i { width: 10px; height: 10px; border-radius: 2px; display: inline-block; }
    .kpi-rc-static-legend .sw-top5 { background: var(--cfo-orange, #f27121); }
    .kpi-rc-static-legend .sw-oth { background: var(--cfo-blue, #007bff); }
    /* Mini charts — revenue area, EBITDA bars, margin dots */
    .kpi-mini-chart-wrap { width: 100%; min-height: 104px; margin-top: 4px; flex-shrink: 0; }
    .kpi-mini-chart-wrap svg { width: 100%; height: 104px; min-height: 104px; display: block; }
    .kpi-rev-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 18px;
        margin-top: 8px;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
    }
    .kpi-rev-legend span { display: inline-flex; align-items: center; gap: 6px; }
    .kpi-rev-legend i { width: 18px; height: 3px; border-radius: 2px; display: inline-block; }
    .kpi-rev-legend .cy { background: var(--cfo-orange, #f27121); }
    .kpi-rev-legend .ly { background: #cbd5e1; border-bottom: 2px dashed #94a3b8; height: 0; width: 18px; }
    .kpi-mt-legend .mt-legend-net {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--cfo-blue, #007bff);
        display: inline-block;
    }
    .kpi-ebitda-bars { display: flex; align-items: flex-end; justify-content: space-between; gap: 3px; height: 100px; min-height: 100px; padding-top: 6px; flex-shrink: 0; }
    .kpi-ebitda-bars .eb-col { flex: 1; display: flex; flex-direction: column; align-items: center; height: 100%; min-height: 0; justify-content: flex-end; }
    .kpi-ebitda-bars .eb-bar { width: 100%; max-width: 22px; border-radius: 4px 4px 0 0; background: var(--cfo-blue, #007bff); min-height: 4px; transition: height .3s ease; }
    .kpi-mt-chart { display: flex; align-items: flex-end; justify-content: space-between; gap: 6px; min-height: 112px; height: 112px; padding: 8px 4px 0; flex-shrink: 0; }
    .kpi-mt-chart .mt-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; min-height: 0; }
    .kpi-mt-chart .mt-plot {
        position: relative;
        width: 100%;
        max-width: 36px;
        height: 72px;
        margin: 0 auto;
    }
    .kpi-mt-chart .mt-dot-pos {
        position: absolute;
        transform: translate(-50%, 50%);
        bottom: 0;
    }
    .kpi-mt-chart .mt-dot-pos.is-gross { left: 38%; }
    .kpi-mt-chart .mt-dot-pos.is-net { left: 62%; }
    .kpi-mt-chart .mt-dot { width: 8px; height: 8px; border-radius: 50%; }
    .kpi-mt-chart .mt-dot.gross { background: var(--cfo-orange, #f27121); }
    .kpi-mt-chart .mt-dot.net { background: var(--cfo-blue, #007bff); }
    .kpi-mt-chart .mt-lbl { font-size: 9px; color: #94a3b8; font-weight: 600; margin-top: 4px; }

    .kpi-rc-info {
        color: var(--cfo-orange, #f27121);
        font-size: 18px;
        line-height: 1;
        cursor: default;
        flex-shrink: 0;
    }
    .kpi-counter-head--rc { align-items: flex-start; }
    .kpi-counter-head--rc .kpi-counter-sub { margin-top: 6px; margin-bottom: 0; text-transform: none; letter-spacing: 0; font-weight: 600; color: #64748b; }

    .kpi-card { outline: none; }
    .kpi-card:focus-visible { box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.28); }

    /* Detail drawer (SPA) */
    .kpi-detail-drawer-root { position: fixed; inset: 0; z-index: 5000; visibility: hidden; }
    .kpi-detail-drawer-root.is-open { visibility: visible; }
    .kpi-detail-drawer-backdrop {
        position: absolute; inset: 0; background: rgba(15, 23, 42, 0.45);
        opacity: 0; transition: opacity 0.22s ease;
    }
    .kpi-detail-drawer-root.is-open .kpi-detail-drawer-backdrop { opacity: 1; }
    .kpi-detail-drawer-panel {
        position: absolute; top: 0; right: 0; bottom: 0;
        width: min(560px, 100vw);
        max-width: 100%;
        background: #fff;
        box-shadow: -12px 0 48px rgba(0, 0, 0, 0.14);
        transform: translateX(100%);
        transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
    }
    .kpi-detail-drawer-root.is-open .kpi-detail-drawer-panel { transform: translateX(0); }
    .kpi-detail-drawer-head {
        padding: 16px 18px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-shrink: 0;
    }
    .kpi-detail-drawer-title { font-size: 18px; font-weight: 800; color: #111827; margin: 0; line-height: 1.25; }
    .kpi-detail-drawer-close {
        border: none; background: #f1f5f9; color: #475569;
        width: 36px; height: 36px; border-radius: 10px;
        font-size: 22px; line-height: 1; cursor: pointer;
        flex-shrink: 0; display: flex; align-items: center; justify-content: center;
    }
    .kpi-detail-drawer-close:hover { background: #e2e8f0; color: #0f172a; }
    .kpi-detail-drawer-actions {
        padding: 10px 18px;
        border-bottom: 1px solid #f1f5f9;
        flex-shrink: 0;
    }
    .kpi-detail-open-page {
        font-size: 13px;
        font-weight: 600;
        color: var(--cfo-blue, #007bff);
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px 0;
        text-decoration: underline;
    }
    .kpi-detail-open-page:hover { color: #0056b3; }
    .kpi-detail-drawer-body { flex: 1; overflow-y: auto; padding: 18px; -webkit-overflow-scrolling: touch; }
    .kpi-detail-metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .kpi-detail-metric {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
    }
    .kpi-detail-metric-l {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 6px;
    }
    .kpi-detail-metric-v { font-size: 19px; font-weight: 800; color: #0f172a; word-break: break-word; line-height: 1.2; }
    .kpi-detail-subh {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #64748b;
        margin: 20px 0 10px;
    }
    .kpi-detail-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .kpi-detail-table th,
    .kpi-detail-table td { text-align: left; padding: 9px 10px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
    .kpi-detail-table th { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; background: #fafafa; }
    .kpi-detail-fallback { font-size: 14px; color: #64748b; line-height: 1.55; }
</style>
