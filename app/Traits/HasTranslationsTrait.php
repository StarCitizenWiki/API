<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 13.07.2018
 * Time: 13:34
 */

namespace App\Traits;

/**
 * Trait HasTranslationsTrait
 */
trait HasTranslationsTrait
{
    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function english()
    {
        return $this->translations()->english()->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function german()
    {
        return $this->translations()->german()->first();
    }

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
}
