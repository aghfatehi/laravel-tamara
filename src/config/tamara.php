<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tamara Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Tamara is a Buy Now Pay Later (BNPL) solution available in the
    | Middle East. This configuration manages API credentials,
    | environment settings, and default payment parameters.
    |
    */

    'sandbox' => env('TAMARA_SANDBOX_MODE', true),

    'api_token' => env('TAMARA_API_TOKEN', ''),

    'country_code' => env('TAMARA_COUNTRY_CODE', 'SA'),

    'currency' => env('TAMARA_CURRENCY', 'SAR'),

    'instalments' => env('TAMARA_INSTALMENTS', 3),

    'routes' => [
        'prefix' => env('TAMARA_ROUTE_PREFIX', 'tamara'),
        'middleware' => ['web'],
    ],

    'merchant_urls' => [
        'success' => env('TAMARA_SUCCESS_URL', '/tamara/callback'),
        'failure' => env('TAMARA_FAILURE_URL', '/tamara/failure'),
        'cancel' => env('TAMARA_CANCEL_URL', '/tamara/cancel'),
        'notification' => env('TAMARA_NOTIFICATION_URL', '/tamara/webhook'),
    ],

    'api_urls' => [
        'sandbox' => 'https://api-sandbox.tamara.co',
        'production' => 'https://api.tamara.co',
    ],

    'payment_type' => env('TAMARA_PAYMENT_TYPE', 'PAY_BY_INSTALMENTS'),

    'locale' => env('TAMARA_LOCALE', 'en_US'),
    
    'logging' => env('TAMARA_LOGGING', true),
];
