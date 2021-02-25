<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Transformers\Api\V1\AbstractV1Transformer;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer as TranslationTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\Ship\ShipLinkTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

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
