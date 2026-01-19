<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Galactapedia;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\TranslationResolver;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'galactapedia_article',
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
        new OA\Property(property: 'created_at', type: 'string'),
        new OA\Property(
            property: 'categories',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactapedia_category'),
        ),
        new OA\Property(
            property: 'tags',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactapedia_tag'),
        ),
        new OA\Property(
            property: 'properties',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactapedia_property'),
        ),
        new OA\Property(
            property: 'related_articles',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/galactpedia_related_article'),
        ),
        new OA\Property(
            property: 'translations',
            oneOf: [
                new OA\Schema(type: 'string'),
                new OA\Schema(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/translation'),
                ),
            ],
        ),
        new OA\Property(property: 'created_at_human', type: 'string', example: '1 hour ago'),
    ],
    type: 'object'
)]
class ArticleResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'categories',
            'properties',
            'tags',
            'related',
        ];
    }

    public function toArray(Request $request): array
    {
        $template = $this->templates->isEmpty() ? null : $this->templates[0]->template;
        $categoryList = $this->categories->pluck('name')->filter()->implode(', ');
        $tagList = $this->tags->pluck('name')->filter()->implode(', ');

        return [
            'id' => $this->cig_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'thumbnail' => $this->thumbnail,
            'type' => $template,
            'template' => $template,
            'category' => $categoryList !== '' ? $categoryList : null,
            'tag' => $tagList !== '' ? $tagList : null,
            'rsi_url' => $this->url,
            'api_url' => route(
                'galactapedia.show',
                ['article' => $this->getRouteKey()],
            ),
            'web_url' => route(
                'web.galactapedia.show',
                ['article' => $this->getRouteKey()],
            ),
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'categories_count' => $this->categories_count,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'tags_count' => $this->tags_count,
            'properties' => PropertyResource::collection($this->whenLoaded('properties')),
            'related_articles' => RelatedArticleResource::collection($this->whenLoaded('related')),
            'related_articles_count' => $this->related_articles_count,
            'translations' => TranslationResolver::resolve($this, $request),
            'created_at' => $this->created_at->toIso8601String(),
            'created_at_human' => $this->created_at->diffForHumans(),
        ];
    }
}
