<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Galactapedia\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportCategories extends AbstractBaseDownloadData implements ShouldQueue
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
        app('Log')::info('Importing Galactapedia categories.');

        $result = $this->makeClient()->post('galactapedia/graphql', [
            'query' => <<<QUERY
query GetCategories {
  allCategory {
    edges {
      node {
        id
        name
        slug
        thumbnail {
          url
        }
      }
    }
  }
}
QUERY
        ]);

        $result = $result->json() ?? [];

        if (!isset($result['data']['allCategory']['edges'])) {
            return;
        }

        collect($result['data']['allCategory']['edges'])
            ->map(function ($edge) {
                return $edge['node'] ?? false;
            })
            ->each(function (array $node) {
                Category::updateOrCreate([
                    'cig_id' => $node['id'],
                ], [
                    'name' => $node['name'],
                    'slug' => $node['slug'] ?? Str::slug($node['name']),
                    'thumbnail' => $node['thumbnail']['url'] ?? null,
                ]);
            });
    }
}
