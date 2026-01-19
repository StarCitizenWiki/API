<?php

declare(strict_types=1);

use App\Models\System\Language;

return [
    'english' => Language::ENGLISH,
    'german' => Language::GERMAN,
    'chinese' => Language::CHINESE,

    // @Todo Codes need to be updated if more are added
    'codes' => [
        'de',
        'en',
        'zh',
    ],

    'enable_galactapedia_language_links' => env('GALACTAPEDIA_LANGUAGE_LINKS', false),
    'translate_wrap_galactapedia' => env('GALACTAPEDIA_TRANSLATE_WRAP', false),

    'translate_wrap_commlinks' => env('COMMLINKS_TRANSLATE_WRAP', false),
];
