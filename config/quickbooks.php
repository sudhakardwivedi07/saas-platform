<?php

return [
    'client_id'     => env('QUICKBOOKS_CLIENT_ID'),
    'client_secret' => env('QUICKBOOKS_CLIENT_SECRET'),
    'redirect_uri'  => env('QUICKBOOKS_REDIRECT_URI', env('APP_URL') . '/quickbooks/callback'),
    'environment'   => env('QUICKBOOKS_ENVIRONMENT', 'Development'), // 'Development' | 'Production'
    'base_url'      => env('QUICKBOOKS_ENVIRONMENT', 'Development') === 'Production'
        ? 'https://quickbooks.api.intuit.com'
        : 'https://sandbox-quickbooks.api.intuit.com',
];
