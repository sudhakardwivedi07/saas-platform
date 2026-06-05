<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $appName ?? config('app.name'))</title>

    {{-- Favicon --}}
    @if($branding)
        <link rel="icon" type="image/x-icon" href="{{ $branding->favicon_url }}">
    @elseif($globalSetting?->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $globalSetting->favicon) }}">
    @endif

    {{-- Fonts --}}
    <link href="{{ asset(config('assets.font_dm_sans')) }}" rel="stylesheet">

    {{-- FontAwesome 5 --}}
    <link rel="stylesheet" href="{{ asset(config('assets.font_awesome')) }}">

    {{-- DataTables (used on multiple pages) --}}
    <link rel="stylesheet" href="{{ asset(config('assets.datatables_css')) }}">

    {{-- White-label CSS variables from branding --}}
    <!-- DEBUG_BRANDING: company_id={{ $branding?->company_id ?? 'null' }} primary={{ $branding?->primary_color ?? 'null' }} accent={{ $branding?->accent_color ?? 'null' }} -->
    <style>
/* ── BRAND TOKENS ─────────────────────────────────────── */
:root {
  --blue:{{ $branding?->primary_color ?? '#1a6aa8' }};
  --blue-light:#e8f2fb; --blue-pale:#f0f7ff;
  --orange:{{ $branding?->accent_color ?? '#f06b21' }};
  --orange-dark:#d05519; --orange-light:#fef3ec;
  --green:#10b981; --green-light:#ecfdf5;
  --red:#ef4444; --red-light:#fef2f2;
  --amber:#f59e0b; --amber-light:#fffbeb;
  --purple:#7c3aed; --purple-light:#f3f0ff;
  --text-1:#1a202c; --text-2:#6b7280; --text-3:#9ca3af;
  --bg:#f0f4f8; --card:#fff; --border:#e2e8f0;
  --sidebar-w:242px; --topbar-h:58px;
  --r-lg:14px; --r-md:10px;
  --sh-sm:0 1px 4px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
  --sh-md:0 4px 18px rgba(0,0,0,.09),0 2px 5px rgba(0,0,0,.04);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',-apple-system,sans-serif;font-size:14px;color:var(--text-1);background:var(--bg);min-height:100vh;-webkit-font-smoothing:antialiased}
input,select,textarea,button{font-family:inherit}
a{text-decoration:none;color:inherit}
button{cursor:pointer}

/* ── SIDEBAR ──────────────────────────────────────────── */
.ca-sidebar{width:var(--sidebar-w);background:#fff;border-right:1px solid var(--border);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100;box-shadow:2px 0 8px rgba(0,0,0,.04)}
.ca-logo-zone{height:var(--topbar-h);box-sizing:border-box;padding:0 20px;display:flex;align-items:center;border-bottom:1px solid var(--border);flex-shrink:0;background:#fff}
.ca-logo-img{display:block;max-height:36px;max-width:180px;object-fit:contain}
.ca-logo-placeholder{display:flex;align-items:center;justify-content:center;height:38px;border:2px dashed var(--border);border-radius:9px;background:#fafbff;color:var(--text-3);font-size:12px;font-weight:500;letter-spacing:.04em;cursor:pointer;transition:border-color .14s}
.ca-logo-placeholder:hover{border-color:var(--blue);color:var(--blue)}
.ca-partner-tag{display:block;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-3);margin-top:8px;font-weight:500}
.ca-nav{flex:1;padding:8px 0;overflow-y:auto}
.ca-nav-item{display:flex;align-items:center;gap:10px;padding:10px 18px 10px 16px;color:var(--text-2);text-decoration:none;font-size:13px;font-weight:400;cursor:pointer;border-left:3px solid transparent;transition:all .14s ease}
.ca-nav-item:hover{background:#f8fafc;color:var(--text-1)}
.ca-nav-item.active{background:var(--orange-light);color:var(--orange);border-left-color:var(--orange);font-weight:500}
.ca-nav-item svg{width:15px;height:15px;flex-shrink:0;stroke-width:1.8;stroke:var(--text-3)}
.ca-nav-item.active svg{stroke:var(--orange)}
.ca-nav-item:hover svg{stroke:var(--text-1)}
.ca-nav-group{position:relative}
.ca-nav-group-hdr{display:flex;align-items:center;gap:10px;padding:10px 18px 10px 16px;color:var(--text-2);font-size:13px;font-weight:400;cursor:pointer;border-left:3px solid transparent;transition:all .14s ease;user-select:none}
.ca-nav-group-hdr:hover{background:#f8fafc;color:var(--text-1)}
.ca-nav-group-hdr.open,.ca-nav-group-hdr.active-group{background:var(--orange-light);color:var(--orange);border-left-color:var(--orange);font-weight:500}
.ca-nav-group-hdr .nav-icon{width:15px;height:15px;flex-shrink:0;stroke-width:1.8;stroke:var(--text-3)}
.ca-nav-group-hdr.open .nav-icon,.ca-nav-group-hdr.active-group .nav-icon{stroke:var(--orange)}
.ca-nav-group-hdr:hover .nav-icon{stroke:var(--text-1)}
.ca-nav-group-hdr .nav-lbl{flex:1}
.ca-nav-group-hdr .nav-chv{width:12px;height:12px;stroke:var(--text-3);transition:transform .2s;flex-shrink:0}
.ca-nav-group-hdr.open .nav-chv{transform:rotate(90deg)}
.ca-nav-sub{overflow:hidden;max-height:0;transition:max-height .22s ease}
.ca-nav-sub.open{max-height:200px}
.ca-nav-sub-item{display:flex;align-items:center;gap:8px;padding:7px 14px 7px 34px;color:var(--text-2);text-decoration:none;font-size:12.5px;font-weight:400;transition:all .14s ease}
.ca-nav-sub-item:hover{background:#f1f5f9;color:var(--text-1)}
.ca-nav-sub-item.active{color:var(--orange);font-weight:500;background:rgba(240,107,33,.07)}
.ca-nav-sub-item svg{width:13px;height:13px;flex-shrink:0;stroke-width:1.8;stroke:var(--text-3)}
.ca-nav-sub-item.active svg{stroke:var(--orange)}
.ca-nav-sub-item:hover svg{stroke:var(--text-1)}
.ca-sidebar-foot{padding:12px 16px;border-top:1px solid var(--border);display:flex;align-items:center;gap:10px;flex-shrink:0}
.ca-sfoot-av{width:32px;height:32px;border-radius:8px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:#fff;flex-shrink:0}
.ca-sfoot-name{font-size:12.5px;color:var(--text-1);font-weight:500;line-height:1.2}
.ca-sfoot-role{font-size:11px;color:var(--text-3)}
.ca-sfoot-logout{margin-left:auto;color:var(--text-3);cursor:pointer;display:flex;align-items:center;transition:color .14s;background:none;border:none;padding:4px}
.ca-sfoot-logout:hover{color:var(--red)}
.ca-sfoot-logout svg{width:16px;height:16px;stroke:currentColor;stroke-width:2}

/* ── MAIN ─────────────────────────────────────────────── */
.ca-main{margin-left:var(--sidebar-w);flex:1;min-height:100vh;display:flex;flex-direction:column}

/* ── TOPBAR ───────────────────────────────────────────── */
.ca-topbar{height:var(--topbar-h);background:#fff;border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 26px;gap:12px;position:sticky;top:0;z-index:50}
.ca-topbar-bc{font-size:12.5px;color:var(--text-2);display:flex;align-items:center;gap:5px;flex:1;min-width:0}
.ca-topbar-bc span{color:var(--text-1);font-weight:500}
.ca-topbar-bc svg{width:12px;height:12px;flex-shrink:0;stroke:var(--text-3);stroke-width:2}
.ca-tb-right{display:flex;align-items:center;gap:10px;flex-shrink:0}
.ca-tb-notif{width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--text-2);cursor:pointer;transition:background .14s;position:relative;border:1px solid var(--border)}
.ca-tb-notif:hover{background:var(--bg)}
.ca-tb-notif svg{width:16px;height:16px;stroke:var(--text-2);stroke-width:2}
.ca-notif-dot{width:7px;height:7px;background:var(--orange);border-radius:50%;border:2px solid #fff;position:absolute;top:5px;right:6px}
.ca-tb-user{display:flex;align-items:center;gap:9px;cursor:pointer;padding:4px 10px 4px 4px;border-radius:10px;transition:background .14s}
.ca-tb-user:hover{background:var(--bg)}
.ca-tb-av{width:34px;height:34px;border-radius:8px;background:var(--orange);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#fff}
.ca-tb-uname{font-size:13px;font-weight:500;line-height:1.2}
.ca-tb-urole{font-size:11px;color:var(--text-3)}
.ca-logout-btn{width:36px;height:36px;border-radius:9px;background:var(--orange);border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .14s;flex-shrink:0;color:#fff}
.ca-logout-btn:hover{background:var(--orange-dark)}
.ca-logout-btn svg{width:17px;height:17px;stroke:#fff;stroke-width:2.5}

/* ── CONTENT ──────────────────────────────────────────── */
.ca-content{flex:1;padding:24px 28px;background:var(--bg)}

/* ── IMPERSONATION BANNER ─────────────────────────────── */
.ca-impersonate-bar{background:#f59e0b;color:#fff;text-align:center;padding:8px;font-size:13px;font-weight:500}
.ca-impersonate-bar a{color:#fff;text-decoration:underline;margin-left:8px;font-weight:700}

/* ── PAGE HEADER ──────────────────────────────────────── */
.ph{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.ph-left h1{font-size:20px;font-weight:600;line-height:1.2}
.ph-left p{font-size:13px;color:var(--text-2);margin-top:3px}
.ph-actions{display:flex;gap:9px;align-items:center;flex-wrap:wrap}

/* ── BUTTONS ──────────────────────────────────────────── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;border:none;transition:all .14s;text-decoration:none;font-family:inherit;white-space:nowrap}
.btn-primary{background:var(--orange);color:#fff}
.btn-primary:hover{background:var(--orange-dark);box-shadow:0 4px 12px rgba(240,107,33,.28)}
.btn-secondary{background:transparent;color:var(--blue);border:1px solid var(--blue)}
.btn-secondary:hover{background:var(--blue-light)}
.btn-ghost{background:transparent;color:var(--text-2);border:1px solid var(--border)}
.btn-ghost:hover{background:var(--bg);color:var(--text-1)}
.btn-danger{background:var(--red);color:#fff}
.btn-danger:hover{background:#dc2626}
.btn-sm{padding:6px 13px;font-size:12.5px}
.btn-xs{padding:4px 9px;font-size:12px}
.btn svg{width:14px;height:14px}

/* ── KPI GRID ─────────────────────────────────────────── */
.kpi-grid{display:grid;gap:12px;margin-bottom:12px}
.kpi-4{grid-template-columns:repeat(4,1fr)}
.kpi-3{grid-template-columns:repeat(3,1fr)}
.kpi{background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);padding:16px 17px;box-shadow:var(--sh-sm);position:relative;overflow:hidden;transition:box-shadow .18s,transform .18s}
.kpi:hover{box-shadow:var(--sh-md);transform:translateY(-2px)}
.kpi.kpi-bl{border-left:3px solid var(--blue)}
.kpi.kpi-gr{border-left:3px solid var(--green)}
.kpi.kpi-or{border-left:3px solid var(--orange);background:#fff9f6}
.kpi.kpi-rd{border-left:3px solid var(--red);background:#fff5f5}
.kpi.kpi-pu{border-left:3px solid var(--purple)}
.kpi-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px}
.kpi-lbl{font-size:10.5px;font-weight:500;text-transform:uppercase;letter-spacing:.07em;color:var(--text-3)}
.kpi-ico{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.kpi-ico svg{width:16px;height:16px;stroke-width:1.8}
.ico-blue{background:#dbeafe;color:var(--blue)}.ico-blue svg{stroke:var(--blue)}
.ico-orange{background:var(--orange-light);color:var(--orange)}.ico-orange svg{stroke:var(--orange)}
.ico-green{background:var(--green-light);color:var(--green)}.ico-green svg{stroke:var(--green)}
.ico-red{background:var(--red-light);color:var(--red)}.ico-red svg{stroke:var(--red)}
.ico-purple{background:var(--purple-light);color:var(--purple)}.ico-purple svg{stroke:var(--purple)}
.kpi-val{font-size:25px;font-weight:700;line-height:1;margin-bottom:5px}
.kpi-trend{display:flex;align-items:center;gap:3px;font-size:11.5px;font-weight:500;margin-top:4px}
.kpi-trend.up{color:var(--green)}.kpi-trend.down{color:var(--red)}.kpi-trend.neutral{color:var(--text-3)}
.kpi-ctx{font-size:12px;color:var(--text-2);display:flex;align-items:center;gap:4px;margin-top:3px}

/* ── CARDS ────────────────────────────────────────────── */
.card{background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--sh-sm);margin-bottom:14px}
.card-title{font-size:13.5px;font-weight:600;margin-bottom:2px}
.card-sub{font-size:12px;color:var(--text-3);margin-bottom:14px}
.acc-card{background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);padding:22px;box-shadow:var(--sh-sm);margin-bottom:16px}
.acc-section-title{font-size:15px;font-weight:600;margin-bottom:18px}

/* ── TABLES ───────────────────────────────────────────── */
.tc{background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);overflow:visible;box-shadow:var(--sh-sm);margin-bottom:14px}
.tc-head{display:flex;align-items:center;justify-content:space-between;padding:13px 17px;border-bottom:1px solid var(--border);gap:10px;flex-wrap:wrap}
.tc-filters{display:flex;align-items:center;gap:8px;flex:1;flex-wrap:wrap}
table{width:100%;border-collapse:collapse}
thead th{padding:9px 14px;font-size:10.5px;font-weight:500;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);background:#fafbfc;border-bottom:1px solid var(--border);text-align:left;white-space:nowrap}
tbody td{padding:11px 14px;font-size:13px;border-bottom:1px solid var(--border);vertical-align:middle}
tbody tr:last-child td{border-bottom:none}
tbody tr:hover td{background:#fafcff}
.srch-wrap{position:relative;flex:1;max-width:260px}
.srch-wrap svg{position:absolute;left:9px;top:50%;transform:translateY(-50%);width:13px;height:13px;color:var(--text-3);pointer-events:none;stroke:var(--text-3)}
.srch-inp{width:100%;height:34px;border:1px solid var(--border);border-radius:7px;padding:0 9px 0 30px;font-size:13px;font-family:inherit;color:var(--text-1);background:var(--bg);outline:none;transition:border-color .14s}
.srch-inp:focus{border-color:var(--blue);background:#fff}
.sel{height:34px;border:1px solid var(--border);border-radius:7px;padding:0 10px;font-size:12.5px;font-family:inherit;color:var(--text-2);background:var(--bg);outline:none;cursor:pointer}
.sel:focus{border-color:var(--blue)}

/* ── BADGES ───────────────────────────────────────────── */
.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:999px;font-size:11.5px;font-weight:500;white-space:nowrap}
.b-green{background:var(--green-light);color:#065f46}
.b-red{background:var(--red-light);color:#dc2626}
.b-blue{background:#eff6ff;color:#1e40af}
.b-amber{background:var(--amber-light);color:#92400e}
.b-gray{background:#f3f4f6;color:#4b5563}
.b-orange{background:var(--orange-light);color:#9a3900}
.b-purple{background:var(--purple-light);color:#5b21b6}
.badge-dot{width:5px;height:5px;border-radius:50%;flex-shrink:0}

/* ── TABS ─────────────────────────────────────────────── */
.tab-bar{display:flex;align-items:center;border-bottom:2px solid var(--border);margin-bottom:16px;gap:0}
.tab-btn{padding:10px 20px;font-size:13.5px;font-weight:500;color:var(--text-2);cursor:pointer;border:none;background:transparent;font-family:inherit;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .14s;display:flex;align-items:center;gap:7px;white-space:nowrap}
.tab-btn:hover{color:var(--text-1)}
.tab-btn.active{color:var(--orange);border-bottom-color:var(--orange)}
.tab-badge{background:var(--bg);border:1px solid var(--border);color:var(--text-2);border-radius:999px;padding:1px 7px;font-size:11px;font-weight:600}
.tab-btn.active .tab-badge{background:var(--orange-light);border-color:var(--orange);color:var(--orange)}
.tab-panel{display:none}.tab-panel.active{display:block}

/* ── MODALS ───────────────────────────────────────────── */
.modal-overlay{position:fixed;inset:0;z-index:1000;background:rgba(10,25,45,.4);display:none;align-items:center;justify-content:center;padding:20px}
.modal-overlay.open{display:flex}
.modal-box{background:#fff;border-radius:var(--r-lg);padding:26px 30px;width:500px;max-width:100%;max-height:90vh;overflow-y:auto;position:relative}
.modal-close{position:absolute;top:15px;right:15px;width:28px;height:28px;border-radius:6px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:none;border:none;color:var(--text-3);font-size:18px;line-height:1;font-family:inherit;transition:background .14s}
.modal-close:hover{background:var(--bg)}
.modal-title{font-size:17px;font-weight:600;margin-bottom:3px}
.modal-sub{font-size:13px;color:var(--text-2);margin-bottom:20px}
.mf-label{display:block;font-size:13px;font-weight:500;margin-bottom:5px}
.mf-inp{width:100%;height:37px;border:1px solid var(--border);border-radius:8px;padding:0 11px;font-size:13px;font-family:inherit;color:var(--text-1);outline:none;background:#fff;transition:border-color .14s}
.mf-inp:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(26,106,168,.07)}
.mf-sel{width:100%;height:37px;border:1px solid var(--border);border-radius:8px;padding:0 11px;font-size:13px;font-family:inherit;color:var(--text-1);background:#fff;outline:none;cursor:pointer}
.mf-sel:focus{border-color:var(--blue)}
.mf-textarea{width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 11px;font-size:13px;font-family:inherit;color:var(--text-1);background:#fff;outline:none;resize:vertical;min-height:90px;transition:border-color .14s}
.mf-textarea:focus{border-color:var(--blue)}
.mf-field{margin-bottom:15px}
.mf-foot{display:flex;justify-content:flex-end;gap:9px;margin-top:20px}
.form-2col{display:grid;grid-template-columns:1fr 1fr;gap:13px;margin-bottom:13px}

/* ── FORMS ────────────────────────────────────────────── */
.f-label{display:block;font-size:13px;font-weight:500;margin-bottom:5px;color:var(--text-1)}
.f-inp{width:100%;height:37px;border:1px solid var(--border);border-radius:8px;padding:0 11px;font-size:13px;font-family:inherit;color:var(--text-1);outline:none;transition:border-color .14s}
.f-inp:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(26,106,168,.07)}
.f-sel{width:100%;height:37px;border:1px solid var(--border);border-radius:8px;padding:0 11px;font-size:13px;font-family:inherit;color:var(--text-1);background:#fff;outline:none;cursor:pointer}
.f-sel:focus{border-color:var(--blue)}
.f-textarea{width:100%;border:1px solid var(--border);border-radius:8px;padding:9px 11px;font-size:13px;font-family:inherit;color:var(--text-1);background:#fff;outline:none;resize:vertical;min-height:90px}
.f-textarea:focus{border-color:var(--blue)}
.f-group{margin-bottom:14px}

/* ── DOT MENU ─────────────────────────────────────────── */
.dot-menu{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:6px;cursor:pointer;color:var(--text-3);border:none;background:none;font-size:16px;transition:background .14s}
.dot-menu:hover{background:var(--bg);color:var(--text-2)}
.dm-wrap{position:relative;display:inline-block}
.dm-popup{display:none;position:fixed;background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);z-index:9999;min-width:190px;overflow:hidden}
.dm-popup.open{display:block}
.dm-item{display:flex;align-items:center;gap:9px;padding:9px 14px;font-size:13px;color:var(--text-1);cursor:pointer;transition:background .12s;white-space:nowrap;text-decoration:none}
.dm-item:hover{background:var(--bg)}
.dm-item svg{width:14px;height:14px;color:var(--text-3);flex-shrink:0;stroke:var(--text-3);stroke-width:1.8}
.dm-item:hover svg{stroke:var(--blue)}
.dm-divider{height:1px;background:var(--border);margin:3px 0}
.dm-danger{color:var(--red)}
.dm-danger svg{stroke:var(--red)}
.dm-danger:hover{background:#fff5f5}

/* ── ALERTS ───────────────────────────────────────────── */
.ca-alert{padding:10px 14px;border-radius:8px;font-size:13px;display:flex;align-items:center;gap:8px;margin-bottom:14px}
.ca-alert-success{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534}
.ca-alert-error{background:#fff5f5;border:1px solid #fca5a5;color:#991b1b}
.ca-alert-info{background:var(--blue-pale);border:1px solid #bfdbfe;color:#1e40af}
.ca-alert-warning{background:#fffbeb;border:1px solid #fde68a;color:#92400e}
.ca-alert svg{flex-shrink:0;stroke:currentColor}

/* ── INFO BANNER ──────────────────────────────────────── */
.info-banner{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;display:flex;align-items:center;gap:9px;margin-bottom:14px}
.info-banner svg{width:15px;height:15px;flex-shrink:0;stroke:#3b82f6}
.info-banner-txt{flex:1;font-size:12.5px;color:#1e40af;line-height:1.4}

/* ── TOAST ────────────────────────────────────────────── */
.toast{position:fixed;top:18px;right:18px;padding:11px 17px;border-radius:10px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:7px;z-index:2000;transform:translateX(calc(100% + 22px));transition:transform .3s cubic-bezier(.34,1.56,.64,1);box-shadow:var(--sh-md);max-width:370px}
.toast.show{transform:translateX(0)}
.toast-success{background:var(--green);color:#fff}
.toast-error{background:var(--red);color:#fff}
.toast-info{background:var(--blue);color:#fff}
.toast svg{width:15px;height:15px;flex-shrink:0;stroke:#fff}

/* ── BACK BUTTON ──────────────────────────────────────── */
.back-btn{display:inline-flex;align-items:center;gap:7px;padding:7px 14px;border:1px solid var(--blue);border-radius:8px;color:var(--blue);font-size:13px;font-weight:500;cursor:pointer;background:transparent;font-family:inherit;transition:all .14s;margin-bottom:18px;text-decoration:none}
.back-btn:hover{background:var(--blue-light)}
.back-btn svg{width:14px;height:14px;stroke:var(--blue)}

/* ── GLOBAL GENERATION BANNER ─────────────────────────── */
#global-gen-banner{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);z-index:3000;display:none;align-items:center;gap:10px;background:#1a6aa8;color:#fff;padding:11px 20px;border-radius:30px;font-size:13px;font-weight:500;box-shadow:0 4px 18px rgba(0,0,0,.18);white-space:nowrap}
#global-gen-banner svg{width:15px;height:15px;flex-shrink:0;animation:spin 1.4s linear infinite}
#global-gen-banner a{color:#fff;text-decoration:underline;text-underline-offset:2px;opacity:.9}
#global-gen-banner a:hover{opacity:1}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── CHARTS ───────────────────────────────────────────── */
.charts-row{display:grid;grid-template-columns:1.6fr 1fr;gap:14px;margin-bottom:18px}
.act-row{display:flex;align-items:center;gap:11px;padding:8px 0;border-bottom:1px solid var(--border)}
.act-row:last-child{border-bottom:none}
.act-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.act-txt{flex:1;font-size:13px;line-height:1.3}
.act-time{font-size:11.5px;color:var(--text-3);flex-shrink:0}

/* ── CLIENT DETAIL ────────────────────────────────────── */
.cd-header-card{background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);padding:24px 28px;box-shadow:var(--sh-sm);margin-bottom:16px;display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:18px}
.cd-avatar-ring{width:64px;height:64px;border-radius:14px;background:linear-gradient(135deg,var(--blue),#0c84bc);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:#fff;flex-shrink:0;letter-spacing:-.5px}
.cd-name{font-size:20px;font-weight:700;line-height:1.2}
.cd-email{font-size:13px;color:var(--text-3);margin-top:2px}
.cd-stat-chips{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
.cd-chip{display:flex;align-items:center;gap:6px;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:6px 12px;font-size:12.5px}
.cd-chip-val{font-weight:600;color:var(--text-1)}
.cd-chip-lbl{color:var(--text-3)}
.cd-section-grid{display:grid;grid-template-columns:1.4fr 1fr;gap:14px;margin-bottom:14px}

/* ── PLAN BANNER ──────────────────────────────────────── */
.plan-card{background:var(--blue-pale);border:1px solid #bfdbfe;border-left:4px solid var(--blue);border-radius:var(--r-lg);padding:18px 22px;margin-bottom:16px}
.plan-clabel{font-size:10.5px;font-weight:500;text-transform:uppercase;letter-spacing:.1em;color:var(--blue);margin-bottom:5px;display:flex;align-items:center;gap:5px}
.plan-name{font-size:20px;font-weight:700;color:var(--text-1)}
.plan-note{font-size:12px;color:var(--text-2);font-style:italic;border-top:1px solid #dbeafe;padding-top:11px;margin-top:13px}

/* ── BRANDING ─────────────────────────────────────────── */
.brand-layout{display:grid;grid-template-columns:1fr 310px;gap:16px;align-items:start}
.brand-sec{background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--sh-sm);margin-bottom:14px}
.brand-sec-title{font-size:13.5px;font-weight:600;margin-bottom:14px;display:flex;align-items:center;gap:7px}
.upload-zone{border:1.5px dashed #bfdbfe;border-radius:9px;background:var(--blue-pale);padding:22px;text-align:center;cursor:pointer;transition:border-color .14s}
.upload-zone:hover{border-color:var(--blue)}
.upload-zone svg{width:26px;height:26px;color:#93c5fd;margin:0 auto 8px;display:block;stroke:#93c5fd}
.upload-zone p{font-size:13px;color:var(--text-2)}
.preview-sticky{position:sticky;top:70px}
.preview-card{background:#fff;border:1px solid var(--border);border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-sm)}
.preview-head{background:var(--blue);padding:11px 15px;font-size:13px;font-weight:500;color:#fff;display:flex;align-items:center;gap:7px}

/* ── TIME PILLS ───────────────────────────────────────── */
.time-pills{display:flex;border:1px solid var(--border);border-radius:8px;overflow:hidden;background:#fff}
.tp{padding:6px 14px;font-size:12.5px;font-weight:500;color:var(--text-2);cursor:pointer;border:none;background:transparent;font-family:inherit;transition:all .14s;border-right:1px solid var(--border)}
.tp:last-child{border-right:none}
.tp:hover{background:var(--bg)}
.tp.active{background:var(--orange);color:#fff}

/* ── USAGE BAR ────────────────────────────────────────── */
.ubar-wrap{width:80px;height:5px;background:var(--border);border-radius:999px;overflow:hidden;display:inline-block;vertical-align:middle}
.ubar{height:100%;border-radius:999px;background:var(--blue)}

/* ── DATATABLES ───────────────────────────────────────── */
table.dataTable{border-collapse:collapse!important;width:100%!important}
table.dataTable thead th{cursor:pointer;padding:9px 14px!important;font-size:10.5px!important;font-weight:500!important;text-transform:uppercase!important;letter-spacing:.06em!important;color:var(--text-3)!important;background:#fafbfc!important;border-bottom:1px solid var(--border)!important;border-top:none!important;white-space:nowrap}
table.dataTable tbody td{padding:11px 14px!important;font-size:13px!important;border-bottom:1px solid var(--border)!important;border-top:none!important;vertical-align:middle!important}
table.dataTable tbody tr:last-child td{border-bottom:none!important}
table.dataTable tbody tr:hover td{background:#fafcff!important}
div.dataTables_wrapper{padding:0}
div.dataTables_wrapper div.dataTables_info{font-size:12.5px;color:var(--text-3);padding:12px 16px 12px 0;float:left;line-height:32px}
div.dataTables_wrapper div.dataTables_paginate{float:right;padding:10px 0 12px}
div.dataTables_wrapper div.dataTables_paginate .paginate_button{display:inline-block!important;border-radius:7px!important;font-size:13px!important;border:1.5px solid var(--border)!important;background:#fff!important;color:var(--text-1)!important;padding:5px 11px!important;margin:0 2px!important;line-height:1.4!important;cursor:pointer!important;transition:background .12s,border-color .12s!important;text-decoration:none!important}
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current,
div.dataTables_wrapper div.dataTables_paginate .paginate_button.current:hover{background:var(--orange)!important;color:#fff!important;border-color:var(--orange)!important}
div.dataTables_wrapper div.dataTables_paginate .paginate_button:hover:not(.current):not(.disabled){background:var(--bg)!important;border-color:var(--text-3)!important;color:var(--text-1)!important}
div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled,
div.dataTables_wrapper div.dataTables_paginate .paginate_button.disabled:hover{opacity:.38!important;cursor:not-allowed!important;background:#fff!important;border-color:var(--border)!important}
div.dataTables_wrapper::after{content:'';display:table;clear:both}
/* scroll-only wrapper around the table */
div.dataTables_wrapper .dt-tscroll{overflow-x:auto}
/* footer: info left, pagination right */
div.dataTables_wrapper .dt-footer{display:flex;align-items:center;justify-content:space-between;padding:4px 16px 4px 16px;border-top:1px solid var(--border);background:#fafbfc;border-radius:0 0 var(--r-lg) var(--r-lg)}
div.dataTables_wrapper .dt-footer div.dataTables_info{float:none!important;padding:8px 0!important;line-height:1}
div.dataTables_wrapper .dt-footer div.dataTables_paginate{float:none!important;padding:8px 0!important}

/* ── DATE RANGE PICKER ─────────────────────────────────── */
.dr-wrap{position:relative;display:inline-block}
.dr-field{display:flex;align-items:center;gap:8px;height:34px;padding:0 12px;border:1.5px solid var(--border);border-radius:8px;background:#fff;cursor:pointer;user-select:none;font-size:13px;min-width:220px;transition:border-color .14s}
.dr-field:hover,.dr-field.open{border-color:var(--orange)}
.dr-field .dr-cal-ico{flex-shrink:0;width:15px;height:15px;stroke:var(--text-3);transition:stroke .14s}
.dr-field.has-val .dr-cal-ico,.dr-field.open .dr-cal-ico{stroke:var(--orange)}
.dr-text{flex:1;color:var(--text-3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:13px}
.dr-text.has-val{color:var(--text-1)}
.dr-clear-btn{display:none;align-items:center;justify-content:center;width:17px;height:17px;border-radius:50%;background:var(--text-3);color:#fff;font-size:11px;line-height:1;border:none;cursor:pointer;padding:0;flex-shrink:0;transition:background .12s;font-family:inherit}
.dr-clear-btn:hover{background:var(--red,#dc2626)}
.dr-field.has-val .dr-clear-btn{display:flex}
.dr-menu{position:absolute;top:calc(100% + 4px);left:0;z-index:1050;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 28px rgba(0,0,0,.13);min-width:220px;display:none;overflow:hidden}
.dr-menu.open{display:block}
.dr-list{list-style:none;padding:6px 0;margin:0}
.dr-opt{padding:9px 16px;font-size:13px;color:var(--text-1);cursor:pointer;transition:background .1s;white-space:nowrap}
.dr-opt:hover{background:var(--bg)}
.dr-opt.active{background:#1a3f6f;color:#fff}
.dr-opt.active:hover{background:#183766}
.dr-custom-row{display:none;align-items:center;gap:6px;padding:10px 12px;border-top:1px solid var(--border);flex-wrap:wrap}
.dr-date-inp{flex:1;min-width:100px;height:32px;padding:0 8px;border:1px solid var(--border);border-radius:7px;font-size:12.5px;font-family:inherit;color:var(--text-1);outline:none;box-sizing:border-box}
.dr-date-inp:focus{border-color:var(--orange)}
.dr-apply-btn{padding:5px 13px;border:none;border-radius:7px;background:var(--orange);color:#fff;font-size:12.5px;font-weight:600;font-family:inherit;cursor:pointer;flex-shrink:0}
.dr-apply-btn:hover{background:#d95a1a}
/* ── Calendar mode ─────────────────────────────────────── */
.dr-menu.open.dr-cal-mode{display:flex}
.dr-menu.dr-cal-mode{flex-direction:row;min-width:660px;overflow:visible}
.dr-menu.dr-cal-mode .dr-list{border-right:1px solid var(--border);min-width:170px;flex-shrink:0;padding-top:4px}
.dr-cal-panel{display:flex;flex-direction:column;padding:12px 16px;flex:1;min-width:490px}
.dr-cal-months{display:flex;gap:20px}
.dr-month-wrap{flex:1;min-width:0}
.dr-month-hd{display:flex;align-items:center;gap:3px;margin-bottom:8px}
.dr-nav-btn{width:26px;height:26px;border:1px solid var(--border);border-radius:6px;background:#fff;cursor:pointer;font-size:15px;color:var(--text-2);flex-shrink:0;display:flex;align-items:center;justify-content:center;padding:0;line-height:1;transition:background .1s}
.dr-nav-btn:hover{background:var(--bg)}
.dr-mon-sel{height:28px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-family:inherit;color:var(--text-1);background:#fff;padding:0 4px;cursor:pointer;outline:none;flex:1;min-width:0}
.dr-yr-sel{height:28px;border:1px solid var(--border);border-radius:6px;font-size:12px;font-family:inherit;color:var(--text-1);background:#fff;padding:0 4px;cursor:pointer;outline:none;width:60px;flex-shrink:0}
.dr-mon-sel:focus,.dr-yr-sel:focus{border-color:var(--orange)}
.dr-day-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:1px}
.dr-day-hdr{text-align:center;font-size:10.5px;font-weight:700;color:var(--text-3);padding:5px 1px}
.dr-day-cell{text-align:center;font-size:12.5px;padding:6px 1px;cursor:pointer;border-radius:50%;color:var(--text-1);user-select:none;min-width:0}
.dr-day-cell:hover:not(.dr-day-out):not(.dr-day-sel-s):not(.dr-day-sel-e){background:rgba(0,0,0,.06);border-radius:50%}
.dr-day-out{color:var(--text-3);cursor:default;opacity:.38}
.dr-day-today{font-weight:700}
.dr-day-sel-s,.dr-day-sel-e{background:#2563eb!important;color:#fff!important;border-radius:50%!important}
.dr-day-in-range{background:rgba(37,99,235,.13);border-radius:0!important}
.dr-day-hover-preview{background:rgba(37,99,235,.22)!important;border-radius:50%!important}
.dr-cal-footer{display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:10px;border-top:1px solid var(--border)}
.dr-cal-rtxt{font-size:12.5px;color:var(--text-2);flex:1;margin-right:10px}
.dr-cal-btn-cancel{padding:6px 18px;border:none;border-radius:7px;background:#dc2626;color:#fff;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .12s}
.dr-cal-btn-cancel:hover{background:#b91c1c}
.dr-cal-btn-apply{padding:6px 18px;border:none;border-radius:7px;background:#2563eb;color:#fff;font-size:12.5px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .12s}
.dr-cal-btn-apply:hover{background:#1d4ed8}
    </style>
    @stack('styles')
    <link rel="stylesheet" href="{{ asset(config('assets.sweetalert2_css')) }}">
    <style>
    .swal-toast-popup { font-family: inherit !important; font-size: 13.5px !important; border-radius: 10px !important; padding: 10px 16px !important; min-width: 280px; }
    .swal-toast-popup .swal2-title { font-size: 13.5px !important; font-weight: 500 !important; margin: 0 !important; padding: 0 !important; }
    .swal-toast-popup .swal2-icon { width: 22px !important; height: 22px !important; margin: 0 8px 0 0 !important; border-width: 2px !important; }
    .swal-toast-popup .swal2-icon .swal2-icon-content { font-size: 13px !important; }
    </style>
</head>
<body>

{{-- Impersonation Banner --}}
@if(session('impersonating_company'))
<div class="ca-impersonate-bar">
    ⚠️ Viewing as <strong>{{ $tenantCompany?->company_name }}</strong>.
    <a href="{{ route('superadmin.stop_impersonation') }}">Exit ↩</a>
</div>
@endif

{{-- Client Impersonation Banner --}}
@if(session('impersonating_client_original_id'))
<div class="ca-impersonate-bar" style="background:#1a6aa8;display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap">
    <span>
        👤 Logged in as <strong>{{ session('impersonating_client_name') }}</strong>
        <span style="opacity:.75;font-weight:400"> — {{ session('impersonating_client_email') }}</span>
    </span>
    <a href="{{ route('clients.stop-impersonating') }}"
       style="background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.5);color:#fff;border-radius:6px;padding:3px 12px;font-size:12.5px;font-weight:600;text-decoration:none;display:inline-block">
        ↩ Exit — Return to Admin
    </a>
</div>
@endif

<div style="display:flex;min-height:100vh">

{{-- ── Sidebar ──────────────────────────────────────────── --}}
<aside class="ca-sidebar" id="ca-sidebar">
    <div class="ca-logo-zone">
        @if($branding?->logo)
            <img src="{{ asset('storage/' . $branding->logo) }}" alt="Logo" class="ca-logo-img">
        @else
            <a class="ca-logo-placeholder">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-right:5px"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Your Logo
            </a>
        @endif
    </div>

    <nav class="ca-nav">
    @php
        // When admin is impersonating a client always render the client nav,
        // regardless of what role the client user happens to have.
        $__navRole  = session('impersonating_client_original_id')
            ? 'client'
            : (auth()->user()->getRoleNames()->first() ?? 'client');
        $__navItems = \App\Models\NavItem::getForRole($__navRole, auth()->user());
    @endphp

    @foreach($__navItems as $__nir)
    @php
        // active_route_pattern is comma-separated, e.g. "quickbooks.*,firm-quickbooks.*"
        $__patterns = array_values(array_filter(array_map('trim', explode(',', $__nir->active_route_pattern))));
        $__isActive = !empty($__patterns) && request()->routeIs($__patterns);
        try { $__href = route($__nir->route_name); } catch (\Exception $e) { $__href = '#'; }
    @endphp
    <a href="{{ $__href }}" class="ca-nav-item {{ $__isActive ? 'active' : '' }}">
        {!! $__nir->navItem->icon_svg !!}
        {{ $__nir->navItem->label }}
    </a>
    @endforeach

    </nav>

    <!-- <div class="ca-sidebar-foot"> -->
        <!-- <div class="ca-sfoot-av">{{ strtoupper(substr($authUser->name ?? 'U', 0, 2)) }}</div> -->
        <!-- <div style="flex:1;min-width:0">
            <div class="ca-sfoot-name">{{ $authUser->name }}</div>
            <div class="ca-sfoot-role">{{ $authUser->roleDisplayName('Admin') }}</div>
        </div> -->
        <!-- <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="ca-sfoot-logout" title="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
        </form> -->
    <!-- </div> -->
</aside>

{{-- ── Main ──────────────────────────────────────────────── --}}
<div class="ca-main">
    {{-- Topbar --}}
    <header class="ca-topbar">
        <div class="ca-topbar-bc">
            {{ $appName ?? config('app.name') }}
            <svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
            <span>@yield('page-title', 'Dashboard')</span>
        </div>
        <div class="ca-tb-right">
            <div class="ca-tb-notif">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
                <div class="ca-notif-dot"></div>
            </div>
            <div class="ca-tb-user">
                <div class="ca-tb-av">{{ strtoupper(substr($authUser->name ?? 'U', 0, 2)) }}</div>
                <div>
                    <div class="ca-tb-uname">{{ $authUser->name }}</div>
                    <div class="ca-tb-urole">{{ $authUser->roleDisplayName('Admin') }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="ca-logout-btn" title="Logout">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M18.36 6.64a9 9 0 11-12.73 0"/><line x1="12" y1="2" x2="12" y2="12"/></svg>
                </button>
            </form>
        </div>
    </header>

    {{-- Content --}}
    <div class="ca-content">

        @yield('content')
    </div>
</div>

</div>{{-- flex wrapper --}}

{{-- Toast --}}
<div id="ca-toast" class="toast toast-success"></div>

{{-- Global insight-generation floating banner (shown on non-insights pages while a job runs) --}}
<div id="global-gen-banner">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
    </svg>
    <span id="global-gen-msg">Generating insights in the background…</span>
    <a id="global-gen-link" href="{{ route('insights.index') }}">View Progress &rarr;</a>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, type='success') {
    const t = document.getElementById('ca-toast');
    t.textContent = msg;
    t.className = 'toast toast-' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 3500);
}

// ── Global insight-generation notification ────────────────────────────────
(function () {
    const LS_KEY          = 'insights_processing_ids';
    const DASH_LS_KEY     = 'dash_processing_insight_id';
    const INSIGHTS_URL    = '{{ route("insights.index") }}';
    const RELOAD_ONCE_KEY = 'insights_reload_done_for';
    let pollTimer         = null;
    let pollInFlight      = false;

    function parseStoredIds(raw) {
        if (!raw) return [];
        try {
            const parsed = JSON.parse(raw);
            if (Array.isArray(parsed)) {
                return parsed.map(v => parseInt(String(v), 10)).filter(v => Number.isFinite(v) && v > 0);
            }
            const n = parseInt(String(parsed), 10);
            return Number.isFinite(n) && n > 0 ? [n] : [];
        } catch (_) {
            const n = parseInt(String(raw), 10);
            return Number.isFinite(n) && n > 0 ? [n] : [];
        }
    }

    function getProcessingIds() {
        const ids = [
            ...parseStoredIds(localStorage.getItem(LS_KEY)),
            ...parseStoredIds(localStorage.getItem(DASH_LS_KEY)),
        ];
        return Array.from(new Set(ids));
    }

    function getPrimaryProcessingId() {
        return getProcessingIds()[0] ?? null;
    }

    function clearProcessingIds() {
        localStorage.removeItem(LS_KEY);
        localStorage.removeItem(DASH_LS_KEY);
    }

    function scheduleNext(ms) {
        if (pollTimer) clearTimeout(pollTimer);
        pollTimer = setTimeout(checkGlobalProgress, ms);
    }

    function checkGlobalProgress() {
        if (pollInFlight) return;
        const processingId = getPrimaryProcessingId();
        const banner = document.getElementById('global-gen-banner');
        if (!processingId) {
            if (banner) banner.style.display = 'none';
            return;
        }

        // Don't show on the insights page itself — it has its own bar
        if (window.location.pathname.includes('/insights')) return;

        // Show the floating banner
        const msg    = document.getElementById('global-gen-msg');
        const lnk    = document.getElementById('global-gen-link');
        if (!banner) return;

        const path = window.location.pathname;
        // Don't show on any insights page — they have their own progress bars
        if (path.includes('/insights')) return;

        const reportId = processingId;
        const url      = '/insights/status/' + reportId;
        const viewHref = '{{ route("insights.index") }}';

        if (msg) msg.textContent = 'Generating insights in the background…';
        if (lnk) lnk.href = viewHref;
        banner.style.display = 'flex';

        pollInFlight = true;
        fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => {
            if (!r.ok) {
                clearProcessingIds();
                const stillRunning = getPrimaryProcessingId();
                if (!stillRunning) banner.style.display = 'none';
                else scheduleNext(5000);
                return Promise.reject('not-found');
            }
            return r.json();
        })
        .then(data => {
            if (data.status === 'error') {
                clearProcessingIds();
                const stillRunning = getPrimaryProcessingId();
                if (!stillRunning) banner.style.display = 'none';
                else scheduleNext(5000);
                return;
            }
            if (data.status !== 'success') { scheduleNext(10000); return; }
            const r = data.report;
            if (r.has_insights || r.is_failed) {
                clearProcessingIds();
                if (banner) banner.style.display = 'none';
                const alreadyReloadedFor = sessionStorage.getItem(RELOAD_ONCE_KEY);
                if (
                    shouldReloadOnInsightComplete() &&
                    !window.location.pathname.includes('/insights') &&
                    String(alreadyReloadedFor || '') !== String(r.id)
                ) {
                    sessionStorage.setItem(RELOAD_ONCE_KEY, String(r.id));
                    window.location.reload();
                }
            } else {
                scheduleNext(8000);
            }
        })
        .catch(() => scheduleNext(15000))
        .finally(() => { pollInFlight = false; });
}

function shouldReloadOnInsightComplete() {
    return document.getElementById('insights-refresh-target') !== null;
}

document.addEventListener('DOMContentLoaded', checkGlobalProgress);
window.addEventListener('storage', function(event) {
    if ((event.key === LS_KEY || event.key === DASH_LS_KEY) && event.newValue) {
        if (pollTimer) {
            clearTimeout(pollTimer);
            pollTimer = null;
        }
        checkGlobalProgress();
    }
});
}());

document.addEventListener('DOMContentLoaded', () => {
    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const bar = btn.closest('.tab-bar');
            const container = bar?.parentElement;
            const tabId = btn.dataset.tab;
            bar?.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            container?.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            container?.querySelector('#' + tabId)?.classList.add('active');
            // Notify page-level scripts so they can refresh the tab's data
            document.dispatchEvent(new CustomEvent('tabActivated', { detail: { tabId } }));
        });
    });
});

// Dot menus — position:fixed so they escape overflow:auto containers
document.addEventListener('click', function(e) {
    const btn = e.target.closest('[data-menu]');
    if (btn) {
        e.stopPropagation();
        const id  = btn.dataset.menu;
        const popup = document.getElementById(id);
        const isOpen = popup?.classList.contains('open');

        // Close all open popups
        document.querySelectorAll('.dm-popup.open').forEach(p => p.classList.remove('open'));

        if (!isOpen && popup) {
            // Position fixed relative to button
            const rect = btn.getBoundingClientRect();
            popup.style.top  = (rect.bottom + 4) + 'px';
            // Align right edge of popup to right edge of button
            popup.style.left = 'auto';
            popup.style.right = 'auto';
            const popupWidth = 190;
            let left = rect.right - popupWidth;
            if (left < 8) left = 8;
            popup.style.left = left + 'px';
            popup.classList.add('open');
        }
        return;
    }
    document.querySelectorAll('.dm-popup.open').forEach(p => p.classList.remove('open'));
});

// Reposition open popup on scroll/resize
['scroll','resize'].forEach(ev => window.addEventListener(ev, function() {
    document.querySelectorAll('.dm-popup.open').forEach(p => p.classList.remove('open'));
}, true));

async function apiCall(url, method='POST', body={}) {
    const r = await fetch(url, {
        method,
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        body: method !== 'GET' ? JSON.stringify(body) : undefined
    });
    return r.json();
}
</script>
<script src="{{ asset(config('assets.jquery')) }}"></script>
<script src="{{ asset(config('assets.datatables_js')) }}"></script>
<script src="{{ asset(config('assets.sweetalert2_js')) }}"></script>
<script>
window.Swal = window.Swal || window.Sweetalert2 || null;
window.showAppLoading = function(title, text) {
    if (!window.Swal) return;
    window.Swal.fire({
        title: title || 'Loading',
        text: text || 'Please wait...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: function() {
            window.Swal.showLoading();
        }
    });
};
window.hideAppLoading = function() {
    if (window.Swal && window.Swal.isVisible && window.Swal.isVisible()) {
        window.Swal.close();
    }
};
const SwalToast = window.Swal ? window.Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    customClass: { popup: 'swal-toast-popup' },
    didOpen: function(toast) {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
}) : null;
@if(session('toast_success'))
SwalToast && SwalToast.fire({ icon: 'success', title: @json(session('toast_success')) });
@endif
@if(session('toast_warning'))
SwalToast && SwalToast.fire({ icon: 'warning', title: @json(session('toast_warning')) });
@endif
@if(session('toast_error'))
SwalToast && SwalToast.fire({ icon: 'error', title: @json(session('toast_error')) });
@endif
(function() {
    if (!window.Swal) return;
    const btnColor = getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21';
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
<script>
function toggleNavGroup(hdr) {
    var sub = hdr.nextElementSibling;
    hdr.classList.toggle('open');
    sub.classList.toggle('open');
}
</script>
<script>
// ── Global Date Range Picker ──────────────────────────────
var DR_FMT   = '{{ active_date_format() }}';
var _drCbs   = {};   // callback registry: id → function(from, to)
var _drCal   = {};   // calendar state registry: id → { y1,m1,y2,m2,start,end,hover }

// Format a Y-m-d string using the active date format
function drFmt(ymd) {
    if (!ymd) return '';
    var p = ymd.split('-');
    if (p.length < 3) return ymd;
    var y = +p[0], mo = +p[1] - 1, d = +p[2];
    var MS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var ML = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var map = { F:ML[mo], M:MS[mo], Y:String(y), m:String(mo+1).padStart(2,'0'), d:String(d).padStart(2,'0'), j:String(d) };
    return DR_FMT.replace(/[FMYmdj]/g, function(tok) {
        return Object.prototype.hasOwnProperty.call(map, tok) ? map[tok] : tok;
    });
}

// Format a JS Date as YYYY-MM-DD
function drYmd(dt) {
    var y = dt.getFullYear();
    var m = String(dt.getMonth() + 1).padStart(2, '0');
    var d = String(dt.getDate()).padStart(2, '0');
    return y + '-' + m + '-' + d;
}

// Calculate from/to for built-in presets
function drPresetRange(preset) {
    var now = new Date(); now.setHours(0,0,0,0);
    var from, to = drYmd(now);
    switch (preset) {
        case 'today':     from = to; break;
        case 'last30':    { var d=new Date(now); d.setDate(d.getDate()-30);   from=drYmd(d); break; }
        case 'thisMonth': { from=drYmd(new Date(now.getFullYear(),now.getMonth(),1));
                            to=drYmd(new Date(now.getFullYear(),now.getMonth()+1,0)); break; }
        case 'lastMonth': { from=drYmd(new Date(now.getFullYear(),now.getMonth()-1,1));
                            to=drYmd(new Date(now.getFullYear(),now.getMonth(),0)); break; }
        case 'last90':    { var d=new Date(now); d.setDate(d.getDate()-90);   from=drYmd(d); break; }
        case 'last6m':    { var d=new Date(now); d.setMonth(d.getMonth()-6);  from=drYmd(d); break; }
        case 'last1y':    { var d=new Date(now); d.setFullYear(d.getFullYear()-1); from=drYmd(d); break; }
        default: return null;
    }
    return { from: from, to: to };
}

// Toggle the dropdown open/closed
function drOpen(id) {
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (!menu) return;
    // Close other open menus
    document.querySelectorAll('.dr-menu.open').forEach(function(m) {
        if (m.id !== id + '-menu') {
            var wid = m.id.replace('-menu', '');
            m.classList.remove('open');
            var otherField = document.querySelector('#' + wid + ' .dr-field');
            if (otherField) otherField.classList.remove('open');
            drHideCalendar(wid);
        }
    });
    var isOpen = menu.classList.toggle('open');
    if (field) { isOpen ? field.classList.add('open') : field.classList.remove('open'); }
    if (!isOpen) drHideCalendar(id);
}

// Handle preset or "Custom Range" selection
function drPickPreset(id, preset, el) {
    // Mark active
    var list = el.closest('.dr-list');
    if (list) list.querySelectorAll('.dr-opt').forEach(function(li) { li.classList.remove('active'); });
    el.classList.add('active');

    if (preset === 'custom') {
        // Hide the simple custom row (if present) and show the calendar
        var customRow = document.getElementById(id + '-custom');
        if (customRow) customRow.style.display = 'none';
        drShowCalendar(id);
        return; // keep menu open
    }

    // Non-custom preset: hide calendar + wipe its state so next Custom Range opens clean
    drHideCalendar(id);
    delete _drCal[id];
    var customRow = document.getElementById(id + '-custom');
    if (customRow) customRow.style.display = 'none';

    var range = drPresetRange(preset);
    if (!range) return;

    // Store values in hidden inputs
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    if (fromEl) fromEl.value = range.from;
    if (toEl)   toEl.value   = range.to;

    // Update display
    drSetLabel(id, range.from, range.to);

    // Close menu
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (menu)  menu.classList.remove('open');
    if (field) field.classList.remove('open');

    // Fire callback
    var cb = _drCbs[id];
    if (typeof cb === 'function') cb(range.from, range.to);
}

// Apply a custom (manually entered) date range
function drApplyCustom(id) {
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    var from   = fromEl ? fromEl.value : '';
    var to     = toEl   ? toEl.value   : '';
    if (!from || !to) { return; }
    if (from > to) {
        if (window.Swal) {
            Swal.fire({ icon:'warning', title:'Invalid range', text:'Start date must be before end date.',
                confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--orange').trim() || '#f06b21' });
        }
        return;
    }
    drSetLabel(id, from, to);
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (menu)  menu.classList.remove('open');
    if (field) field.classList.remove('open');
    var cb = _drCbs[id];
    if (typeof cb === 'function') cb(from, to);
}

// Update the input field label
function drSetLabel(id, from, to) {
    var textEl = document.getElementById(id + '-text');
    var field  = document.querySelector('#' + id + ' .dr-field');
    if (from || to) {
        if (textEl) { textEl.textContent = drFmt(from) + ' to ' + drFmt(to); textEl.classList.add('has-val'); }
        if (field)  field.classList.add('has-val');
    } else {
        if (textEl) { textEl.textContent = 'Start Date To End Date'; textEl.classList.remove('has-val'); }
        if (field)  field.classList.remove('has-val');
    }
}

// Programmatic clear (called from "Clear Filters" buttons)
function drClear(id) {
    document.querySelectorAll('#' + id + ' .dr-opt').forEach(function(li) { li.classList.remove('active'); });
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    if (fromEl) fromEl.value = '';
    if (toEl)   toEl.value   = '';
    var customRow = document.getElementById(id + '-custom');
    if (customRow) customRow.style.display = 'none';
    drSetLabel(id, '', '');
    // Clean up calendar
    drHideCalendar(id);
    if (_drCal[id]) { _drCal[id].start = null; _drCal[id].end = null; _drCal[id].hover = null; }
    var menu  = document.getElementById(id + '-menu');
    var field = document.querySelector('#' + id + ' .dr-field');
    if (menu)  menu.classList.remove('open');
    if (field) { field.classList.remove('open'); field.classList.remove('has-val'); }
    var cb = _drCbs[id];
    if (typeof cb === 'function') cb('', '');
}

// Close all date menus on outside click
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dr-wrap')) {
        document.querySelectorAll('.dr-menu.open').forEach(function(m) {
            m.classList.remove('open');
            var wrapId = m.id.replace('-menu', '');
            var field  = document.querySelector('#' + wrapId + ' .dr-field');
            if (field) field.classList.remove('open');
            drHideCalendar(wrapId);
        });
    }
});

// ── Dual-calendar picker ──────────────────────────────────
var _DR_MON = ['January','February','March','April','May','June','July','August','September','October','November','December'];
var _DR_DAY = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

function drShowCalendar(id) {
    var menu = document.getElementById(id + '-menu');
    if (!menu) return;

    // Initialise or refresh state from hidden inputs
    var fromEl = document.getElementById(id + '-from');
    var toEl   = document.getElementById(id + '-to');
    var exFrom = fromEl ? fromEl.value : '';
    var exTo   = toEl   ? toEl.value   : '';

    if (!_drCal[id]) {
        var now = new Date();
        var y1, m1, y2, m2;
        if (exFrom) { var p = exFrom.split('-'); y1 = +p[0]; m1 = +p[1] - 1; }
        else        { y1 = now.getFullYear(); m1 = now.getMonth(); }
        if (exTo)   { var p = exTo.split('-');   y2 = +p[0]; m2 = +p[1] - 1; }
        else        { m2 = m1 === 11 ? 0 : m1 + 1; y2 = m1 === 11 ? y1 + 1 : y1; }
        if (y1 === y2 && m1 === m2) { m2 = m1 === 11 ? 0 : m1 + 1; y2 = m1 === 11 ? y1 + 1 : y1; }
        // Only use hidden inputs for month navigation positioning, not for pre-selecting dates
        _drCal[id] = { y1: y1, m1: m1, y2: y2, m2: m2, start: null, end: null, hover: null };
    }
    // Do not sync start/end from hidden inputs — always open with a clean slate

    menu.classList.add('dr-cal-mode');

    // Position menu as position:fixed so it doesn't cause document scrollbars
    // and doesn't overflow the viewport
    var fieldEl = document.querySelector('#' + id + ' .dr-field');
    if (fieldEl) {
        var rect  = fieldEl.getBoundingClientRect();
        var calW  = 664; // approximate calendar width
        var left  = rect.left;
        // Clamp to viewport so calendar never goes off-screen right
        if (left + calW > window.innerWidth - 8) {
            left = Math.max(8, window.innerWidth - calW - 8);
        }
        menu.style.position = 'fixed';
        menu.style.top      = (rect.bottom + 4) + 'px';
        menu.style.left     = left + 'px';
    }

    // Remove existing panel
    var old = document.getElementById(id + '-cal-panel');
    if (old) old.remove();

    // Create panel
    var panel = document.createElement('div');
    panel.id        = id + '-cal-panel';
    panel.className = 'dr-cal-panel';
    menu.appendChild(panel);

    drRenderCal(id);

    // Bind delegated events.
    // stopPropagation prevents the document outside-click handler from firing when
    // the user clicks inside the calendar (which would detach the target element via
    // innerHTML replacement and cause closest('.dr-wrap') to return null, closing the menu).
    panel.addEventListener('click',     function(e) { e.stopPropagation(); drCalClick(e, id); });
    panel.addEventListener('change',    function(e) { drCalChange(e, id); });
    panel.addEventListener('mouseover', function(e) { drCalHover(e, id); });
    panel.addEventListener('mouseleave',function()  { drCalHoverEnd(id); });
}

function drHideCalendar(id) {
    var menu = document.getElementById(id + '-menu');
    if (menu) {
        menu.classList.remove('dr-cal-mode');
        // Reset fixed positioning
        menu.style.position = '';
        menu.style.top      = '';
        menu.style.left     = '';
    }
    var panel = document.getElementById(id + '-cal-panel');
    if (panel) panel.remove();
}

function drRenderCal(id) {
    var panel = document.getElementById(id + '-cal-panel');
    if (!panel) return;
    var s = _drCal[id];
    panel.innerHTML =
        '<div class="dr-cal-months" id="' + id + '-cal-months">' +
            drBuildMonth(id, s.y1, s.m1, 1) +
            drBuildMonth(id, s.y2, s.m2, 2) +
        '</div>' +
        '<div class="dr-cal-footer">' +
            '<span class="dr-cal-rtxt" id="' + id + '-cal-rtxt">' + drCalRangeTxt(id) + '</span>' +
            '<div style="display:flex;gap:8px;">' +
                '<button class="dr-cal-btn-cancel" data-cal-cancel="' + id + '">Cancel</button>' +
                '<button class="dr-cal-btn-apply"  data-cal-apply="'  + id + '">Apply</button>' +
            '</div>' +
        '</div>';
}

function drRefreshCalMonths(id) {
    var wrap = document.getElementById(id + '-cal-months');
    if (!wrap) return;
    var s = _drCal[id];
    wrap.innerHTML = drBuildMonth(id, s.y1, s.m1, 1) + drBuildMonth(id, s.y2, s.m2, 2);
    var rtxt = document.getElementById(id + '-cal-rtxt');
    if (rtxt) rtxt.textContent = drCalRangeTxt(id);
}

function drCalRangeTxt(id) {
    var s = _drCal[id];
    if (!s) return 'Select a date range';
    if (s.start && s.end) return drFmt(s.start) + ' To ' + drFmt(s.end);
    if (s.start)          return drFmt(s.start) + ' To ...';
    return 'Select a date range';
}

function drBuildMonth(id, year, month, side) {
    var s = _drCal[id];
    var curY = new Date().getFullYear();
    var todayStr = drYmd(new Date());

    // Header
    var h = '<div class="dr-month-wrap">';
    h += '<div class="dr-month-hd">';
    h += '<button class="dr-nav-btn" data-nav="' + id + '" data-side="' + side + '" data-dir="-1">&#8249;</button>';

    // Month select
    h += '<select class="dr-mon-sel" data-sel="' + id + '" data-side="' + side + '" data-type="m">';
    for (var mi = 0; mi < 12; mi++) {
        h += '<option value="' + mi + '"' + (mi === month ? ' selected' : '') + '>' + _DR_MON[mi] + '</option>';
    }
    h += '</select>';

    // Year select
    h += '<select class="dr-yr-sel" data-sel="' + id + '" data-side="' + side + '" data-type="y">';
    for (var yi = curY - 5; yi <= curY + 3; yi++) {
        h += '<option value="' + yi + '"' + (yi === year ? ' selected' : '') + '>' + yi + '</option>';
    }
    h += '</select>';

    h += '<button class="dr-nav-btn" data-nav="' + id + '" data-side="' + side + '" data-dir="1">&#8250;</button>';
    h += '</div>';

    // Day header row
    h += '<div class="dr-day-grid">';
    for (var di = 0; di < 7; di++) { h += '<div class="dr-day-hdr">' + _DR_DAY[di] + '</div>'; }

    // Calculate grid positions
    var firstDow   = new Date(year, month, 1).getDay();           // 0=Sun
    var startCol   = firstDow === 0 ? 6 : firstDow - 1;           // Mon=0 … Sun=6
    var daysInMon  = new Date(year, month + 1, 0).getDate();
    var prevMonLen = new Date(year, month, 0).getDate();

    // Prev-month padding
    for (var pi = 0; pi < startCol; pi++) {
        h += '<div class="dr-day-cell dr-day-out">' + (prevMonLen - startCol + pi + 1) + '</div>';
    }

    // Current-month days
    for (var day = 1; day <= daysInMon; day++) {
        var mm  = String(month + 1).padStart(2, '0');
        var dd  = String(day).padStart(2, '0');
        var ymd = year + '-' + mm + '-' + dd;

        // Determine active range bounds (considering hover preview when end not set)
        var lo = null, hi = null;
        if (s.start && s.end) {
            lo = s.start; hi = s.end;
        } else if (s.start && s.hover) {
            lo = s.start < s.hover ? s.start : s.hover;
            hi = s.start < s.hover ? s.hover : s.start;
        }

        var isStart    = ymd === s.start;
        var isEnd      = (s.end && ymd === s.end) || (!s.end && s.hover && ymd === s.hover && ymd !== s.start);
        var inRange    = lo && hi && ymd > lo && ymd < hi;
        var isToday    = ymd === todayStr;

        var cls = 'dr-day-cell';
        if (isStart) cls += ' dr-day-sel-s';
        if (isEnd)   cls += ' dr-day-hover-preview';  // hover end
        if (s.end && ymd === s.end) { cls = cls.replace('dr-day-hover-preview', ''); cls += ' dr-day-sel-e'; }
        if (inRange) cls += ' dr-day-in-range';
        if (isToday && !isStart && !(s.end && ymd === s.end)) cls += ' dr-day-today';

        h += '<div class="' + cls + '" data-ymd="' + ymd + '">' + day + '</div>';
    }

    // Next-month padding
    var total = startCol + daysInMon;
    var tail  = total % 7 === 0 ? 0 : 7 - (total % 7);
    for (var ti = 1; ti <= tail; ti++) { h += '<div class="dr-day-cell dr-day-out">' + ti + '</div>'; }

    h += '</div></div>'; // dr-day-grid + dr-month-wrap
    return h;
}

// --- Calendar event handlers ---

function drCalClick(e, id) {
    // Nav arrows
    var nav = e.target.closest('[data-nav]');
    if (nav && nav.dataset.nav === id) {
        var s   = _drCal[id];
        var side = +nav.dataset.side;
        var dir  = +nav.dataset.dir;
        if (side === 1) {
            s.m1 += dir;
            if (s.m1 > 11) { s.m1 = 0;  s.y1++; }
            if (s.m1 < 0)  { s.m1 = 11; s.y1--; }
        } else {
            s.m2 += dir;
            if (s.m2 > 11) { s.m2 = 0;  s.y2++; }
            if (s.m2 < 0)  { s.m2 = 11; s.y2--; }
        }
        drRefreshCalMonths(id);
        return;
    }

    // Day cell — only current-month cells have [data-ymd], so this selector is reliable
    var cell = e.target.closest('[data-ymd]');
    if (cell && cell.dataset.ymd) {
        var ymd = cell.dataset.ymd;
        var s   = _drCal[id];
        if (!s.start || (s.start && s.end)) {
            // Start a new selection
            s.start = ymd; s.end = null; s.hover = null;
        } else {
            // Complete the selection
            if (ymd < s.start) { s.end = s.start; s.start = ymd; }
            else                { s.end = ymd; }
            s.hover = null;
        }
        drRefreshCalMonths(id);
        return;
    }

    // Cancel button
    if (e.target.closest('[data-cal-cancel]')) { drClear(id); return; }

    // Apply button
    var applyBtn = e.target.closest('[data-cal-apply]');
    if (applyBtn) {
        var s = _drCal[id];
        if (!s || !s.start || !s.end) return;
        // Store in hidden inputs
        var fromEl = document.getElementById(id + '-from');
        var toEl   = document.getElementById(id + '-to');
        if (fromEl) fromEl.value = s.start;
        if (toEl)   toEl.value   = s.end;
        // Update label
        drSetLabel(id, s.start, s.end);
        // Close menu + calendar (drHideCalendar resets position:fixed too)
        drHideCalendar(id);
        var menu  = document.getElementById(id + '-menu');
        var field = document.querySelector('#' + id + ' .dr-field');
        if (menu)  menu.classList.remove('open');
        if (field) field.classList.remove('open');
        // Fire callback
        var cb = _drCbs[id];
        if (typeof cb === 'function') cb(s.start, s.end);
        return;
    }
}

function drCalChange(e, id) {
    var sel = e.target.closest('[data-sel]');
    if (!sel || sel.dataset.sel !== id) return;
    var s    = _drCal[id];
    var side = +sel.dataset.side;
    var val  = +sel.value;
    if (sel.dataset.type === 'm') { if (side === 1) s.m1 = val; else s.m2 = val; }
    else                          { if (side === 1) s.y1 = val; else s.y2 = val; }
    drRefreshCalMonths(id);
}

function drCalHover(e, id) {
    var cell = e.target.closest('[data-ymd]');
    if (!cell || !cell.dataset.ymd) return;
    var s = _drCal[id];
    if (!s || !s.start || s.end) return;
    if (s.hover === cell.dataset.ymd) return;
    s.hover = cell.dataset.ymd;
    drRefreshCalMonths(id);
}

function drCalHoverEnd(id) {
    var s = _drCal[id];
    if (!s || !s.start || s.end) return;
    s.hover = null;
    drRefreshCalMonths(id);
}
</script>
@stack('scripts')
</body>
</html>
