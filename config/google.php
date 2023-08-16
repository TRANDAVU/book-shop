<?php

return [

    // OAuth
    'client_id'        => env('GOOGLE_CLIENT_ID', ''),
    'client_secret'    => env('GOOGLE_CLIENT_SECRET', ''),
    'redirect_uri'     => env('GOOGLE_REDIRECT', ''),
    'scopes'           => [\Google\Service\Sheets::DRIVE, \Google\Service\Sheets::SPREADSHEETS],
    'access_type'      => 'online',
    'approval_prompt'  => 'auto',
    'prompt'           => 'consent', //"none", "consent", "select_account" default:none

    // or Service Account
    'file'    => storage_path('credentials.json'),
    'enable'  => env('GOOGLE_SERVICE_ENABLED', true),
];
