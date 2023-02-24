<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Category;

use App\Models\Rsi\CommLink\Category\Category;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'comm_link_category',
    title: 'Comm-Link Category',
    description: 'Category of a Comm-Link',
    properties: [
        new OA\Property(property: 'name', description: 'Category Name', type: 'string'),
        new OA\Property(property: 'slug', description: 'Slug of the name', type: 'string'),
        new OA\Property(property: 'api_url', description: 'Link to the api page', type: 'string'),
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
            'name' => $category->name,
            'slug' => $category->slug,
            'api_url' => $this->makeApiUrl(self::COMM_LINKS_CATEGORIES_SHOW, $category->getRouteKey()),
        ];
    }
}
