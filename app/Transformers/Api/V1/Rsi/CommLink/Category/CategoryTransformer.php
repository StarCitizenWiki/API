<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 10:31
 */

namespace App\Transformers\Api\V1\Rsi\CommLink\Category;

use App\Models\Rsi\CommLink\Category\Category;
use League\Fractal\TransformerAbstract;

/**
 * Category Transformer
 */
class CategoryTransformer extends TransformerAbstract
{
    /**
     * @param \App\Models\Rsi\CommLink\Category\Category $category
     *
     * @return array
     */
    public function transform(Category $category): array
    {
        return [
            'name' => $category->name,
            'slug' => $category->slug,
            'url' => app('api.url')->version('v1')->route(
                'api.v1.rsi.comm-links.categories.show',
                [$category->getRouteKey()]
            ),
        ];
    }
}
