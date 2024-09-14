<?php

declare(strict_types=1);

namespace App\Models\System;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 */
class Language extends Model
{
    use HasFactory;

    public const ENGLISH = 'en_EN';

    public const GERMAN = 'de_DE';

    public const CHINESE = 'zh_CN';

    public $incrementing = false;

    protected $primaryKey = 'locale_code';

    public function scopeGerman(Builder $query): Builder
    {
        return $query->where('local_code', self::GERMAN);
    }

    public function scopeEnglish(Builder $query): Builder
    {
        return $query->where('local_code', self::ENGLISH);
    }

    public function scopeChinese(Builder $query): Builder
    {
        return $query->where('local_code', self::CHINESE);
    }
}
