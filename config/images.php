<?php

declare(strict_types=1);

return [

    'sources' => [
        [
            'name' => 'starcitizen.tools',
            'type' => 'mediawiki',
            'api_url' => env('IMAGES_TOOLS_API_URL', 'https://starcitizen.tools/api.php'),
        ],
        [
            'name' => 'star-citizen.wiki',
            'type' => 'mediawiki',
            'api_url' => env('IMAGES_SCW_API_URL', 'https://star-citizen.wiki/api.php'),
        ],
        [
            'name' => 'cstone.space',
            'type' => 'direct',
            'base_url' => env('IMAGES_CSTONE_BASE_URL', 'https://cstone.space/uifimages/'),
        ],
    ],

    'placeholder_patterns' => [
        '/placeholder/i',
    ],

    'throttle_microseconds' => 200_000,

];
