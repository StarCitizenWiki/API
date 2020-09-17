<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\StarCitizen;

use App\Models\System\Language;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Transformers\Api\LocalizableTransformerInterface as LocaleAwareTransformer;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use Illuminate\Support\Collection;

/**
 * Class AbstractTranslationTransformer
 */
abstract class AbstractTranslationTransformer extends V1Transformer implements LocaleAwareTransformer
{
    private $localeCode;

    /**
     * Array containing missing translations for each transformed model
     *
     * @var array
     */
    protected array $missingTranslations = [];

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

    private function getSingleTranslation(
        $translation,
        $translationKey,
        HasTranslations $model,
        Collection $translations
    ) {
        if ($translation instanceof Language) {
            if (!in_array($translation->locale_code, $this->missingTranslations, true)) {
                $this->missingTranslations[] = $translation->locale_code;
            }

            return [];
        }

        if (is_array($translationKey)) {
            $translationData = [];

            foreach ($translationKey as $key) {
                $translationData[$key] = $this->getSingleTranslation($translation, $key, $model, $translations);
            }

            return $translationData;
        }

        if (!isset($translation[$translationKey])) {
            $this->missingTranslations[] = $model->getRouteKey();

            return $translations['en_EN'][$translationKey];
        }

        return $translation[$translationKey];
    }
}
