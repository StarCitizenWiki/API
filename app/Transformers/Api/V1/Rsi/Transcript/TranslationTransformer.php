<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\Transcript;

use App\Models\Transcript\TranscriptTranslation;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

class TranslationTransformer extends V1Transformer
{
    /**
     * @param TranscriptTranslation|null $translation
     *
     * @return array
     */
    public function transform(?TranscriptTranslation $translation): array
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
