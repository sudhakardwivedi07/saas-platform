<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $appName ?? config('app.name'))</title>

    {{-- Favicon --}}
    @if($branding?->favicon)
        <link rel="icon" href="{{ asset('storage/' . $branding->favicon) }}">
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">

    {{-- DataTables (used on multiple pages) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    {{-- White-label CSS variables from branding --}}
    <style>
/* ── BRAND TOKENS ─────────────────────────────────────── */
:root {
  --blue:{{ $branding->primary_color ?? '#1a6aa8' }};
  --blue-light:#e8f2fb; --blue-pale:#f0f7ff;
  --orange:{{ $branding->accent_color ?? '#f06b21' }};
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
.ca-logo-zone{padding:16px 20px 14px;border-bottom:1px solid var(--border);flex-shrink:0;background:#fff}
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
.dm-popup{display:none;position:absolute;right:0;top:calc(100% + 4px);background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.18);z-index:9999;min-width:190px;overflow:hidden}
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
    </style>
    @stack('styles')
</head>
<body>

{{-- Impersonation Banner --}}
@if(session('impersonating_company'))
<div class="ca-impersonate-bar">
    ⚠️ Viewing as <strong>{{ $tenantCompany?->company_name }}</strong>.
    <a href="{{ route('superadmin.stop_impersonation') }}">Exit ↩</a>
</div>
@endif

<div style="display:flex;min-height:100vh">

{{-- ── Sidebar ──────────────────────────────────────────── --}}
<aside class="ca-sidebar" id="ca-sidebar">
    <div class="ca-logo-zone">
        @if($branding?->logo)
            <img src="{{ asset('storage/' . $branding->logo) }}" alt="Logo" class="ca-logo-img">
        @else
            <a href="{{ route('settings.branding') }}" class="ca-logo-placeholder">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-right:5px"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                Your Logo
            </a>
        @endif
        <span class="ca-partner-tag">{{ $appName ?? config('app.name') }}</span>
    </div>

    <nav class="ca-nav">
        <a href="{{ route('dashboard') }}" class="ca-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            Dashboard
        </a>
        <a href="{{ route('clients.index') }}" class="ca-nav-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Clients
        </a>
        <a href="{{ route('billing.index') }}" class="ca-nav-item {{ request()->routeIs('billing.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
            Subscription &amp; Usage
        </a>
        <a href="{{ route('settings.index') }}" class="ca-nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
            Settings
        </a>
        <a href="{{ route('quickbooks.index') }}" class="ca-nav-item {{ request()->routeIs('quickbooks.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12.55a11 11 0 0114.08 0"/><path d="M1.42 9a16 16 0 0121.16 0"/><path d="M8.53 16.11a6 6 0 016.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
            QuickBooks
        </a>
        <a href="{{ route('users.index') }}" class="ca-nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Users
        </a>
        <a href="{{ route('finance.index') }}" class="ca-nav-item {{ request()->routeIs('finance.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            Finance
        </a>
        <a href="{{ route('support.index') }}" class="ca-nav-item {{ request()->routeIs('support.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Support
        </a>
        <a href="{{ route('account.index') }}" class="ca-nav-item {{ request()->routeIs('account.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Account
        </a>
        <a href="{{ route('activity.index') }}" class="ca-nav-item {{ request()->routeIs('activity.*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Activity Log
        </a>
    </nav>

    <div class="ca-sidebar-foot">
        <div class="ca-sfoot-av">{{ strtoupper(substr($authUser->name ?? 'U', 0, 2)) }}</div>
        <div style="flex:1;min-width:0">
            <div class="ca-sfoot-name">{{ $authUser->name }}</div>
            <div class="ca-sfoot-role">{{ $authUser->getRoleNames()->first() ?? 'Admin' }}</div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
            @csrf
            <button type="submit" class="ca-sfoot-logout" title="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
        </form>
    </div>
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
                    <div class="ca-tb-urole">{{ $authUser->getRoleNames()->first() ?? 'Admin' }}</div>
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
        {{-- Flash messages --}}
        @foreach(['success','error','info','warning'] as $t)
            @if(session($t))
            <div class="ca-alert ca-alert-{{ $t }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    @if($t === 'success')<polyline points="20 6 9 17 4 12"/>
                    @elseif($t === 'error')<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    @else<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    @endif
                </svg>
                {{ session($t) }}
            </div>
            @endif
        @endforeach

        @yield('content')
    </div>
</div>

</div>{{-- flex wrapper --}}

{{-- Toast --}}
<div id="ca-toast" class="toast toast-success"></div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function showToast(msg, type='success') {
    const t = document.getElementById('ca-toast');
    t.textContent = msg;
    t.className = 'toast toast-' + type + ' show';
    setTimeout(() => t.classList.remove('show'), 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.ca-alert').forEach(el => {
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 4500);

    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const bar = btn.closest('.tab-bar');
            const container = bar?.parentElement;
            bar?.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            container?.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
            container?.querySelector('#' + btn.dataset.tab)?.classList.add('active');
        });
    });
});

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

async function apiCall(url, method='POST', body={}) {
    const r = await fetch(url, {
        method,
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        body: method !== 'GET' ? JSON.stringify(body) : undefined
    });
    return r.json();
}
</script>
@stack('scripts')
</body>
</html>
