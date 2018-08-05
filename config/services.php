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
        'redirect' => 'oob',
        'url' => env('WIKI_URL'),
    ],
];
