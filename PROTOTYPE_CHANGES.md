# Prototype Implementation Changes

This document lists every change being made to align the project with the HTML prototypes:
- `cfoinsights-superadmin-v2.html` — CFO Insights Admin Console (SuperAdmin panel)
- `cfoinsights-partner-portal-v38.html` — CFO Insights White Label Solution Admin Portal (Client Admin panel)

---

## 1. SUPERADMIN PANEL

### 1.1 Layout — `resources/views/layouts/superadmin/app.blade.php`
**Change:** Complete redesign to match prototype design system
- Font: Inter (unchanged, keep Google Fonts link)
- CSS variables: `--blue:#0C84BC`, `--orange:#F06B21`, `--bg:#f1f5f9`, `--sidebar-w:252px`, `--topbar-h:60px`
- Sidebar: `background:#0a2540`, fixed, 252px wide
- Sidebar logo area: logo image + "SUPERADMIN" badge text
- Nav items: SVG icons (feather-style), `color:rgba(255,255,255,.55)`, `border-radius:8px`
- Nav active state: `background:rgba(240,107,33,.22)`, `color:#F06B21`
- Nav sections with separators: Management, Billing, Content, System
- Topbar: 60px, white, breadcrumb + notifications bell + user pill + orange round logout button
- Main: margin-left 252px, margin-top 60px, `background:#f1f5f9`

### 1.2 SuperAdmin Dashboard — `resources/views/super-admin/dashboard/index.blade.php`
**Change:** Complete page redesign
- Page header with title + subtitle
- KPI grid (3 columns): Total Firms, Active Firms, MRR, Suspended Firms, Trial Firms, Pending Invoices
- Bar chart section (Revenue Overview — placeholder canvas)
- Recent Firms table with health signal badges
- Recent Activity sidebar panel

### 1.3 Firms (Companies) List — `resources/views/super-admin/companies/index.blade.php`
**Change:** Redesign to match prototype
- Page header with "+ Add Firm" orange button
- Toolbar: search input, status filter dropdown, plan filter dropdown
- Table: 11 columns — Firm Name, Plan, MRR, Users, QuickBooks, Health, Joined, Status, Actions (dot menu)
- Health signal badges: green/amber/red dots
- Dot menu: View, Login As, Edit, Suspend, Delete
- Status badges: Active (green), Suspended (red), Trial (amber), Inactive (gray)

### 1.4 View Firm Detail — `resources/views/super-admin/companies/show.blade.php`
**Change:** New detailed firm view
- Gradient header card: firm avatar + name + plan + status chips (MRR, Users, QB status, Health score)
- Tabs: Overview, Users, Billing, Activity
- Overview tab: notes, timeline, KPIs
- Users sub-table, Billing history table

### 1.5 Plan Management — `resources/views/super-admin/packages/index.blade.php`
**Change:** Redesign with plan cards
- Page header with "+ New Plan" button
- Plan cards (3-col grid): name, price, features list, user/company limits, status toggle, edit/delete buttons
- Stripe Price ID indicator (green check or dash)

### 1.6 Finance — NEW page `resources/views/super-admin/finance/index.blade.php`
**Add:** New Finance page
- 4 KPI cards: Total MRR, ARR, New MRR, Churn MRR
- Revenue table: firm-by-firm breakdown
- Invoices table with status badges
- Route: `GET /superadmin/finance` → `SuperAdmin\FinanceController@index`

### 1.7 Users — `resources/views/super-admin/users/index.blade.php`
**Change:** Redesign table
- Search, role filter, status filter
- Table: Name, Email, Role, Company, Status, Last Login, Actions
- Toggle status AJAX button

### 1.8 Settings — `resources/views/super-admin/settings/index.blade.php`
**Change:** 3-tab layout
- General tab: app name, timezone, default currency
- Email tab: SMTP settings
- Integrations tab: Stripe keys, webhook URL display

### 1.9 Support — NEW page `resources/views/super-admin/support/index.blade.php`
**Add:** Support tickets page
- Info banner
- Open tickets table with SLA tracking, priority badges, status badges
- Route: `GET /superadmin/support` → `SuperAdmin\SupportController@index`

