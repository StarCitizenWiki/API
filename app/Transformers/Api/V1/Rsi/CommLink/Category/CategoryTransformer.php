<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\Rsi\CommLink\Category;

use App\Models\Rsi\CommLink\Category\Category;
use League\Fractal\TransformerAbstract;

/**
 * Category Transformer
 */
class CategoryTransformer extends TransformerAbstract
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
            'api_url' => app('api.url')->version('v1')->route(
                'api.v1.rsi.comm-links.categories.show',
                [$category->getRouteKey()]
            ),
        ];
    }
}
