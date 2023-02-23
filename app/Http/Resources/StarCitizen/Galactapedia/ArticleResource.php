<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\TranslationCollection;
use App\Http\Resources\TranslationResourceFactory;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactapedia_article_v2',
    title: 'Galactapedia Article',
    description: 'An article form the Galactapedia',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'thumbnail', type: 'string'),
        new OA\Property(property: 'template', type: 'string', nullable: true),
        new OA\Property(property: 'rsi_url', type: 'string'),
        new OA\Property(property: 'api_url', type: 'string'),
        new OA\Property(property: 'created_at', type: 'timestamp'),
        new OA\Property(
            property: 'categories',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactapedia_category_v2'),
        ),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactapedia_tag_v2'),
        ),
        new OA\Property(
            property: 'properties',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactapedia_property_v2'),
        ),
        new OA\Property(
            property: 'related_articles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactpedia_related_article_v2'),
        ),
        new OA\Property(
            property: 'translations',
            ref: '#/components/schemas/translation_v2',
            nullable: true
        ),
    ],
    type: 'json'
)]
class ArticleResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'categories',
            'properties',
            'tags',
            'relatedArticles',
            'translations'
        ];
    }

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
            'slug' => $this->slug,
            'thumbnail' => $this->thumbnail,
            'type' => $this->templates->isEmpty() ? null : $this->templates[0]->template,
            'rsi_url' => $this->url,
            'api_url' => $this->makeApiUrl(
                self::GALACTAPEDIA_ARTICLE_SHOW,
                $this->getRouteKey(),
            ),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'properties' => PropertyResource::collection($this->whenLoaded('properties')),
            'related_articles' => RelatedArticleResource::collection($this->whenLoaded('related_articles')),
            'translations' => TranslationResourceFactory::getTranslationResource($request, $this->whenLoaded('translations')),
            'created_at' => $this->created_at,
        ];
    }
}
