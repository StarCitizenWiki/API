<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactpedia_related_article_v2',
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
class RelatedArticleResource extends AbstractBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->cig_id,
            'title' => $this->title,
            'url' => $this->url,
            'api_url' => $this->makeApiUrl(
                self::GALACTAPEDIA_ARTICLE_SHOW,
                $this->getRouteKey(),
            )
        ];
    }
}
