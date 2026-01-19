<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Sources
    |--------------------------------------------------------------------------
    |
    | These paths define where translation data files are located for each
    | supported language. The Labels service uses these paths to load
    | translation data during item import.
    |
    */

    'sources' => [
        'zh_CN' => storage_path('app/api/ScToolBoxLocales/chinese_(simplified)/global.ini'),
        'de_DE' => storage_path('app/api/StarCitizenDeutsch/live/global.ini'),
    ],

    'sources_git' => [
        'de_DE' => 'https://github.com/rjcncpt/StarCitizen-Deutsch-INI',
        'zh_CN' => 'https://github.com/StarCitizenToolBox/LocalizationData',
    ],

    /*
    |--------------------------------------------------------------------------
    | Labels JSON Path
    |--------------------------------------------------------------------------
    |
    | Path to the labels.json file containing label definitions.
    |
    */

    'labels_json' => storage_path('app/api/scunpacked-data/labels.json'),
];
