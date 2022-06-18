<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Transformers\Api\V1\AbstractV1Transformer as V1Transformer;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactpedia_related_article',
    title: 'Galactapedia related article',
    description: 'Related article for this galactapedia article',
    properties: [
        new OA\Property(property: 'id', type: 'string'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'url', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string'),
    ],
    type: 'object'
)]
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
