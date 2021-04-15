<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportArticles extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $result = $this->makeClient()->post('galactapedia/graphql', [
            'query' => <<<QUERY
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

        if (!isset($result['data']['allArticle']['edges'])) {
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
