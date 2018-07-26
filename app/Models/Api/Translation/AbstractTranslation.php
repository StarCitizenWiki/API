<?php declare(strict_types = 1);

namespace App\Models\Api\Translation;

use App\Traits\HasCompositePrimaryKeyTrait as CompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Base Translation Class which holds Language Query Scopes
 */
abstract class AbstractTranslation extends Model
{
    use CompositePrimaryKey;

    const ATTR_LOCALE_CODE = '.locale_code';
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
        return $query->where($this->getTable().self::ATTR_LOCALE_CODE, config('language.english'));
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
        return $query->where($this->getTable().self::ATTR_LOCALE_CODE, config('language.german'));
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string                                $localeCode
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfLanguage(Builder $query, string $localeCode)
    {
        return $query->where($this->getTable().self::ATTR_LOCALE_CODE, $localeCode);
    }
}
