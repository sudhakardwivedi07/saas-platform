<?php

return [
    'client_id'     => env('QUICKBOOKS_CLIENT_ID'),
    'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
    'redirect_uri'  => env('QUICKBOOKS_REDIRECT_URI', env('APP_URL') . '/quickbooks/callback'),
    'environment'       => env('QUICKBOOKS_ENVIRONMENT', 'Development'), // 'Development' | 'Production'
    'api_production_url' => env('QB_API_PRODUCTION_URL'),
    'api_sandbox_url'    => env('QB_API_SANDBOX_URL'),
];
