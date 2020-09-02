<?php declare(strict_types = 1);

return [
    'api_url' => env('WIKI_API_URL'),

    'token_driver' => 'session',

    'driver' => [
        // Session Keys
        'session' => [
            'token' => 'oauth.user_token',
            'secret' => 'oauth.user_secret',
        ],
        // Database Fields
        'database' => [
            'token' => 'oauth_token',
            'secret' => 'oauth_secret',
        ],
    ],

    'request' => [
        'timeout' => 10.0,
    ],
];
