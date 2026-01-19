<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia\Sync;

use App\Jobs\StarCitizen\Galactapedia\ImportArticle;
use App\Jobs\StarCitizen\Galactapedia\ImportCategories;
use App\Services\RsiDownloadClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class SyncGalactapedia implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(RsiDownloadClient $client): void
    {
        ImportCategories::dispatch();

        $result = $client->forRsi()->post('galactapedia/graphql', [
            'query' => <<<'QUERY'
query GetArticles {
  allArticle {
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
        ]);

        $result = $result->json() ?? [];

        if (! isset($result['data']['allArticle']['edges'])) {
            return;
        }

        $jobs = collect($result['data']['allArticle']['edges'])
            ->map(function (array $edge) {
                return $edge['node'] ?? null;
            })
            ->filter()
            ->map(function (array $node) {
                return new ImportArticle($node['id']);
            });

        if ($jobs->isEmpty()) {
            return;
        }

        Bus::batch($jobs->values())
            ->then(function () {
                DispatchArticleProperties::dispatch();
            })
            ->dispatch();
    }
}
