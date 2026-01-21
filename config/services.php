<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'rsi_url' => env('RSI_URL', 'https://robertsspaceindustries.com'),

    'deepl' => [
        'auth_key' => env('DEEPL_AUTH_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Plausible Analytics
    |--------------------------------------------------------------------------
    |
    | Plausible Analytics configuration for tracking website analytics.
    |
    | 'enabled' determines whether Plausible analytics tracking is enabled.
    | 'domain' is the main domain to track (e.g., 'api.star-citizen.wiki').
    | 'tracking_domain' is the custom Plausible instance domain if self-hosted.
    |
    */
    'plausible' => [
        'enabled' => env('PLAUSIBLE_ENABLED', false),
        'domain' => env('PLAUSIBLE_DOMAIN'),
        'tracking_domain' => env('PLAUSIBLE_TRACKING_DOMAIN'),
    ],
];
