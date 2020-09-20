<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Category;

use App\Models\Rsi\CommLink\Category\Category;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

/**
 * Category Transformer
 */
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
