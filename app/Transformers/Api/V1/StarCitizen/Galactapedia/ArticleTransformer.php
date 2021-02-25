<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

/**
 * Manufacturer Transformer
 */
class ArticleTransformer extends AbstractTranslationTransformer
{
    protected $availableIncludes = [
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
            'url' => $article->url,
            'api_url' => $this->makeApiUrl(
                self::GALACTAPEDIA_ARTICLE_SHOW,
                $article->getRouteKey(),
            )
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
