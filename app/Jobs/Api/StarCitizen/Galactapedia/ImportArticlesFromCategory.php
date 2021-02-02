<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Galactapedia\GalactapediaArticle;
use App\Models\Api\StarCitizen\Galactapedia\GalactapediaCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportArticlesFromCategory extends AbstractBaseDownloadData implements ShouldQueue
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
        $cat = GalactapediaCategory::all()->first()->toArray();

        $result = self::$client->post('galactapedia/graphql', [
            'query' => <<<QUERY
         {
          cat_{$cat['cig_id']}: allArticle(where: {categories: {contains: "${cat['cig_id']}"}}) {
              edges {
                node {
                id,
                title,
                slug,
                body
                }
              }
            
          }
        }
QUERY
        ]);

        $result = $result->json() ?? [];

        if (!isset($result['data']["cat_{$cat['cig_id']}"]['edges'])) {
            return;
        }

        collect($result['data']["cat_{$cat['cig_id']}"]['edges'])->map(function ($edge) {
            return $edge['node'] ?? false;
        })->each(function (array $node) {
            /** @var GalactapediaArticle $article */
            $article = GalactapediaArticle::create([
                'cig_id' => $node['id'],
                'title' => $node['title'],
                'slug' => $node['slug'] ?? Str::slug($node['name']),
            ]);

            $article->translations()->create([
                'locale_code' => 'en_EN',
                'translation' => $node['body']
            ]);
        });
    }
}
