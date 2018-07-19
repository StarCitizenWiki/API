<?php declare(strict_types = 1);

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractTranslation extends Model
{
    public function language()
    {
        return $this->belongsTo('App\Models\System\Language');
    }

    public function scopeEnglish(Builder $query)
    {
        return $query->where('language_id', config('language.english'));
    }

    public function scopeGerman(Builder $query)
    {
        return $query->where('language_id', config('language.german'));
    }
}
