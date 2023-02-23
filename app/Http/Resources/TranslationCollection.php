<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'translation_v2',
    title: 'Translations',
    description: 'Translations of an entity',
    properties: [
        new OA\Property(property: 'en_EN', type: 'string'),
        new OA\Property(property: 'de_DE', type: 'string'),
    ],
    type: 'json'
)]
class TranslationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $out = [];
        foreach ($this->collection as $translation) {
            $out[$translation->locale_code] = $translation->translation;
        }

        return $out;
    }
}
