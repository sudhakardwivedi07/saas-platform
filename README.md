# White Label SaaS Platform — Laravel 11

A production-ready, multi-tenant white-label SaaS application built with Laravel 11. Each client gets their own branded subdomain, custom logo/colours, isolated data, Stripe subscriptions, QuickBooks integration, document management, and email/SMS systems.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Tech Stack](#tech-stack)
3. [Project Structure](#project-structure)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Multi-Tenancy](#multi-tenancy)
7. [Authentication & Roles](#authentication--roles)
8. [Payment System (Stripe)](#payment-system-stripe)
9. [Email System](#email-system)
10. [SMS Integration](#sms-integration)
11. [Document Management](#document-management)
12. [QuickBooks Integration](#quickbooks-integration)
13. [White-Label Branding](#white-label-branding)
14. [Queue System](#queue-system)
15. [Scheduled Commands](#scheduled-commands)
16. [API Reference](#api-reference)
17. [SuperAdmin Panel](#superadmin-panel)
18. [Development Workflow](#development-workflow)
19. [Deployment](#deployment)
20. [Credentials Reference](#credentials-reference)

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────┐
│                    INCOMING REQUEST                       │
└──────────────────────┬──────────────────────────────────┘
                       │
           IdentifyTenant Middleware
         (resolves subdomain/custom domain)
                       │
           ┌───────────┴───────────┐
           │                       │
    SuperAdmin Panel          Tenant Panel
    /superadmin/*             /{any}
    (no company_id)           (company_id scoped)
           │                       │
    SuperAdmin MW           TenantScope (global)
    is_superadmin=true       auto-filters all queries
           │                       │
    Dashboard, Companies    Dashboard, Documents,
    Packages, Payments      QB, Email Templates,
    Email Templates         Billing, Settings
```

**Core Design Decisions:**

- **Single-database multi-tenancy** — every table with tenant data has `company_id`. A global `TenantScope` applied via `HasTenant` trait automatically scopes all Eloquent queries. SuperAdmin bypasses it with `withoutGlobalScopes()`.
- **Service Layer** — no business logic in controllers. Every domain has a dedicated Service class.
- **Queue-first** — all emails, SMS, QB syncs, and webhook side effects are dispatched as queue jobs. HTTP responses never wait on third parties.
- **White-label** — branding is stored per-tenant in `company_brandings`. CSS variables are injected at layout render time. Tenants can override logo, colours, fonts, login background, and custom CSS.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 11 |
| Auth | Laravel built-in + Spatie Permission |
| Payments | Stripe (Checkout Sessions + Webhooks) |
| Email | SMTP / Mailgun / SendGrid (runtime-configurable per tenant) |
| SMS | Twilio |
| Accounting | QuickBooks Online SDK (OAuth2) |
| Documents | Local disk / AWS S3 (Flysystem) |
| Queues | Redis + Laravel Horizon |
| Cache | Redis |
| Database | MySQL 8+ |
| Frontend | Blade + vanilla JS (no Node build step required) |
| CSS | Custom utility CSS (no Tailwind compiler needed) |

---

## Project Structure

```
saas-platform/
├── app/
│   ├── Console/Commands/
│   │   ├── RefreshQuickBooksTokens.php   # Runs every 15 min — refreshes QB tokens
│   │   └── SendTrialEndingReminders.php  # Runs daily — emails 3-day and 1-day warnings
│   ├── Helper/
│   │   ├── helpers.php                   # Global functions: company(), user(), abort_403()
│   │   └── Reply.php                     # AJAX response helper (status/message/data)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/AuthController.php   # Login, register, 2FA, password reset
│   │   │   ├── Api/ApiControllers.php    # Sanctum API: auth + documents
│   │   │   ├── SuperAdmin/               # Dashboard, Companies, Packages, Payments
│   │   │   ├── TenantControllers.php     # Dashboard, Documents, QB, Email Templates
│   │   │   └── SettingsControllers.php   # Branding, SMTP, SMS, Profile
│   │   └── Middleware/
│   │       ├── IdentifyTenant.php        # Resolves company from subdomain/custom domain
│   │       └── SuperAdmin.php            # Guards superadmin routes
│   ├── Jobs/
│   │   ├── SendEmailJob.php              # Queued — dispatched for every outbound email
│   │   ├── SendSmsJob.php                # Queued — dispatched for every SMS
│   │   └── SyncQuickBooksJob.php         # Queued — QB customer/invoice sync
│   ├── Models/
│   │   ├── Company.php                   # Tenant anchor model
│   │   ├── CompanyBranding.php           # Per-tenant white-label settings
│   │   ├── User.php                      # NULL company_id = SuperAdmin
│   │   ├── Document.php + DocumentVersion.php
│   │   ├── EmailTemplate.php             # Tenant-specific or global fallback
│   │   ├── QuickBooksConnection.php      # Multiple QB accounts per tenant
│   │   └── SuperAdmin/                   # Package, Subscription, Credentials
│   ├── Scopes/TenantScope.php            # Auto-applied company_id filter
│   ├── Services/
│   │   ├── EmailService.php              # Template resolution, SMTP swap, HTML wrapping
│   │   ├── SmsService.php                # Twilio with per-tenant settings
│   │   ├── StripeService.php             # Checkout, webhook handlers
│   │   ├── DocumentService.php           # Upload, versioning, signed URLs
│   │   └── QuickBooksService.php         # OAuth2, token refresh, sync
│   └── Traits/HasTenant.php              # Applies TenantScope + auto-fills company_id
├── database/
│   ├── migrations/                        # 3 files covering all 20+ tables
│   └── seeders/AllSeeders.php            # SuperAdmin, packages, email templates
├── resources/views/
│   ├── auth/                             # Login, register, 2FA, password reset
│   ├── layouts/                          # Tenant layout + SuperAdmin layout
│   ├── dashboard/                        # Tenant dashboard
│   ├── documents/                        # Document manager with upload modal
│   ├── quickbooks/                       # QB connections + sync log
│   ├── email-templates/                  # Template list + WYSIWYG editor
│   ├── billing/                          # Subscription management + payment history
│   ├── settings/                         # Branding, SMTP, SMS
│   ├── profile/                          # User profile + password
│   └── super-admin/                      # All SuperAdmin panel views
├── routes/
│   ├── web.php                           # Tenant + auth routes
│   ├── SuperAdmin/web.php                # SuperAdmin routes + Stripe webhook
│   ├── api.php                           # Sanctum API routes
│   └── console.php                       # Scheduler definitions
└── public/
    ├── css/app.css                       # Complete utility CSS (no build step)
    └── js/app.js                         # Global JS: AJAX, toasts, modals
```

---

## Installation

### Prerequisites

- PHP 8.2+
- MySQL 8.0+
- Redis 6+
- Composer 2+

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/yourorg/saas-platform.git
cd saas-platform

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure your .env (see Configuration section below)

# 6. Run migrations and seed
php artisan migrate --seed

# 7. Create storage symlink
php artisan storage:link

# 8. Start the queue worker
php artisan horizon

# 9. Start the scheduler (in production: add to crontab)
php artisan schedule:work

# 10. Serve
php artisan serve
```

### Crontab entry (production)
```
* * * * * cd /var/www/saas-platform && php artisan schedule:run >> /dev/null 2>&1
```

---

## Configuration

### Required `.env` values

```env
# App
APP_URL=https://app.yourdomain.com
ROOT_DOMAIN=yourdomain.com          # Tenants get subdomain.yourdomain.com

# Database
DB_HOST=127.0.0.1
DB_DATABASE=saas_platform
DB_USERNAME=root
DB_PASSWORD=secret

# Redis (required for queues and cache)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# QuickBooks
QUICKBOOKS_CLIENT_ID=...
QUICKBOOKS_CLIENT_SECRET=...
QUICKBOOKS_REDIRECT_URI=https://app.yourdomain.com/quickbooks/callback
QUICKBOOKS_ENVIRONMENT=Development   # Change to Production when live

# Twilio (optional — SMS features)
TWILIO_SID=ACxxxx
TWILIO_TOKEN=xxxx
TWILIO_FROM=+15551234567

# AWS S3 (optional — use for document storage in production)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_BUCKET=
AWS_DEFAULT_REGION=us-east-1
```

### Stripe Webhook Setup

1. Install Stripe CLI: `stripe listen --forward-to localhost:8000/webhooks/stripe`
2. Or in Stripe Dashboard → Developers → Webhooks → Add endpoint:
   - URL: `https://yourdomain.com/webhooks/stripe`
   - Events: `checkout.session.completed`, `invoice.payment_succeeded`, `invoice.payment_failed`, `customer.subscription.deleted`
3. Copy the signing secret into `STRIPE_WEBHOOK_SECRET`
4. Update SuperAdmin → Payment Gateway Settings with your Stripe keys

---

## Multi-Tenancy

### How it works

Every request goes through `IdentifyTenant` middleware, which resolves the tenant from:
1. **Custom domain** — exact match on `companies.custom_domain`
2. **Subdomain** — `{subdomain}.ROOT_DOMAIN` matches `companies.subdomain`

Once resolved, the `Company` model is bound to the container (`app('currentCompany')`), accessible via the `company()` helper.

All Eloquent models using `HasTenant` automatically:
- Filter queries by `company_id` via `TenantScope`
- Auto-fill `company_id` on `create()`

### SuperAdmin bypasses scoping

```php
// SuperAdmin queries — no company filter
Company::withoutGlobalScopes()->get();

// Specific scope bypass
Document::withoutGlobalScope(TenantScope::class)->get();
```

### Adding a new tenant-scoped model

```php
class MyModel extends BaseModel
{
    use HasTenant;  // That's it — all queries automatically scoped

    protected $fillable = ['company_id', 'name', ...];
}
```

---

## Authentication & Roles

### Default roles (seeded)

| Role | Permissions |
|---|---|
| `client_admin` | All permissions |
| `manager` | View/create users, documents, QB, email templates, settings, reports |
| `user` | View and download documents, view QB, view reports |

### Permission check in controllers

```php
// Abort if user lacks permission (mirrors reference project pattern)
abort_403(!auth()->user()->can('upload_documents'));

// Or use Blade @can directive
@can('edit_branding')
    <a href="...">Edit Branding</a>
@endcan
```

### Adding permissions

In `PermissionSeeder` or via tinker:
```php
Permission::create(['name' => 'my_new_permission', 'guard_name' => 'web']);
$role = Role::findByName('client_admin');
$role->givePermissionTo('my_new_permission');
```

### Two-Factor Authentication

Users with `two_factor_code` set will be redirected to `/2fa` after login. The 6-digit code is:
- Generated by `$user->generateTwoFactorCode()`
- Valid for 10 minutes
- Sent via email using the `two_factor_code` template

---

## Payment System (Stripe)

### Flow

```
User selects plan → POST /billing/checkout
→ StripeService::createCheckoutSession()
→ Redirect to Stripe Hosted Checkout
→ User pays
→ Stripe sends webhook to /webhooks/stripe
→ StripeWebhookController::handle()
→ Signature verified → event matched → handler called
→ Subscription/Payment records updated
→ Welcome email dispatched to queue
```

### Webhook events handled

| Event | Handler | Effect |
|---|---|---|
| `checkout.session.completed` | `handleCheckoutCompleted()` | Creates Subscription, updates company package, dispatches payment_success email |
| `invoice.payment_succeeded` | `handleInvoicePaid()` | Updates Subscription period dates, creates Payment record |
| `invoice.payment_failed` | `handlePaymentFailed()` | Sets Subscription to `past_due`, creates failed Payment, dispatches payment_failed email |
| `customer.subscription.deleted` | `handleSubscriptionDeleted()` | Sets Subscription to `canceled`, dispatches cancellation email |

### Adding a new package to Stripe

1. Create the price in Stripe Dashboard
2. Copy Price ID (e.g. `price_1Abc...`)
3. SuperAdmin → Packages → Edit → paste into "Stripe Monthly/Annual Price ID"

---

## Email System

### Sending an email

```php
// From a controller or listener — always dispatch to queue:
SendEmailJob::dispatch(
    toEmail:      $user->email,
    templateSlug: 'user_welcome',
    placeholders: [
        '{{name}}'      => $user->name,
        '{{app_name}}' => $company->app_name,
        '{{login_url}}' => tenant_url($company->subdomain, 'login'),
    ],
    companyId: $company->id,
)->onQueue('emails');

// Or use the convenience wrapper on User:
app(EmailService::class)->sendToUser($user, 'payment_success', [
    '{{amount}}'    => '$79.00',
    '{{plan_name}}' => 'Professional',
]);
```

### Template resolution

`EmailTemplate::resolveFor($slug, $companyId)` returns:
1. Tenant-specific template if one exists for that `(company_id, slug)` pair
2. Global template (`company_id = NULL`) as fallback

This means tenants can customize any template without affecting others, and always have a working default.

### Per-tenant SMTP

Tenants configure their own SMTP via Settings → Email SMTP. The `EmailService` calls `configureMailer()` before each send, switching Laravel's runtime mail config to the tenant's settings. If no tenant config exists, the platform default is used.

### Available template slugs

| Slug | Trigger |
|---|---|
| `user_welcome` | New user registration |
| `password_reset` | Password reset request |
| `payment_success` | Successful Stripe payment |
| `payment_failed` | Failed Stripe payment |
| `subscription_canceled` | Subscription deleted |
| `two_factor_code` | 2FA login code |
| `document_shared` | Document sharing |
| `trial_ending` | Trial ending in 3 or 1 day |

---

## SMS Integration

### Sending an SMS

```php
// Always queue — never send inline:
SendSmsJob::dispatch(
    toNumber:  '+15551234567',
    message:   'Your verification code is 123456',
    companyId: $company->id,
)->onQueue('default');

// Or via service directly (in a job context):
app(SmsService::class)->send('+15551234567', 'Message text', $companyId);
```

### Per-tenant Twilio credentials

Tenants configure credentials at Settings → SMS Settings. The `SmsService` resolves tenant credentials first, falling back to the global `.env` credentials.

---

## Document Management

### Uploading

```php
$document = app(DocumentService::class)->upload(
    file:      $request->file('document'),
    companyId: company()->id,
    meta: [
        'name'              => 'Q3 Financial Report',
        'documentable_type' => \App\Models\Company::class,
        'documentable_id'   => company()->id,
    ]
);
```

### Downloading (signed URL)

```php
$url = app(DocumentService::class)->getTemporaryUrl($document, minutes: 5);
return redirect($url);
```

S3 documents get a presigned URL expiring in 5 minutes. Local documents get a direct storage URL.

### Versioning

```php
$version = app(DocumentService::class)->uploadVersion(
    document: $document,
    file:     $request->file('new_version'),
    notes:    'Updated Q3 figures',
);
```

### Storage backend

- **Development**: `local` disk — stored in `storage/app/public`
- **Production**: Switch `FILESYSTEM_DISK=s3` in `.env`. All uploads automatically go to S3.

File paths follow: `companies/{company_id}/documents/uploads/{year}/{month}/{hash}.ext`

---

## QuickBooks Integration

### Connecting

```
User clicks "Connect QuickBooks"
→ GET /quickbooks/connect
→ QuickBooksService::getAuthorizationUrl()
→ Redirect to Intuit OAuth page
→ User authorizes
→ GET /quickbooks/callback?code=...&realmId=...
→ QuickBooksService::handleCallback()
→ Tokens stored in quickbooks_connections
→ Redirect back with success message
```

### Multiple QB accounts per tenant

The `quickbooks_connections` table has a unique constraint on `(company_id, realm_id)`, allowing one tenant to connect multiple QuickBooks company accounts (e.g. multiple business entities) while preventing duplicates.

### Token auto-refresh

The `RefreshQuickBooksTokens` command runs every 15 minutes and refreshes any access token expiring within 30 minutes. Access tokens last ~1 hour; refresh tokens last ~100 days. If a refresh token expires, the connection is marked inactive and the tenant must reconnect.

### Syncing data

```php
// Via HTTP (dispatches to queue):
POST /quickbooks/{id}/sync
{ "sync_type": "customers" }  // or "invoices"

// Programmatically:
SyncQuickBooksJob::dispatch($connectionId, 'customers')->onQueue('quickbooks');
```

---

## White-Label Branding

### What tenants can customize

| Setting | How |
|---|---|
| Logo (light + dark) | Image upload → stored on disk/S3 |
| Favicon | Image upload |
| Login background | Image upload |
| Primary / Secondary / Accent colour | Colour picker |
| Font family | Select from 8 Google Fonts options |
| Custom CSS | Free-form CSS injected per page |
| Login heading + subheading | Text fields |
| App display name | `companies.app_name` |

### How CSS variables are injected

In `layouts/app.blade.php`:
```html
<style>
  :root {
    --primary:   {{ $branding->primary_color   ?? '#2563eb' }};
    --secondary: {{ $branding->secondary_color ?? '#64748b' }};
    --accent:    {{ $branding->accent_color    ?? '#0ea5e9' }};
  }
</style>
@if($branding?->custom_css)
<style>{!! $branding->custom_css !!}</style>
@endif
```

All buttons, sidebar, links, and form focus states use `var(--primary)`, so changing the primary colour instantly rebrands the entire UI.

---

## Queue System

### Queue configuration

```env
QUEUE_CONNECTION=redis
```

### Queue names used

| Queue | Used for |
|---|---|
| `emails` | All outgoing emails (high priority) |
| `default` | SMS, general jobs |
| `quickbooks` | QB sync jobs (long-running, isolated) |

### Horizon configuration

```bash
php artisan horizon
```

Access Horizon dashboard at `/horizon` (SuperAdmin only in production — restrict via `HorizonServiceProvider`).

### Job retry behaviour

| Job | Tries | Backoff |
|---|---|---|
| `SendEmailJob` | 3 | 60s |
| `SendSmsJob` | 3 | 30s |
| `SyncQuickBooksJob` | 2 | none |

---

## Scheduled Commands

Defined in `routes/console.php`:

| Command | Schedule | Purpose |
|---|---|---|
| `quickbooks:refresh-tokens` | Every 15 minutes | Refreshes QB access tokens expiring within 30 min |
| `subscriptions:trial-reminders` | Daily at 09:00 UTC | Emails tenants 3 days and 1 day before trial ends |
| Log cleanup | Weekly | Deletes `event_logs` older than 90 days |

---

## API Reference

### Authentication

```http
POST /api/v1/login
Content-Type: application/json

{ "email": "admin@company.com", "password": "secret" }

→ { "token": "...", "user": { ... } }
```

All subsequent requests:
```http
Authorization: Bearer {token}
Accept: application/json
```

### Endpoints

| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/v1/login` | Get Sanctum token |
| POST | `/api/v1/logout` | Revoke token |
| GET | `/api/v1/me` | Current user + company |
| GET | `/api/v1/documents` | List tenant documents (paginated) |
| POST | `/api/v1/documents` | Upload document (multipart) |
| GET | `/api/v1/documents/{id}` | Get document metadata |
| GET | `/api/v1/documents/{id}/download` | Redirect to signed URL |
| DELETE | `/api/v1/documents/{id}` | Soft-delete document |

---

## SuperAdmin Panel

Access at `/superadmin/dashboard` — requires `is_superadmin = true`.

### Features

| Section | What you can do |
|---|---|
| Dashboard | View platform stats, revenue chart (Chart.js), recent companies and payments |
| Companies | List all tenants, search/filter, view details, edit package/status, impersonate |
| Packages | Create/edit subscription plans, link Stripe Price IDs |
| Payment Gateway | Configure Stripe test/live keys and webhook secret |

### Impersonation

SuperAdmin can log in as any tenant admin:
```
SuperAdmin Dashboard → Companies → [Company] → Login As Admin
```
A banner appears: "You are viewing as Acme Inc." with an exit link.

On exit, the SuperAdmin session is restored seamlessly.

---

## Development Workflow

### Creating a new feature module

1. Add migration for new tables
2. Create model with `use HasTenant` trait (if tenant-scoped)
3. Create Service class in `app/Services/`
4. Create Controller extending `AccountBaseController`
5. Register routes in `routes/web.php`
6. Create Blade views extending `layouts.app`
7. Add permissions to `PermissionSeeder` and wire to roles

### Key patterns

**Thin controller:**
```php
public function store(Request $request)
{
    $request->validate([...]);
    abort_403(!auth()->user()->can('my_permission'));

    $result = $this->myService->doThing($request->validated());

    return Reply::successWithData('Done!', ['id' => $result->id]);
}
```

**Queue side effects:**
```php
// Never call services inline for email/SMS/QB
// Always dispatch to queue:
SendEmailJob::dispatch($user->email, 'template_slug', $placeholders, $companyId)
    ->onQueue('emails');
```

**AJAX forms in Blade:**
```html
<form data-ajax-form method="POST" action="{{ route('...') }}">
    @csrf
    <!-- inputs -->
    <button type="submit">Save</button>
</form>
<!-- app.js auto-wires [data-ajax-form] with handleReply() -->
```

---

## Deployment

### Production checklist

```bash
# Optimise
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Run migrations
php artisan migrate --force

# Supervisor config for queue worker
[program:saas-horizon]
command=php /var/www/saas-platform/artisan horizon
autostart=true
autorestart=true
user=www-data

# Nginx vhost — wildcard subdomain
server {
    listen 80;
    server_name *.yourdomain.com yourdomain.com;
    root /var/www/saas-platform/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### SSL

Use Let's Encrypt with wildcard certificate:
```bash
certbot certonly --dns-cloudflare -d yourdomain.com -d *.yourdomain.com
```

---

## Credentials Reference

After running `php artisan migrate --seed`:

| Role | Email | Password | URL |
|---|---|---|---|
| SuperAdmin | `superadmin@platform.com` | `SuperAdmin@123` | `/superadmin/dashboard` |

**First steps after installation:**
1. Log in as SuperAdmin
2. Go to Payment Gateway → add your Stripe test keys
3. Go to Packages → verify packages exist, add Stripe Price IDs
4. Register a new company via `/register` to test the tenant flow
5. Log into the tenant at `{subdomain}.yourdomain.com`

---

## File Count Summary

| Layer | Files |
|---|---|
| Models | 18 |
| Controllers | 12 (grouped in 5 files) |
| Services | 5 |
| Jobs | 3 |
| Middleware | 2 |
| Migrations | 3 |
| Seeders | 7 (in 2 files) |
| Blade Views | 28 |
| Routes | 4 |
| Config | 2 (tenancy, quickbooks) |
| Console Commands | 2 |
| Public Assets | 2 (CSS + JS) |
| **Total** | **88 files** |
