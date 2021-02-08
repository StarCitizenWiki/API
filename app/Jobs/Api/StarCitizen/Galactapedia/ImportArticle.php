<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Galactapedia\Article;
use App\Models\Api\StarCitizen\Galactapedia\Category;
use App\Models\Api\StarCitizen\Galactapedia\Tag;
use App\Models\Api\StarCitizen\Galactapedia\Template;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportArticle extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $articleId;
    private Article $article;

    /**
     * Create a new job instance.
     *
     * @param string $articleId
     */
    public function __construct(string $articleId)
    {
        $this->articleId = $articleId;

        app('Log')::info(sprintf('Importing Galactapedia Article "%s"', $articleId));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $result = $this->makeClient()->post('galactapedia/graphql', [
            'query' => <<<QUERY
query ArticleByID(\$query: ID!) {
  Article(id: \$query) {
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

        if (!isset($result['data']['Article'])) {
            return;
        }

        $data = $result['data']['Article'];

        /** @var Article $article */
        $this->article = Article::updateOrCreate([
            'cig_id' => $data['id'],
        ], [
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'thumbnail' => $data['thumbnail']['url'] ?? null
        ]);

        $this->article->translations()->updateOrCreate([
            'locale_code' => 'en_EN',
        ], [
            'translation' => $data['body']
        ]);

        $this->syncTemplates($data['template'] ?? []);
        $this->syncCategories($data['categories'] ?? []);
        $this->syncTags($data['tags'] ?? []);
        $this->syncRelatedArticles($data['relatedArticles'] ?? []);
    }

    private function syncTemplates(array $data): void
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

        $this->article->templates()->sync($ids);
    }

    private function syncCategories(array $data): void
    {
        $ids = collect($data)
            ->map(function (array $category) {
                return Category::updateOrCreate([
                    'cig_id' => $category['id'],
                ], [
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                ]);
            })
            ->map(function (Category $category) {
                return $category->id;
            })
            ->collect();

        $this->article->categories()->sync($ids);
    }

    private function syncTags(array $data): void
    {
        $ids = collect($data)
            ->map(function (array $tag) {
                return Tag::updateOrCreate([
                    'cig_id' => $tag['id'],
                ], [
                    'name' => $tag['name'],
                    'slug' => $tag['slug'],
                ]);
            })
            ->map(function (Tag $tag) {
                return $tag->id;
            })
            ->collect();

        $this->article->tags()->sync($ids);
    }

    private function syncRelatedArticles(array $data): void
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

        $this->article->related()->sync($ids);
    }
}
