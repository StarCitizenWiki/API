<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;

/**
 * Manufacturer Transformer
 */
class RelatedArticleTransformer extends V1Transformer
{
    /**
     * @param Article $article
     *
     * @return array
     */
    public function transform(Article $article): array
    {
        return [
            'id' => $article->cig_id,
            'title' => $article->title,
            'url' => $article->url,
            'api_url' => $this->makeApiUrl(
                self::GALACTAPEDIA_ARTICLE_SHOW,
                $article->getRouteKey(),
            )
        ];
    }
}
