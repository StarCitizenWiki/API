<?php declare(strict_types = 1);

namespace App\Models\Api;

use App\Traits\HasCompositePrimaryKeyTrait as CompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Translation Class which holds Language Query Scopes
 */
abstract class AbstractTranslation extends Model
{
    use CompositePrimaryKey;

    public $incrementing = false;

    /**
     * Language Relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo('App\Models\System\Language');
    }

    /**
     * English Translations
     *1
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnglish(Builder $query)
    {
        return $query->where($this->getTable().'.locale_code', config('language.english'));
    }

    /**
     * German Translations
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGerman(Builder $query)
    {
        return $query->where($this->getTable().'.locale_code', config('language.german'));
    }
}
