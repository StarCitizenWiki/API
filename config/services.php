<?php

declare(strict_types=1);

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
        'target_locale' => env('DEEPL_TARGET_LOCALE', 'de'),
        'translation_locale' => env(
            'DEEPL_TRANSLATION_LOCALE',
            strtolower(substr((string) env('DEEPL_TARGET_LOCALE', 'de'), 0, 2)) ?: 'de',
        ),
    ],

    'comm_links' => [
        'auto_translate_after_import' => (bool) env('COMM_LINKS_AUTO_TRANSLATE_AFTER_IMPORT', false),
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
    | 'tracking_script' is the full path to the Plausible tracking script (e.g., 'https://example.com/js/plausible.js')
    |
    */
    'plausible' => [
        'enabled' => env('PLAUSIBLE_ENABLED', false),
        'domain' => env('PLAUSIBLE_DOMAIN'),
        'tracking_script' => env('PLAUSIBLE_TRACKING_SCRIPT'),
    ],
];
