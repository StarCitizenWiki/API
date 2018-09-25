<?php declare(strict_types = 1);

namespace App\Models\System\Translation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Base Translation Class which holds Language Query Scopes
 */
abstract class AbstractHasTranslations extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function translations();

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function english()
    {
        return $this->translations()->english()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function german()
    {
        return $this->translations()->german()->first();
    }

    /**
     * Get a Translation of a given locale
     *
     * @param string $localeCode
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function ofLanguage(string $localeCode)
    {
        return $this->translations()->ofLanguage($localeCode)->first();
    }

    /**
     * Group Translations by Locale Code
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTranslationsAttribute()
    {
        /** @var \Illuminate\Support\Collection $col */
        $col = $this->getRelationValue('translations');

        return $col->keyBy('locale_code');
    }

    /**
     * Translations Right Joined with Languages
     *
     * @return \Illuminate\Support\Collection
     */
    public function translationsCollection(): Collection
    {
        $table = str_singular($this->getTable()).'_translations';

        $collection = DB::table($table)->select('*')->rightJoin(
            'languages',
            function ($join) use ($table) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    "{$table}.locale_code",
                    '=',
                    'languages.locale_code'
                )->where($table.'.'.str_singular($this->getForeignKey()), '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
