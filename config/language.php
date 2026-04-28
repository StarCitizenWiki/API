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
];
