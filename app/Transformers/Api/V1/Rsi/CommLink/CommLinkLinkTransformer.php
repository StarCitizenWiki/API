<?php declare(strict_types=1);

namespace App\Transformers\Api\V1\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;

/**
 * Image Transformer
 */
class CommLinkLinkTransformer extends V1Transformer
{
    /**
     * @param CommLink $commLink
     *
     * @return array
     */
    public function transform(CommLink $commLink): array
    {
        return [
            'api_url' => $this->makeApiUrl(self::COMM_LINKS_SHOW, $commLink->getRouteKey()),
        ];
    }
}
