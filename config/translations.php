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
        'fr_FR' => storage_path('app/api/Scefra/french_(france)/global.ini'),
    ],

    'sources_git' => [
        'de' => 'https://github.com/rjcncpt/StarCitizen-Deutsch-INI',
        'zh' => 'https://github.com/StarCitizenToolBox/LocalizationData',
        'fr' => 'https://github.com/SPEED0U/Scefra',
    ],

    /*
    |--------------------------------------------------------------------------
    | Translation Locales
    |--------------------------------------------------------------------------
    |
    | Locale codes that should be synced from game_labels into translatable
    | models (e.g. game_items.translation). Derived from config sources,
    | but defined explicitly for clarity. Add new locales here when adding
    | a new translation source above.
    |
    */

    'locales' => ['zh', 'de', 'fr'],

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
