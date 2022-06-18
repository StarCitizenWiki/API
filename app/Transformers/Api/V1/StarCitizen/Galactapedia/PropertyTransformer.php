<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\ArticleProperty;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactpedia_property',
    title: 'Galctapedia article property',
    description: 'Property of an article',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'value', type: 'string'),
    ],
    type: 'object'
)]
class PropertyTransformer extends V1Transformer
{
    /**
     * @param ArticleProperty $property
     *
     * @return array
     */
    public function transform(ArticleProperty $property): array
    {
        return [
            'name' => $property->name,
            'value' => $property->content,
        ];
    }
}
