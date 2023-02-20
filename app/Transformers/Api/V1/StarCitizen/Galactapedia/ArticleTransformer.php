<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
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
        new OA\Property(property: 'created_at', type: 'timestamp'),
        new OA\Property(
            property: 'categories',
            properties: [
                new OA\Property(
                    property: 'categories',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/galactapedia_category',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            nullable: true,
        ),
        new OA\Property(
            property: 'tags',
            properties: [
                new OA\Property(
                    property: 'tags',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/galactapedia_tag',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            nullable: true,
        ),
        new OA\Property(
            property: 'properties',
            properties: [
                new OA\Property(
                    property: 'properties',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/galactapedia_property',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            nullable: true,
        ),
        new OA\Property(
            property: 'related_articles',
            properties: [
                new OA\Property(
                    property: 'related_articles',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/galactpedia_related_article',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            nullable: true,
        ),
        new OA\Property(
            property: 'english',
            properties: [
                new OA\Property(
                    property: 'english',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/galactpedia_translation',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
            nullable: true,
        ),
    ],
    type: 'object'
)]
class ArticleTransformer extends AbstractTranslationTransformer
{
    protected array $availableIncludes = [
        'categories',
        'properties',
        'tags',
        'related_articles',
        'english',
    ];

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
            'slug' => $article->slug,
            'thumbnail' => $article->thumbnail,
            'type' => $article->templates->isEmpty() ? null : $article->templates[0]->template,
            'rsi_url' => $article->url,
            'api_url' => $this->makeApiUrl(
                self::GALACTAPEDIA_ARTICLE_SHOW,
                $article->getRouteKey(),
            ),
            'created_at' => $article->created_at,
        ];
    }

    public function includeCategories(Article $article): Collection
    {
        return $this->collection($article->categories, new CategoryTransformer());
    }

    public function includeTags(Article $article): Collection
    {
        return $this->collection($article->tags, new TagTransformer());
    }

    public function includeProperties(Article $article): Collection
    {
        return $this->collection($article->properties, new PropertyTransformer());
    }

    public function includeRelatedArticles(Article $article): Collection
    {
        return $this->collection($article->related, new RelatedArticleTransformer());
    }

    /**
     * @param Article $article
     *
     * @return Item
     */
    public function includeEnglish(Article $article): Item
    {
        $translation = $article->english();

        return $this->item($translation, new TranslationTransformer());
    }
}
