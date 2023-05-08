<?php

namespace App\Http\Resources;

use App\Models\SC\Item\ItemTranslation;
use App\Models\System\Language;
use Illuminate\Http\Request;

abstract class AbstractTranslationResource extends AbstractBaseResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    protected function getTranslation($model, Request $request, $translationKey = 'translation')
    {
        $translations = $model->translationsCollection();

        $locale = $request->get('locale');
        if (!empty($locale)) {
            return $this->getSingleTranslation($translations, $request->get('locale'), $translationKey);
        }

        return $translations->map(
            function ($translation) use ($translationKey, $model, $translations) {
                if ($translation instanceof Language) {
                    return $this->getSingleTranslation($translations, 'en_EN', $translationKey);
                }

                return $this->getSingleTranslation($translation, $translationKey);
            }
        )->filter(
            function ($translations) {
                return !empty($translations);
            }
        );
    }

    private function getSingleTranslation($translations, string $locale, $translationKey = 'translation'): ?string
    {
        $translation = null;

        if ($translations instanceof ItemTranslation) {
            return $translations[$translationKey];
        }

        if ($translations->has($locale) && !$translations->get($locale) instanceof Language) {
            $translation = $translations->get($locale)[$translationKey];
        } elseif ($translations->has('en_EN')) {
            $translation = $translations->get('en_EN')[$translationKey];
        }

        return $translation;
    }
}
