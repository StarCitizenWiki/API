<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use League\Fractal\TransformerAbstract;

/**
 * Image Transformer
 */
class CommLinkLinkTransformer extends TransformerAbstract
{
    /**
     * @param CommLink $commLink
     *
     * @return array
     */
    public function transform(CommLink $commLink): array
    {
        return [
            'api_url' => app('api.url')->version('v1')->route(
                'api.v1.rsi.comm-links.show',
                [$commLink->getRouteKey()]
            ),
        ];
    }
}
