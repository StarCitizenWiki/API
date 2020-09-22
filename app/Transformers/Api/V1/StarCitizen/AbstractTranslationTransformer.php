<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen;

use App\Models\System\Language;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Models\System\Translation\AbstractTranslation;
use App\Transformers\Api\LocalizableTransformerInterface as LocaleAwareTransformer;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use Illuminate\Support\Collection;

/**
 * Class AbstractTranslationTransformer
 */
abstract class AbstractTranslationTransformer extends V1Transformer implements LocaleAwareTransformer
{
    /**
     * Array containing missing translations for each transformed model
     *
     * @var array
     */
    protected array $missingTranslations = [];

    /**
     * The target language
     *
     * @var string
     */
    private $localeCode;

    /**
     * Set the Locale
     *
     * @param string $localeCode
     */
    public function setLocale(string $localeCode): void
    {
        $this->localeCode = $localeCode;
    }

    /**
     * If a valid locale code is set this function will return the corresponding translation or use english as a
     * fallback
     *
     * @param HasTranslations $model
     *
     * @param string|array    $translationKey
     *
     * @return array|string the Translation
     */
    protected function getTranslation(HasTranslations $model, $translationKey = 'translation')
    {
        $translations = $model->translationsCollection();

        if (isset($this->localeCode)) {
            return $this->getSingleTranslation(
                $translations[$this->localeCode],
                $translationKey,
                $model,
                $translations
            );
        }

        $data = $translations->map(
            function ($translation) use ($translationKey, $model, $translations) {
                return $this->getSingleTranslation($translation, $translationKey, $model, $translations);
            }
        )->filter(
            function ($translations) {
                return !empty($translations);
            }
        );

        // Ugly
        // Maps translations to translationKey = [en_EN => '', de_DE => '']
        if (is_array($translationKey)) {
            $return = [];

            $data->each(
                function ($translations, $localeCode) use (&$return) {
                    foreach ($translations as $translationKey => $translation) {
                        if (!isset($return[$translationKey])) {
                            $return[$translationKey] = [];
                        }

                        $return[$translationKey][$localeCode] = $translation;
                    }
                }
            );

            return $return;
        }

        return $data->toArray();
    }

    /**
     * Get a singular translation by key
     * Returns english fallback is key is unavailable
     *
     * @param Language|AbstractTranslation $translation
     * @param string|array                 $translationKey
     * @param HasTranslations              $model
     * @param Collection                   $translations
     *
     * @return array|mixed
     */
    private function getSingleTranslation(
        $translation,
        $translationKey,
        HasTranslations $model,
        Collection $translations
    ) {
        $inArray = in_array($translation->locale_code, $this->missingTranslations, true);

        if ($translation instanceof Language && !$inArray) {
            $this->addMissingTranslation($translation->locale_code);
        }

        if (is_array($translationKey)) {
            $translationData = [];

            foreach ($translationKey as $key) {
                $translationData[$key] = $this->getSingleTranslation($translation, $key, $model, $translations);
            }

            return $translationData;
        }

        if (!isset($translation[$translationKey])) {
            $this->addMissingTranslation($model->getRouteKey());

            return $translations['en_EN'][$translationKey];
        }

        return $translation[$translationKey];
    }

    /**
     * Adds a missing translation key to the array if it does not already exist
     *
     * @param string $key The key to add
     */
    private function addMissingTranslation(string $key): void
    {
        if (!in_array($key, $this->missingTranslations, true)) {
            $this->missingTranslations[] = $key;
        }
    }
}
