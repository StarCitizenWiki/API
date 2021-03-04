<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Schedule settings
    |--------------------------------------------------------------------------
    |
    | Flags to enable / disable schedule commands
    |
    */

    'ship_matrix' => [
        'enabled' => env('SCHEDULE_SHIPMATRIX_ENABLE', true),
        'at' => [1, 13], //First time, second Time
    ],

    'starmap' => [
        'enabled' => env('SCHEDULE_STARMAP_ENABLE', true),
    ],

    'comm_links' => [
        'enabled' => env('SCHEDULE_COMM_LINKS_ENABLE', true),
        'download_local' => env('SCHEDULE_COMM_LINKS_DOWNLOAD_IMAGES', false),
    ],

    'galactapedia' => [
        'enabled' => env('SCHEDULE_GALACTAPEDIA_ENABLE', true),
        'create_wiki_pages' => env('SCHEDULE_GALACTAPEDIA_ENABLE_WIKI_PAGES', true),
    ],
];