### 1.10 My Account — NEW page `resources/views/super-admin/account/index.blade.php`
**Add:** SuperAdmin account page
- Profile tab: avatar upload, name, email, password change
- Global Settings tab: app name, logo upload, live preview card
- Route: `GET /superadmin/my-account` → `SuperAdmin\AccountController@index`

### 1.11 Activity Log — NEW page `resources/views/super-admin/activity/index.blade.php`
**Add:** Activity log page
- Filters: date range, event type, firm
- Timeline/table of activity events
- Route: `GET /superadmin/activity` → `SuperAdmin\ActivityController@index`

### 1.12 Login Page — `resources/views/auth/login.blade.php`
**Change:** SuperAdmin login style (used when no tenant branding)
- White card on gradient background (#0a2540 → #0C84BC)
- SUPERADMIN badge
- Email/password fields with Inter font
- Orange sign-in button

---

## 2. CLIENT ADMIN PANEL

### 2.1 Layout — `resources/views/layouts/app.blade.php`
**Change:** Complete redesign to match prototype design system
- Font: DM Sans (add Google Fonts link)
- CSS variables: `--blue:#1a6aa8`, `--orange:#f06b21`, `--bg:#f0f4f8`, `--sidebar-w:242px`, `--topbar-h:58px`
- Sidebar: WHITE background, `border-right:1px solid #e2e8f0`, `box-shadow:2px 0 8px rgba(0,0,0,.04)`
- Logo zone: company logo or placeholder + partner tag text
- Nav items: SVG icons, `color:#6b7280`, left-border active indicator
- Nav active: `background:#fef3ec`, `color:#f06b21`, `border-left:3px solid #f06b21`
- Nav items: Dashboard, Clients, Subscription & Usage, Settings, QuickBooks, Users, Finance, Support, My Account, Activity Log
- Sidebar footer: user avatar (orange square) + name/role + logout icon
- Topbar: 58px, sticky, white, breadcrumb + notifications + user pill + orange logout button

### 2.2 Dashboard — `resources/views/dashboard/index.blade.php`
**Change:** Complete redesign
- Time filter pills: Today, 7D, 30D, 90D, Custom
- KPI row 1 (4 cards): Total Clients, Active Companies, MRR, QB Connected
- KPI row 2 (4 cards): Pending Setup, Insights Generated, Avg Health Score, Support Tickets
- Charts row (2-col): Revenue chart + Activity feed
- Each KPI card: colored left border, label, value, trend indicator

### 2.3 Client Management — `resources/views/clients/index.blade.php` (NEW)
**Add:** Client management page with two tabs
- Tabs: Clients | Companies
- Clients tab: table with expandable rows (click to expand companies under that client)
  - Columns: Client Name, Email, Companies, QB, Plan, MRR, Status, Actions
  - Expandable nested company rows
  - Add Client modal
- Companies tab: companies table
  - Columns: Company Name, Client, Plan, Users, QB, Health, Status, Actions
  - Add Company button

### 2.4 Subscription & Usage — `resources/views/billing/index.blade.php`
**Change:** Redesign current billing page
- Current plan banner (blue left-border card): plan name, billing cycle, next billing date
- Plan upgrade cards (3 columns)
- Usage table: feature, limit, used, % bar

### 2.5 Settings — merge existing settings pages into tabs
**Change:** Single settings page with 4 tabs
- Tab 1 — Payment: Stripe keys (existing payment-gateway.blade.php content)
- Tab 2 — Email: SMTP settings (existing email-smtp.blade.php content)
- Tab 3 — SMS: SMS settings (existing sms.blade.php content)
- Tab 4 — Plan Management: client packages CRUD (existing plans/index content)
- New route: `GET /settings` → unified settings page

### 2.6 QuickBooks — `resources/views/quickbooks/index.blade.php`
**Change:** Redesign
- Connection status card
- Connected companies list
- Sync log table

### 2.7 Users — NEW `resources/views/users/index.blade.php`
**Add:** Users management page
- Invite User modal
- Table: Name, Email, Role, Company, Status, Last Login, Actions

### 2.8 Finance — NEW `resources/views/finance/index.blade.php`
**Add:** Finance page
- KPI cards: MRR, Collected, Outstanding, Churn
- Revenue by company table
- Invoice history table

### 2.9 Support — NEW `resources/views/support/index.blade.php`
**Add:** Support ticket submission page
- Info banner
- New Ticket modal
- My tickets table

### 2.10 My Account — `resources/views/profile/index.blade.php`
**Change:** Redesign with two tabs
- Profile tab: avatar, name, email, password change form
- Branding tab: logo upload, color pickers (primary/accent), live preview card, custom domain

### 2.11 Activity Log — NEW `resources/views/activity/index.blade.php`
**Add:** Activity log page
- Date filter, event type filter
- Timeline list of events with colored dots

### 2.12 Client Detail — NEW `resources/views/clients/show.blade.php`
**Add:** Individual client detail page
- `cd-header-card`: avatar ring, name, email, stat chips (Companies, QB Status, MRR, Health)
- Meta row: joined date, plan, timezone
- 2-column grid: Notes/timeline + mini KPIs
- Tabs: Overview, Companies, Insights, Activity

### 2.13 Client Insights — NEW `resources/views/clients/insights.blade.php`
**Add:** Insights list page
- Search bar + "Generate Insight" button
- Insights table: Title, Client, Date, Status, Actions (download/delete)

### 2.14 Insight Detail — NEW `resources/views/clients/insight-detail.blade.php`
**Add:** Individual insight view
- Back button
- Pagination nav (prev/next)
- Section cards: each insight section with icon, title, bullets
- Progress dots at bottom

---

## 3. NEW ROUTES NEEDED

### SuperAdmin:
- `GET /superadmin/finance` → `SuperAdmin\FinanceController@index` (name: `superadmin.finance`)
- `GET /superadmin/support` → `SuperAdmin\SupportController@index` (name: `superadmin.support`)
- `GET /superadmin/my-account` → `SuperAdmin\AccountController@index` (name: `superadmin.account`)
- `POST /superadmin/my-account` → `SuperAdmin\AccountController@update` (name: `superadmin.account.update`)
- `GET /superadmin/activity` → `SuperAdmin\ActivityController@index` (name: `superadmin.activity`)

### Client Admin:
- `GET /clients` → `ClientController@index` (name: `clients.index`)
- `GET /clients/{id}` → `ClientController@show` (name: `clients.show`)
- `GET /finance` → `FinanceController@index` (name: `finance.index`)
- `GET /support` → `SupportController@index` (name: `support.index`)
- `GET /users` → `UserController@index` (name: `users.index`)
- `GET /activity` → `ActivityController@index` (name: `activity.index`)
- `GET /settings` → unified `SettingsController@index` (name: `settings.index`)

---

## 4. NEW CONTROLLERS NEEDED

### SuperAdmin:
- `app/Http/Controllers/SuperAdmin/FinanceController.php`
- `app/Http/Controllers/SuperAdmin/SupportController.php`
- `app/Http/Controllers/SuperAdmin/AccountController.php`
- `app/Http/Controllers/SuperAdmin/ActivityController.php`

### Client Admin:
- `app/Http/Controllers/ClientController.php`
- `app/Http/Controllers/FinanceController.php`
- `app/Http/Controllers/SupportController.php`
- `app/Http/Controllers/UserController.php` (tenant-scoped)
- `app/Http/Controllers/ActivityController.php`
- `app/Http/Controllers/SettingsController.php` (unified)

---

## 5. CSS / ASSET CHANGES

- `public/css/superadmin.css` — SuperAdmin design tokens + component styles
- `public/css/client.css` — Client Admin design tokens + component styles
- Google Fonts: Add DM Sans to client admin layout
- Remove dependency on Tailwind classes from layouts (use custom CSS instead)

---

## 6. IMPLEMENTATION ORDER

1. SuperAdmin layout (`layouts/superadmin/app.blade.php`) ✅
2. Client Admin layout (`layouts/app.blade.php`) ✅
3. Login page redesign
4. SuperAdmin dashboard
5. SuperAdmin companies list + show
6. SuperAdmin packages/plans
7. SuperAdmin finance, support, account, activity (stub pages)
8. Client Admin dashboard
9. Client Management (clients + companies tabs)
10. Subscription & Usage page
11. Client Admin settings (tabbed)
12. Users, Finance, Support, QuickBooks pages
13. My Account (profile + branding tabs)
14. Activity Log
15. Client Detail, Insights, Insight Detail pages
