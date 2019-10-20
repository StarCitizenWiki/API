<?php

declare(strict_types=1);

namespace App\Models\System\Translation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Base Translation Class which holds Language Query Scopes.
 */
abstract class AbstractHasTranslations extends Model
{
    private const LOCALE_CODE = 'locale_code';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function translations();

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function english(): ?Model
    {
        return $this->translations->keyBy(self::LOCALE_CODE)->get('en_EN', null);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function german(): ?Model
    {
        return $this->translations->keyBy(self::LOCALE_CODE)->get('de_DE', null);
    }

    /**
     * Translations Right Joined with Languages.
     *
     * @return \Illuminate\Support\Collection
     */
    public function translationsCollection(): Collection
    {
        $table = Str::singular($this->getTable()).'_translations';

        $collection = DB::table($table)->select('*')->rightJoin(
            'languages',
            function ($join) use ($table) {
                /* @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    "{$table}.locale_code",
                    '=',
                    'languages.locale_code'
                )->where($table.'.'.Str::singular($this->getForeignKey()), '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy(self::LOCALE_CODE);
    }
}
