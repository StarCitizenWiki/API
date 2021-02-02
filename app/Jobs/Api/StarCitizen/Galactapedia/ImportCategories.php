<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Galactapedia\GalactapediaCategory;
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
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->makeClient();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $result = self::$client->post('galactapedia/graphql', [
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

        collect($result['data']['allCategory']['edges'])->map(function ($edge) {
            return $edge['node'] ?? false;
        })->each(function (array $node) {

            GalactapediaCategory::create([
                'cig_id' => $node['id'],
                'name' => $node['name'],
                'slug' => $node['slug'] ?? Str::slug($node['name']),
                'thumbnail' => $node['thumbnail']['url'] ?? null,
            ]);
        });
    }
}
