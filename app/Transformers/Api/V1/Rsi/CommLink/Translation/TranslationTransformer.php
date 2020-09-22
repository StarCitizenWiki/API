<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Translation;

use App\Models\Rsi\CommLink\CommLinkTranslation;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

class TranslationTransformer extends V1Transformer
{
    /**
     * @param CommLinkTranslation|null $translation
     *
     * @return array
     */
    public function transform(?CommLinkTranslation $translation): array
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
