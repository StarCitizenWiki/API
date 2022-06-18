<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Tag;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactpedia_tag',
    title: 'Galctapedia article tag',
    description: 'Tag of an article',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
    ],
    type: 'object'
)]
class TagTransformer extends V1Transformer
{
    /**
     * @param Tag $tag
     *
     * @return array
     */
    public function transform(Tag $tag): array
    {
        return [
            'id' => $tag->cig_id,
            'name' => $tag->name,
        ];
    }
}
