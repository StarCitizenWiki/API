<?php declare(strict_types = 1);

return [
    'fundimage' => [
        'type' => [
            'funding' => 1,
            'text' => 2,
            'bars' => 3,
        ],
        'save_path' => [
            'key' => 'tools_media_images',
            'relative' => join(DIRECTORY_SEPARATOR, ['app', 'tools', 'media', 'images'.DIRECTORY_SEPARATOR]),
        ],
    ],
];
