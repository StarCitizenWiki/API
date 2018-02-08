<?php declare(strict_types = 1);

return [
    'fundimage' => [
        'type' => [
            'funding' => 'funding_only',
            'text' => 'funding_and_text',
            'bars' => 'funding_and_bars',
        ],
        'save_path' => [
            'key' => 'tools_media_images',
            'relative' => join(DIRECTORY_SEPARATOR, ['app', 'tools', 'media', 'images'.DIRECTORY_SEPARATOR]),
        ],
    ],
];
