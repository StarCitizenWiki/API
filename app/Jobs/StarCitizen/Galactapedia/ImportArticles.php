<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Services\RsiDownloadClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ImportArticles implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(RsiDownloadClient $client): void
    {
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

        collect($result['data']['allArticle']['edges'])
            ->map(function ($edge) {
                return $edge['node'];
            })
            ->each(function (array $node) {
                ImportArticle::dispatch($node['id']);
            });
    }
}
