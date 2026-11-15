<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Category;
use App\Services\GalactapediaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ImportCategories implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    /**
     * Execute the job.
     */
    public function handle(GalactapediaClient $client): void
    {
        Log::info('Importing Galactapedia categories.');

        $result = $client->query(<<<'QUERY'
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
QUERY);

        if (! isset($result['allCategory']['edges'])) {
            return;
        }

        collect($result['allCategory']['edges'])
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
