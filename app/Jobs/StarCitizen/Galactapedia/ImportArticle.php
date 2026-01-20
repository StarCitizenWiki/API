<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Models\StarCitizen\Galactapedia\Category;
use App\Models\StarCitizen\Galactapedia\Tag;
use App\Models\StarCitizen\Galactapedia\Template;
use App\Models\System\Language;
use App\Services\RsiDownloadClient;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportArticle implements ShouldQueue
{
    use Batchable;
    use Queueable;

    public int $timeout = 120;

    private Article $article;

    public function __construct(
        public readonly string $articleId,
    ) {
        Log::info(sprintf('Importing Galactapedia Article "%s"', $articleId));
    }

    /**
     * Execute the job.
     */
    public function handle(RsiDownloadClient $client): void
    {
        $result = $client->forRsi()->post('galactapedia/graphql', [
            'query' => <<<'QUERY'
query ArticleByID($query: ID!) {
  Article(id: $query) {
    id
    title
    slug
    body
    template {
      __typename
    }
    thumbnail {
      url
    }
    categories {
      ... on Category {
        id
        name
        slug
      }
    }
    tags {
      ... on Tag {
        id
        name
        slug
      }
    }
    relatedArticles {
      ... on Article {
        id
      }
    }
  }
}
QUERY,
            'variables' => [
                'query' => $this->articleId,
            ],
        ]);

        $result = $result->json() ?? [];

        if (! isset($result['data']['Article'])) {
            return;
        }

        $data = $result['data']['Article'];
        $this->disableDuplicates($data);

        /** @var Article $article */
        $this->article = Article::updateOrCreate(
            [
                'cig_id' => $data['id'],
            ],
            [
                'title' => Article::normalizeContent($data['title']),
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'thumbnail' => $data['thumbnail']['url'] ?? null,
            ]
        );

        $translation = Article::normalizeContent($data['body']);

        if ($translation !== '') {
            $this->article->setTranslation('translation', Language::ENGLISH, $translation);
            $this->article->save();
        }

        $changes = [];
        $changes['templates'] = $this->syncTemplates($data['template'] ?? []);
        $changes['categories'] = $this->syncCategories($data['categories'] ?? []);
        $changes['tags'] = $this->syncTags($data['tags'] ?? []);
        $changes['related_articles'] = $this->syncRelatedArticles($data['relatedArticles'] ?? []);

        $this->updateCounts();
    }

    /**
     * Syncs all article templates
     *
     *
     * @return array Changed templates
     */
    private function syncTemplates(array $data): array
    {
        $ids = collect($data)
            ->map(function (array $template) {
                return $template['__typename'] ?? null;
            })
            ->filter(function ($template) {
                return $template !== null;
            })
            ->map(function (string $template) {
                return Template::updateOrCreate([
                    'template' => $template,
                ]);
            })
            ->map(function (Template $template) {
                return $template->id;
            })
            ->collect();

        return $this->article->templates()->sync($ids);
    }

    /**
     * Syncs all article categories
     *
     *
     * @return array Changed categories
     */
    private function syncCategories(array $data): array
    {
        $ids = collect($data)
            ->filter(function ($datum) {
                return $datum !== null;
            })
            ->map(function (array $category) {
                return Category::updateOrCreate(
                    [
                        'cig_id' => $category['id'],
                    ],
                    [
                        'name' => $category['name'],
                        'slug' => $category['slug'],
                    ]
                );
            })
            ->map(function (Category $category) {
                return $category->id;
            })
            ->collect();

        return $this->article->categories()->sync($ids);
    }

    /**
     * Syncs all article tags
     *
     *
     * @return array Changed tags
     */
    private function syncTags(array $data): array
    {
        $ids = collect($data)
            ->filter(function ($tag) {
                return is_array($tag);
            })
            ->map(function (array $tag) {
                return Tag::updateOrCreate(
                    [
                        'cig_id' => $tag['id'],
                    ],
                    [
                        'name' => $tag['name'],
                        'slug' => $tag['slug'],
                    ]
                );
            })
            ->map(function (Tag $tag) {
                return $tag->id;
            })
            ->collect();

        return $this->article->tags()->sync($ids);
    }

    /**
     * Syncs all related articles
     *
     *
     * @return array changed data
     */
    private function syncRelatedArticles(array $data): array
    {
        $ids = collect($data)
            ->filter(function ($related) {
                return $related !== null;
            })
            ->map(function (array $related) {
                return Article::query()->where('cig_id', $related['id'])->first();
            })
            ->filter(function ($related) {
                return $related !== null;
            })
            ->map(function (Article $tag) {
                return $tag->id;
            })
            ->collect();

        return $this->article->related()->sync($ids);
    }

    private function updateCounts(): void
    {
        $this->article->categories_count = $this->article->categories()->count();
        $this->article->tags_count = $this->article->tags()->count();
        $this->article->templates_count = $this->article->templates()->count();
        $this->article->related_articles_count = $this->article->related()->count();
        $this->article->save();
    }

    /**
     * Checks if an article with a given title exists under multiple ids
     * if so, older article will be disabled
     */
    private function disableDuplicates(array $data): void
    {
        /** @var Article $article */
        $article = Article::query()
            ->where('title', Article::normalizeContent($data['title']))
            ->where('disabled', false)
            ->first();
        if ($article === null) {
            return;
        }

        if ($article->cig_id !== $data['id']) {
            Log::info(sprintf(
                'Galactapedia Article "%s" (%s) is duplicate, disabling older one.',
                $article->cleanTitle,
                $article->cig_id,
            ));

            $article->disabled = true;
            $article->save();
        }
    }
}
