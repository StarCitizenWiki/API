<?php declare(strict_types = 1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'mediawiki' => [
        'client_id' => env('WIKI_OAUTH_ID'),
        'client_secret' => env('WIKI_OAUTH_SECRET'),
        'url' => env('WIKI_URL'),
    ],

    'wiki_translations' => [
        'locale' => env('WIKI_TRANS_LOCALE', 'de_DE'),

        'consumer_token' => env('WIKI_TRANS_OAUTH_CONSUMER_TOKEN'),
        'consumer_secret' => env('WIKI_TRANS_OAUTH_CONSUMER_SECRET'),

        'access_token' => env('WIKI_TRANS_OAUTH_ACCESS_TOKEN'),
        'access_secret' => env('WIKI_TRANS_OAUTH_ACCESS_SECRET'),
    ],

    'wiki_approve_revs' => [
        'consumer_token' => env('WIKI_APPROVE_REVS_OAUTH_CONSUMER_TOKEN'),
        'consumer_secret' => env('WIKI_APPROVE_REVS_CONSUMER_SECRET'),

        'access_token' => env('WIKI_APPROVE_REVS_ACCESS_TOKEN'),
        'access_secret' => env('WIKI_APPROVE_REVS_ACCESS_SECRET'),
    ],

    'deepl' => [
        'target_locale' => env('DEEPL_TARGET_LOCALE', 'DE'),
        'auth_key' => env('DEEPL_AUTH_KEY'),
    ],

    'rsi_account' => [
        'username' => env('RSI_USERNAME'),
        'password' => env('RSI_PASSWORD'),
    ],
];
