<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Root Domain
    |--------------------------------------------------------------------------
    | Tenants get subdomains under this domain.
    | e.g. ROOT_DOMAIN=yourdomain.com → acme.yourdomain.com
    */
    'root_domain' => env('ROOT_DOMAIN', 'yourdomain.com'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Resolution
    |--------------------------------------------------------------------------
    | How to identify tenants from the incoming request.
    | 'subdomain' and 'custom_domain' are both tried in order.
    */
    'identification' => ['subdomain', 'custom_domain'],

    /*
    |--------------------------------------------------------------------------
    | SuperAdmin Domain
    |--------------------------------------------------------------------------
    | Requests to this host bypass tenant resolution entirely.
    | Set to your main app domain (where the SuperAdmin panel lives).
    */
    'superadmin_domain' => env('SUPERADMIN_DOMAIN', 'app.yourdomain.com'),
];
