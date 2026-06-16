<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $globalSetting?->global_app_name ?? config('app.name') }}</title>
    @if($globalSetting?->favicon)
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/'.$globalSetting->favicon) }}">
    @endif
    <link href="{{ asset(config('assets.font_inter')) }}" rel="stylesheet">
    <style>
/* ── BRAND TOKENS ─────────────────────────────────────── */
:root {
  --sidebar-bg:{{ $globalSetting?->primary_color ?? '#0a2540' }};
  --orange:{{ $globalSetting?->accent_color ?? '#F06B21' }}; --orange2:#d05519; --orange-l:#fff7ed;
  --blue:#0C84BC; --blue2:#0a6fa0; --blue3:#e8f4fb;
  --green:#10b981; --red:#ef4444; --amber:#f59e0b; --purple:#7c3aed;
  --text-1:#0f172a; --text-2:#475569; --text-3:#94a3b8;
  --bg:#f1f5f9; --card:#fff; --border:#e2e8f0;
  --sidebar-w:252px; --topbar-h:60px;
  --r:10px; --r-lg:12px;
  --sh:0 1px 3px rgba(0,0,0,.07),0 1px 2px rgba(0,0,0,.04);
  --sh-md:0 4px 16px rgba(0,0,0,.10);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter','Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--text-1);font-size:14px}
input,select,textarea,button{font-family:inherit}
a{text-decoration:none;color:inherit}
button{cursor:pointer}

