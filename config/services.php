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
        'create_english_subpage' => env('WIKI_TRANS_CREATE_ENGLISH_SUBPAGE'),

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

    'wiki_upload_image' => [
        'consumer_token' => env('WIKI_UPLOAD_IMAGE_CONSUMER_TOKEN'),
        'consumer_secret' => env('WIKI_UPLOAD_IMAGE_CONSUMER_SECRET'),

        'access_token' => env('WIKI_UPLOAD_IMAGE_ACCESS_TOKEN'),
        'access_secret' => env('WIKI_UPLOAD_IMAGE_ACCESS_SECRET'),
    ],

    'deepl' => [
        'target_locale' => env('DEEPL_TARGET_LOCALE', 'DE'),
        'auth_key' => env('DEEPL_AUTH_KEY', ''),
    ],

    'rsi_account' => [
        'username' => env('RSI_USERNAME'),
        'password' => env('RSI_PASSWORD'),
    ],

    'item_thumbnail_url' => env('ITEM_THUMBNAIL_URL'),

    'plausible' => [
        'enabled' => env('PLAUSIBLE_ENABLED', false),
        'domain' => env('PLAUSIBLE_DOMAIN'),
    ],

    'sc_tools' => [
        'url' => env('SC_TOOLS_URL', 'https://starcitizen.tools'),
        'bot_name' => env('SC_TOOLS_BOT_NAME'),
        'bot_password' => env('SC_TOOLS_BOT_PASSWORD'),
    ],
];
