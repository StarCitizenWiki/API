<?php

declare(strict_types=1);

namespace App\Models\System;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Language
 */
class Language extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'locale_code';

    public function scopeGerman(Builder $query): Builder
    {
        return $query->where('local_code', 'de_DE');
    }

    public function scopeEnglish(Builder $query): Builder
    {
        return $query->where('local_code', 'en_EN');
    }
}
