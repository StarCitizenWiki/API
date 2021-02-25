<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\StarCitizen\Galactapedia\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportArticlesFromCategory extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Category $category;

    /**
     * Create a new job instance.
     *
     * @param Category $category
     */
    public function __construct(Category $category)
    {
        $this->category = $category;
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
query ArticleByCategory(\$query: String) {
  allArticle(where: {categories: {contains: \$query}}) {
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
