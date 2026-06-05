<?php

declare(strict_types=1);

namespace App\Models\System;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 */
class Language extends Model
{
    use HasFactory;

    public const ENGLISH = 'en';

    public const GERMAN = 'de';

    public const CHINESE = 'zh';

    public const FRENCH = 'fr';

    public const OLD_LANG_MAP = [
        self::ENGLISH => 'en_EN',
        self::GERMAN => 'de_DE',
        self::CHINESE => 'zh_CN',
        self::FRENCH => 'fr_FR',
    ];

    public const LABEL_MAP = [
        'en_EN' => 'English',
        self::ENGLISH => 'English',
        'de_DE' => 'German',
        self::GERMAN => 'German',
        'zh_CN' => 'Chinese',
        self::CHINESE => 'Chinese',
        'fr_FR' => 'French',
        self::FRENCH => 'French',
    ];
}
