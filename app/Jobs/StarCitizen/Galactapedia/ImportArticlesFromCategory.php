<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Category;
use App\Services\RsiDownloadClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportArticlesFromCategory implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    public function __construct(
        public readonly Category $category,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(RsiDownloadClient $client): void
    {
        $result = $client->forRsi()->post('galactapedia/graphql', [
            'query' => <<<'QUERY'
query ArticleByCategory($query: String) {
  allArticle(where: {categories: {contains: $query}}) {
    edges {
      node {
        id
        title
        slug
      }
    }
  }
}
QUERY,
            'variables' => [
                'query' => $this->category->cig_id,
            ],
        ]);

        $result = $result->json() ?? [];

        if (! isset($result['data']['allArticle']['edges'])) {
            return;
        }

        collect($result['data']['allArticle']['edges'])
            ->map(function ($edge) {
                return $edge['node'];
            })
            ->each(function (array $node) {
                ImportArticle::dispatch($node['id']);
            });
    }
}
