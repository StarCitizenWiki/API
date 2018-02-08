<?php

return [
    'version' => '1.0',

    'wiki_url' => env('WIKI_URL', 'http://localhost'),
    'rsi_url' => env('RSI_URL', 'https://robertsspaceindustries.com'),

    'admin_password' => env('ADMIN_PASSWORD', config('app.key')),

    'throttle' => [
        'period'         => env('THROTTLE_PERIOD', 1),
        'guest_requests' => env('THROTTLE_GUEST_REQUESTS', 10),
    ],

    'piwik' => [
        'url'     => env('PIWIK_URL', 'https://piwik.octofox.de/'),
        'site_id' => env('PIWIK_SITE_ID', 15),
    ],

    'log' => [
        'error'   => [
            'danger_hour'  => 10,
            'danger_day'   => 20,
            'warning_hour' => 5,
            'warning_day'  => 10,
        ],
        'warning' => [
            'danger_hour'  => 20,
            'danger_day'   => 40,
            'warning_hour' => 10,
            'warning_day'  => 20,
        ],
    ],

    'transform_types' => [
        'collection' => 'collection',
        'item'       => 'item',
        'null'       => 'NullResource',
    ],
];
