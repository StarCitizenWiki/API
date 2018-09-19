<?php

namespace App\Transformers\Api\V1\StarCitizen;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Transformers\Api\LocaleAwareTransformerInterface as LocaleAwareTransformer;
use League\Fractal\TransformerAbstract;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * Class AbstractTranslationTransformer
 * @package App\Transformers\Api\V1\StarCitizen
 */
class AbstractTranslationTransformer extends TransformerAbstract implements LocaleAwareTransformer
{
    private $localeCode;

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    public function setLocale(string $localeCode)
    {
        $this->localeCode = $localeCode;
    }

    /**
     * If a valid locale code is set this function will return the corresponding translation or use english as a fallback
     * @Todo Generate Array with translations that used the english fallback
     *
     * @param \App\Models\Api\Translation\AbstractHasTranslations $model
     *
     * @return array|string the Translation
     */
    protected function getTranslation(HasTranslations $model)
    {
        app('Log')::debug(
            "Relation translations for Model ".get_class($model)." is loaded: {$model->relationLoaded('translations')}"
        );

        $translations = [];

        $model->translations->each(
            function ($translation) use (&$translations) {
                if (null !== $this->localeCode) {
                    if ($translation->locale_code === $this->localeCode ||
                        (empty($translations) && $translation->locale_code === config('language.english'))) {
                        $translations = $translation->translation;
                    } else {
                        // Translation already found, exit loop
                        return false;
                    }

                    return $translation;
                } else {
                    $translations[$translation->locale_code] = $translation->translation;
                }
            }
        );

        return $translations;
    }
}