/* ── SIDEBAR ──────────────────────────────────────────── */
.sa-sidebar{position:fixed;left:0;top:0;bottom:0;width:var(--sidebar-w);background:var(--sidebar-bg);display:flex;flex-direction:column;z-index:200}
.sa-logo-area{padding:16px 18px 14px;border-bottom:1px solid rgba(255,255,255,.08)}
.sa-logo-img{max-width:160px;max-height:44px;width:auto;height:auto;display:block;object-fit:contain}
.sa-badge{font-size:9px;font-weight:800;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.14em;margin-top:5px}
.sa-logo-text{color:#fff;font-size:17px;font-weight:700;line-height:1}
.sa-nav{padding:12px 10px;flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:1px}
.sa-nav-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;color:rgba(255,255,255,.55);font-size:13px;cursor:pointer;border:none;background:none;width:100%;text-align:left;transition:all .13s;text-decoration:none}
.sa-nav-item:hover{background:rgba(255,255,255,.07);color:#fff}
.sa-nav-item.active{background:color-mix(in srgb,var(--orange) 22%,transparent);color:var(--orange);font-weight:500}
.sa-nav-item.active svg{stroke:var(--orange)}
.sa-nav-item svg{width:15px;height:15px;flex-shrink:0;stroke:rgba(255,255,255,.4);stroke-width:1.8}
.sa-nav-item.active svg{stroke:var(--orange)}
.sa-nav-sep{height:1px;background:rgba(255,255,255,.06);margin:6px 10px}
.sa-nav-sec{font-size:9px;font-weight:700;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:.13em;padding:8px 12px 3px}
.sa-sidebar-foot{padding:12px 10px;border-top:1px solid rgba(255,255,255,.06)}

/* ── TOPBAR ───────────────────────────────────────────── */
.sa-topbar{position:fixed;left:var(--sidebar-w);right:0;top:0;height:var(--topbar-h);background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 22px;gap:14px;z-index:100}
.sa-topbar-bc{font-size:13px;color:var(--text-3);display:flex;align-items:center;gap:7px;flex:1;min-width:0}
.sa-topbar-bc span{color:var(--text-1);font-weight:500}
.sa-topbar-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
.sa-tb-notif{width:36px;height:36px;border-radius:50%;border:1.5px solid var(--border);display:flex;align-items:center;justify-content:center;position:relative;background:#fff;cursor:pointer}
.sa-tb-notif:hover{background:var(--bg)}
.sa-notif-dot{position:absolute;top:6px;right:6px;width:7px;height:7px;background:var(--red);border-radius:50%;border:1.5px solid #fff}
.sa-tb-user{display:flex;align-items:center;gap:9px;padding:5px 12px 5px 5px;border:1.5px solid var(--border);border-radius:999px;background:#fff}
.sa-tb-av{width:30px;height:30px;border-radius:50%;background:var(--blue);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sa-tb-uname{font-size:12.5px;font-weight:600;line-height:1.2}
.sa-tb-urole{font-size:10.5px;color:var(--orange);font-weight:500}
.sa-logout-btn{width:36px;height:36px;border-radius:50%;background:var(--orange);border:none;display:flex;align-items:center;justify-content:center;cursor:pointer}
.sa-logout-btn:hover{filter:brightness(.88)}
.sa-logout-btn svg{stroke:#fff;width:16px;height:16px}

/* ── MAIN ─────────────────────────────────────────────── */
.sa-main{margin-left:var(--sidebar-w);margin-top:var(--topbar-h);min-height:calc(100vh - var(--topbar-h));padding:22px;background:var(--bg)}

/* ── IMPERSONATION BANNER ─────────────────────────────── */
.sa-impersonate-bar{position:fixed;top:0;left:0;right:0;background:#f59e0b;color:#fff;text-align:center;padding:6px;font-size:13px;font-weight:500;z-index:300}
.sa-impersonate-bar a{color:#fff;text-decoration:underline;margin-left:8px;font-weight:700}
.has-impersonate-bar .sa-sidebar{top:34px}
.has-impersonate-bar .sa-topbar{top:34px}
.has-impersonate-bar .sa-main{margin-top:calc(var(--topbar-h) + 34px)}

/* ── ALERTS ───────────────────────────────────────────── */
.sa-alert{padding:10px 14px;border-radius:8px;font-size:13px;display:flex;align-items:center;gap:8px;margin-bottom:14px}
.sa-alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.sa-alert-error{background:#fff5f5;border:1px solid #fca5a5;color:#991b1b}
.sa-alert-info{background:var(--blue3);border:1px solid #bae3f9;color:var(--blue2)}
.sa-alert-warning{background:#fffbeb;border:1px solid #fde68a;color:#92400e}

/* ── PAGE HEADER ──────────────────────────────────────── */
.ph{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.ph h1{font-size:22px;font-weight:700;letter-spacing:-.3px;margin-bottom:3px}
.ph p{font-size:13px;color:var(--text-3)}
.ph-actions{display:flex;gap:8px;align-items:center;flex-shrink:0;flex-wrap:wrap}

/* ── BUTTONS ──────────────────────────────────────────── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;border:none;transition:all .14s;white-space:nowrap;cursor:pointer;text-decoration:none}
.btn-primary{background:var(--orange);color:#fff}.btn-primary:hover{filter:brightness(.88)}
.btn-blue{background:var(--blue);color:#fff}.btn-blue:hover{filter:brightness(.88)}
.btn-ghost{background:#fff;color:var(--text-2);border:1.5px solid var(--border)}.btn-ghost:hover{border-color:var(--text-3);color:var(--text-1)}
.btn-ghost-red{background:#fff;color:var(--red);border:1.5px solid #fca5a5}
.btn-sm{padding:6px 12px;font-size:12px}
.btn-xs{padding:4px 9px;font-size:11.5px}
.btn svg{width:14px;height:14px}

/* ── KPI ──────────────────────────────────────────────── */
.kpi-grid{display:grid;gap:13px;margin-bottom:14px}
.kpi-3{grid-template-columns:repeat(3,1fr)}
.kpi-4{grid-template-columns:repeat(4,1fr)}
.kpi{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--sh);position:relative;overflow:hidden}
.kpi-accent{position:absolute;left:0;top:0;bottom:0;width:4px;border-radius:var(--r-lg) 0 0 var(--r-lg)}
.kpi-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:11px}
.kpi-lbl{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text-3)}
.kpi-ico{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center}
.i-blue{background:var(--blue3)}.i-blue svg{stroke:var(--blue)}
.i-green{background:#f0fdf4}.i-green svg{stroke:var(--green)}
.i-orange{background:var(--orange-l)}.i-orange svg{stroke:var(--orange)}
.i-purple{background:#f5f3ff}.i-purple svg{stroke:var(--purple)}
.i-red{background:#fff5f5}.i-red svg{stroke:var(--red)}
.kpi-val{font-size:28px;font-weight:700;line-height:1;margin-bottom:4px;letter-spacing:-.5px}
.kpi-ctx{font-size:12px;color:var(--text-3)}
.kpi-trend{display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:600;padding:2px 6px;border-radius:999px;margin-top:3px}
.trend-up{background:#f0fdf4;color:var(--green)}.trend-dn{background:#fff5f5;color:var(--red)}

/* ── CARDS ────────────────────────────────────────────── */
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);padding:20px 22px;box-shadow:var(--sh);margin-bottom:14px}
.card-title{font-size:14px;font-weight:600;margin-bottom:3px}
.card-sub{font-size:12px;color:var(--text-3);margin-bottom:14px}
.acc-card{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh);margin-bottom:14px}
.acc-title{font-size:14px;font-weight:600;margin-bottom:16px}

/* ── TABLES ───────────────────────────────────────────── */
.tc{background:var(--card);border:1px solid var(--border);border-radius:var(--r-lg);box-shadow:var(--sh);overflow:hidden;margin-bottom:14px}
.tc-head{padding:12px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px}
table{width:100%;border-collapse:collapse}
th{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3);padding:10px 14px;text-align:left;border-bottom:1px solid var(--border);background:#fafbfc;white-space:nowrap}
td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
tr:last-child td{border-bottom:none}
tbody tr:hover td{background:#fafcff}
.toolbar{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
.srch-wrap{display:flex;align-items:center;gap:7px;background:#fff;border:1.5px solid var(--border);border-radius:8px;padding:6px 11px;min-width:200px}
.srch-wrap svg{width:13px;height:13px;flex-shrink:0;stroke:var(--text-3)}
.srch-inp{border:none;outline:none;font-size:13px;width:100%;background:transparent}
.sel{border:1.5px solid var(--border);border-radius:8px;padding:6px 10px;font-size:13px;background:#fff;outline:none;color:var(--text-1);cursor:pointer}

/* ── BADGES ───────────────────────────────────────────── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;font-size:11.5px;font-weight:600;white-space:nowrap}
.b-green{background:#f0fdf4;color:#166534}.b-red{background:#fff5f5;color:#991b1b}
.b-blue{background:var(--blue3);color:var(--blue2)}.b-orange{background:var(--orange-l);color:#c2410c}
.b-amber{background:#fffbeb;color:#92400e}.b-gray{background:#f1f5f9;color:#475569}
.b-purple{background:#f5f3ff;color:#6d28d9}
.bdot{width:6px;height:6px;border-radius:50%;flex-shrink:0}

/* ── TABS ─────────────────────────────────────────────── */
.tab-bar{display:flex;gap:2px;border-bottom:1px solid var(--border);margin-bottom:18px;overflow-x:auto}
.tab-btn{display:flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;color:var(--text-2);border:none;background:none;border-bottom:2px solid transparent;cursor:pointer;white-space:nowrap;transition:all .13s;margin-bottom:-1px}
.tab-btn:hover{color:var(--text-1)}
.tab-btn.active{color:var(--orange);border-bottom-color:var(--orange)}
.tab-panel{display:none}.tab-panel.active{display:block}

/* ── MODALS ───────────────────────────────────────────── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(10,37,64,.5);z-index:500;align-items:center;justify-content:center}
.modal-overlay.open{display:flex}
.modal-box{background:#fff;border-radius:16px;padding:28px 28px 22px;max-width:96vw;box-shadow:0 20px 60px rgba(0,0,0,.2);max-height:92vh;overflow-y:auto;position:relative;width:100%}
.modal-title{font-size:17px;font-weight:700;margin-bottom:4px}
.modal-sub{font-size:13px;color:var(--text-3);margin-bottom:18px}
.modal-close{position:absolute;top:16px;right:16px;background:var(--bg);border:none;border-radius:8px;width:30px;height:30px;font-size:18px;display:flex;align-items:center;justify-content:center;color:var(--text-2);cursor:pointer;line-height:1}
.mf-field{margin-bottom:8px}
.mf-label{font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:5px;display:block}
.mf-inp{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;transition:border .14s;background:#fff}
.mf-inp:focus{border-color:var(--blue)}
.mf-foot{display:flex;justify-content:flex-end;gap:9px;margin-top:20px}
.form-2col{display:grid;grid-template-columns:1fr 1fr;gap:0 14px;align-items:start}

/* ── FORMS ────────────────────────────────────────────── */
.f-group{margin-bottom:14px}
.f-label{font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:5px;display:block}
.f-inp{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;transition:border .14s;background:#fff;color:var(--text-1)}
.f-inp:focus{border-color:var(--blue)}
.f-sel{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff;cursor:pointer;color:var(--text-1)}
.f-sel:focus{border-color:var(--blue)}
.f-textarea{width:100%;border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;outline:none;resize:vertical;min-height:90px;background:#fff;color:var(--text-1)}
.f-textarea:focus{border-color:var(--blue)}

/* ── DOT MENU ─────────────────────────────────────────── */
.dot-menu{background:none;border:none;font-size:18px;color:var(--text-3);padding:3px 7px;border-radius:6px;line-height:1;cursor:pointer}
.dot-menu:hover{background:var(--bg);color:var(--text-1)}
.dm-wrap{position:relative;display:inline-block}
.dm-popup{display:none;position:absolute;right:0;top:calc(100% + 4px);background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 28px rgba(0,0,0,.12);z-index:999;min-width:190px;padding:5px}
.dm-popup.open{display:block}
.dm-item{display:flex;align-items:center;gap:9px;padding:9px 12px;border-radius:7px;font-size:13px;cursor:pointer;color:var(--text-1);text-decoration:none;border:none;background:none;width:100%;text-align:left;box-sizing:border-box}
.dm-item:hover{background:var(--bg)}
.dm-item svg{width:14px;height:14px;flex-shrink:0;stroke:var(--text-3);stroke-width:2}
.dm-div{height:1px;background:var(--border);margin:4px 8px}
.dm-hi{color:var(--blue);font-weight:600}.dm-hi svg{stroke:var(--blue)}
.dm-danger{color:var(--red)}.dm-danger svg{stroke:var(--red)}

/* ── FIRM AVATAR ──────────────────────────────────────── */
.firm-av{width:36px;height:36px;border-radius:9px;background:var(--blue);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* ── ACTIVITY ─────────────────────────────────────────── */
.act-row{display:flex;align-items:flex-start;gap:10px;padding:9px 0;border-bottom:1px solid var(--border)}
.act-row:last-child{border-bottom:none}
.act-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:5px}
.act-txt{font-size:13px;flex:1;line-height:1.4}
.act-time{font-size:11.5px;color:var(--text-3);flex-shrink:0}

/* ── INFO BANNER ──────────────────────────────────────── */
.info-banner{display:flex;align-items:flex-start;gap:10px;padding:10px 14px;background:var(--blue3);border:1px solid #bae3f9;border-radius:9px;margin-bottom:14px}
.info-banner svg{stroke:var(--blue);width:15px;height:15px;flex-shrink:0;margin-top:1px}
.info-banner-txt{font-size:12.5px;color:var(--blue2);line-height:1.5}

/* ── TOAST ────────────────────────────────────────────── */
.toast{position:fixed;bottom:22px;right:22px;padding:11px 18px;border-radius:10px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:8px;box-shadow:0 8px 28px rgba(0,0,0,.18);z-index:2000;white-space:nowrap;transform:translateY(80px);transition:transform .3s cubic-bezier(.34,1.56,.64,1)}
.toast.show{transform:translateY(0)}
.toast-success{background:#0a2540;color:#fff}
.toast-error{background:#ef4444;color:#fff}

/* ── FIRM DETAIL HEADER ───────────────────────────────── */
.firm-detail-header{background:linear-gradient(135deg,#0a2540 0%,var(--blue) 100%);border-radius:var(--r-lg);padding:24px 28px;margin-bottom:16px;display:flex;align-items:center;gap:18px;flex-wrap:wrap}
.firm-detail-av{width:56px;height:56px;border-radius:14px;background:rgba(255,255,255,.15);color:#fff;font-size:20px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;border:2px solid rgba(255,255,255,.2)}
.stat-chip{background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.15);border-radius:8px;padding:10px 16px;text-align:center}
.stat-chip-val{font-size:20px;font-weight:700;color:#fff}
.stat-chip-lbl{font-size:11px;color:rgba(255,255,255,.65);margin-top:2px}

/* ── CHARTS ───────────────────────────────────────────── */
.charts-row{display:grid;gap:14px;margin-bottom:14px}
.charts-2col{grid-template-columns:1.6fr 1fr}

/* ── PAGE LOADER BAR ──────────────────────────────────── */
#pg-loader{position:fixed;top:0;left:0;right:0;height:3px;z-index:99999;pointer-events:none;opacity:0;transition:opacity .2s}
#pg-loader.on{opacity:1}
#pg-loader-fill{height:100%;width:0;background:linear-gradient(90deg,var(--orange),#ffb36b);border-radius:0 2px 2px 0;box-shadow:0 0 8px rgba(240,107,33,.55)}
/* ── BUTTON LOADING STATE ─────────────────────────────── */
.btn-loading{pointer-events:none!important;opacity:.75!important}
.btn-spin{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;animation:spin .65s linear infinite;flex-shrink:0;vertical-align:middle}
@keyframes spin{to{transform:rotate(360deg)}}
    </style>
    @stack('styles')
</head>
<body>

{{-- Global page-load progress bar --}}
<div id="pg-loader" aria-hidden="true"><div id="pg-loader-fill"></div></div>

{{-- Impersonation Banner --}}
@if(session('impersonating_company'))
<div class="sa-impersonate-bar">
    ⚠️ Viewing as <strong>{{ session('impersonating_company_name', 'Tenant') }}</strong>.
    <a href="{{ route('superadmin.stop_impersonation') }}">Stop &amp; Return ↩</a>
</div>
<script>document.documentElement.classList.add('has-impersonate-bar')</script>
@endif

{{-- Sidebar --}}
<aside class="sa-sidebar">
    <div class="sa-logo-area">
        @if($globalSetting?->logo)
            <img src="{{ asset('storage/'.$globalSetting->logo) }}" alt="{{ $globalSetting->global_app_name }}" class="sa-logo-img">
        @else
            <span class="sa-logo-text">{{ $globalSetting?->global_app_name ?? config('app.name', 'CFO Insights') }}</span>
        @endif
        <span class="sa-badge">SuperAdmin Console</span>
    </div>

    <nav class="sa-nav">
        <a href="{{ route('superadmin.dashboard') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>

        <div class="sa-nav-sep"></div>

        <a href="{{ route('superadmin.companies.index') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.companies.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Firms
        </a>
        <a href="{{ route('superadmin.packages.index') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.packages.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Plan Management
        </a>
        <a href="{{ route('superadmin.finance') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.finance*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            Finance
        </a>
        <a href="{{ route('superadmin.settings') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.settings*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Settings
        </a>

        <div class="sa-nav-sep"></div>

        <a href="{{ route('superadmin.users.index') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Users
        </a>
        <a href="{{ route('superadmin.roles.index') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.roles.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Roles &amp; Permissions
        </a>
        <a href="{{ route('superadmin.nav-menu.index') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.nav-menu*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            Menu Visibility
        </a>
        <a href="{{ route('superadmin.support') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.support*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Support
        </a>
        <a href="{{ route('superadmin.account') }}"
           class="sa-nav-item {{ request()->routeIs('superadmin.account*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Account
        </a>
    </nav>

    <div class="sa-sidebar-foot"></div>
</aside>

{{-- Topbar --}}
<div class="sa-topbar">
    <div class="sa-topbar-bc">
        {{ $globalSetting?->global_app_name ?? config('app.name', 'CFO Insights') }}
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        <span>@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="sa-topbar-right">
        <div class="sa-tb-notif">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
            <div class="sa-notif-dot"></div>
        </div>
        <div class="sa-tb-user">
            <div class="sa-tb-av">{{ strtoupper(substr(auth()->user()?->name ?? 'SA', 0, 2)) }}</div>
            <div>
                <div class="sa-tb-uname">{{ auth()->user()?->name }}</div>
                <div class="sa-tb-urole">Super Admin</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="sa-logout-btn" title="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18.36 6.64a9 9 0 11-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
            </button>
        </form>
    </div>
</div>

{{-- Main --}}
<main class="sa-main">

    @yield('content')
</main>

{{-- Toast container --}}
<div id="toast" class="toast toast-success"></div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

// Toast
function showToast(msg, type='success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast toast-' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 3500);
}

// Dot menus
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-menu]');
    if (btn) {
        e.stopPropagation();
        const id = btn.dataset.menu;
        document.querySelectorAll('.dm-popup.open').forEach(p => {
            if (p.id !== id) p.classList.remove('open');
        });
        document.getElementById(id)?.classList.toggle('open');
        return;
    }
    document.querySelectorAll('.dm-popup.open').forEach(p => p.classList.remove('open'));
});

// Tabs
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const bar = btn.closest('.tab-bar');
            const container = bar?.parentElement;
            bar?.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            container?.querySelectorAll(':scope > .tab-panel').forEach(p => p.classList.remove('active'));
            container?.querySelector('#' + btn.dataset.tab)?.classList.add('active');
        });
    });

});

async function apiCall(url, method='POST', body={}) {
    const r = await fetch(url, {
        method,
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        body: method !== 'GET' ? JSON.stringify(body) : undefined
    });
    return r.json();
}
</script>
<script src="{{ asset(config('assets.sweetalert2_js')) }}"></script>
<script>
(function() {
    if (!window.Swal) return;
    const btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#F06B21';
    const swalCfg = { confirmButtonText: 'Ok', confirmButtonColor: btnColor, allowOutsideClick: false };
    @if(session('success'))
    Swal.fire(Object.assign({}, swalCfg, { icon: 'success', text: @json(session('success')) }));
    @elseif(session('error'))
    Swal.fire(Object.assign({}, swalCfg, { icon: 'error', text: @json(session('error')) }));
    @elseif(session('info'))
    Swal.fire(Object.assign({}, swalCfg, { icon: 'info', text: @json(session('info')) }));
    @elseif(session('warning'))
    Swal.fire(Object.assign({}, swalCfg, { icon: 'warning', text: @json(session('warning')) }));
    @endif
}());
</script>
@stack('scripts')
<script>
/* ════════════════════════════════════════════════════════
   GLOBAL LOADING SYSTEM
   ════════════════════════════════════════════════════════ */
(function () {
    var fill = document.getElementById('pg-loader-fill');
    var wrap = document.getElementById('pg-loader');
    if (!fill) return;
    var _active = false, _pct = 0, _timer = null;
    function pgStart() {
        if (_active) return;
        _active = true; _pct = 0;
        clearInterval(_timer);
        fill.style.transition = 'none'; fill.style.width = '0%';
        wrap.classList.add('on');
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                fill.style.transition = 'width .45s ease';
                fill.style.width = '25%'; _pct = 25;
                _timer = setInterval(function () {
                    if (_pct >= 80) return clearInterval(_timer);
                    _pct += Math.random() * 6 + 2;
                    fill.style.transition = 'width 1s ease';
                    fill.style.width = Math.min(_pct, 80) + '%';
                }, 1200);
            });
        });
    }
    function pgDone() {
        if (!_active) return;
        clearInterval(_timer);
        fill.style.transition = 'width .18s ease'; fill.style.width = '100%';
        setTimeout(function () {
            wrap.classList.remove('on');
            setTimeout(function () { fill.style.transition = 'none'; fill.style.width = '0%'; _active = false; _pct = 0; }, 200);
        }, 280);
    }
    window.__pgStart = pgStart; window.__pgDone = pgDone;
    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[href]');
        if (!a) return;
        var h = a.getAttribute('href');
        if (!h || /^(#|javascript:|mailto:|tel:|void)/.test(h)) return;
        if (a.target === '_blank' || a.dataset.noLoader !== undefined) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey) return;
        if (a._pgLoading) { e.preventDefault(); return; }
        pgStart();
        if (a.classList.contains('btn') || a.classList.contains('dm-item')) { __linkLoad(a); }
    });
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || f.dataset.noLoader !== undefined) return;
        pgStart();
    });
    var _orig = window.fetch, _pending = 0;
    window.fetch = function () {
        _pending++;
        var shown = false;
        var showT = setTimeout(function () { shown = true; pgStart(); }, 300);
        var p = _orig.apply(this, arguments);
        function cleanup() { clearTimeout(showT); _pending = Math.max(0, _pending - 1); if (_pending === 0 && shown) pgDone(); }
        p.then(cleanup, cleanup);
        return p;
    };
    window.addEventListener('pageshow', pgDone);
}());
(function () {
    var _lastBtn = null;
    document.addEventListener('mousedown', function (e) { _lastBtn = e.target.closest('button[type="submit"]') || null; }, true);
    document.addEventListener('submit', function (e) {
        var f = e.target;
        if (!f || f.dataset.noLoader !== undefined) return;
        var btn = _lastBtn || f.querySelector('button[type="submit"]');
        _lastBtn = null;
        if (!btn || btn.disabled) return;
        __btnLoad(btn);
        var t = setTimeout(function () { __btnUnload(btn); }, 12000);
        window.addEventListener('pageshow', function () { clearTimeout(t); __btnUnload(btn); }, { once: true });
    });
}());
function __linkLoad(a) {
    if (!a || a._pgLoading) return;
    a._pgLoading = true; a._pgOrigHtml = a.innerHTML;
    var dark = a.classList.contains('dm-item') || a.classList.contains('btn-ghost') || a.classList.contains('btn-secondary');
    var s = dark ? ' style="border-color:rgba(0,0,0,.15);border-top-color:currentColor"' : '';
    a.innerHTML = '<span class="btn-spin"' + s + '></span>' + a.innerHTML;
    a.style.pointerEvents = 'none'; a.style.opacity = '0.72';
}
function __btnLoad(btn) {
    if (!btn || btn._pgLoading) return;
    btn._pgLoading = true; btn._pgOrigHtml = btn.innerHTML;
    var dark = btn.classList.contains('btn-ghost') || btn.classList.contains('btn-secondary');
    var s = dark ? ' style="border-color:rgba(0,0,0,.15);border-top-color:currentColor"' : '';
    btn.innerHTML = '<span class="btn-spin"' + s + '></span>' + btn.innerHTML;
    btn.disabled = true; btn.classList.add('btn-loading');
}
function __btnUnload(btn) {
    if (!btn || !btn._pgLoading) return;
    btn.disabled = false; btn.classList.remove('btn-loading');
    if (btn._pgOrigHtml !== undefined) btn.innerHTML = btn._pgOrigHtml;
    btn._pgLoading = false; btn._pgOrigHtml = null;
}
</script>
</body>
</html>
