<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\ArticleTranslation;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

class TranslationTransformer extends V1Transformer
{
    /**
     * @param ArticleTranslation|null $translation
     *
     * @return array
     */
    public function transform(?ArticleTranslation $translation): array
    {
        if ($translation === null) {
            return [];
        }

        return [
            'locale' => $translation->locale_code,
            'translation' => $translation->translation,
        ];
    }
}
