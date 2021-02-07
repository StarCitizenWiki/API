<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Galactapedia;

use App\Jobs\AbstractBaseDownloadData;
use App\Models\Api\StarCitizen\Galactapedia\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ImportArticleProperty extends AbstractBaseDownloadData implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Article $article;

    /**
     * Create a new job instance.
     *
     * @param Article $article
     */
    public function __construct(Article $article)
    {
        $this->makeClient();
        $this->article = $article;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if ($this->article->templates->isEmpty()) {
            app('Log')::warning(sprintf('Article "%s" has no Templates!', $this->article->title));
            return;
        }

        $fields = $this->getTemplateFields();

        if ($fields === null) {
            $this->delete();
            return;
        }

        $strFields = implode("\n", $fields->toArray());

        $result = self::$client->post('galactapedia/graphql', [
            'query' => <<<QUERY
{
  Article(id: "{$this->article->cig_id}") {
    template {
      ... on {$this->article->templates[0]->template} {
        {$this->article->templates[0]->template} {
          _meta {
            $strFields
          }
          $strFields
        }
        __typename
      }
    }
  }
}
QUERY,
        ]);

        $result = $result->json() ?? [];

        if (!isset($result['data']['Article']['template'][0][$this->article->templates[0]->template])) {
            return;
        }

        $result = $result['data']['Article']['template'][0][$this->article->templates[0]->template];

        collect($fields)->each(function (string $field) use ($result) {
            if (!isset($result[$field]) || empty($result[$field])) {
                return;
            }

            $match = preg_match_all('/\[([^\]]+)\][^\)]+\)/', $result[$field], $matches);

            if ($match === false || $match === 0 || !isset($matches[1])) {
                return;
            }

            collect($matches[1])
                ->filter(function (string $match) {
                    return !empty($match);
                })
                ->each(function (string $match) use ($field) {
                    $this->article->properties()->updateOrCreate([
                        'name' => $field,
                        'content' => $match,
                    ]);
                });
        });
    }

    private function getTemplateFields(): ?Collection
    {
        $result = self::$client->post('galactapedia/graphql', [
            'query' => <<<QUERY
query ArticleAfterCursor(\$type: String!) {
  template: __type(name: \$type) {
    fields {
      name
      type {
        name
        fields {
          name
        }
      }
    }
  }
}
QUERY,
            'variables' => [
                'type' => sprintf(
                    '%s%s',
                    $this->article->templates[0]->template,
                    $this->article->templates[0]->template,
                ),
            ],
        ]);

        $result = $result->json() ?? [];

        if (!isset($result['data']['template']['fields'])) {
            return null;
        }

        $data = $result['data']['template']['fields'];

        return collect($data)->reject(function (array $field) {
            return $field['name'] === '_meta';
        })
            ->map(function (array $field) {
                return $field['name'];
            });
    }
}
