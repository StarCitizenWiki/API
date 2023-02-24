<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Category;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactpedia_category',
    title: 'Galctapedia article category',
    description: 'Category of an article',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
    ],
    type: 'object'
)]
class CategoryTransformer extends V1Transformer
{
    /**
     * @param Category $category
     *
     * @return array
     */
    public function transform(Category $category): array
    {
        return [
            'id' => $category->cig_id,
            'name' => $category->name,
        ];
    }
}
