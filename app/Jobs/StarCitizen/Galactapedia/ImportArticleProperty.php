<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizen\Galactapedia;

use App\Models\StarCitizen\Galactapedia\Article;
use App\Services\GalactapediaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ImportArticleProperty implements ShouldQueue
{
    use Queueable;

    public int $timeout = 120;

    private Article $article;

    public function __construct(
        public readonly int $articleId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(GalactapediaClient $client): void
    {
        $article = Article::query()
            ->with('templates')
            ->find($this->articleId);

        if ($article === null) {
            return;
        }

        $this->article = $article;

        if ($this->article->templates->isEmpty()) {
            Log::info(sprintf('Article "%s" has no Templates, skipping.', $this->article->title));

            return;
        }

        $template = $this->article->templates[0]->template;

        if (! preg_match('/^[_A-Za-z][_0-9A-Za-z]*$/D', $template)) {
            return;
        }

        $fields = $this->getTemplateFields($client);

        if ($fields === null) {
            $this->delete();

            return;
        }

        $strFields = implode("\n", $fields->toArray());

        $result = $client->query(<<<QUERY
query ArticleProperties(\$id: ID!) {
  Article(id: \$id) {
    template {
      ... on {$template} {
        {$template} {
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
QUERY, ['id' => $this->article->cig_id]);

        if (! isset($result['Article']['template'][0][$this->article->templates[0]->template])) {
            return;
        }

        $result = $result['Article']['template'][0][$this->article->templates[0]->template];

        collect($fields)->each(function (string $field) use ($result) {
            if (! isset($result[$field]) || empty($result[$field])) {
                return;
            }

            $match = preg_match_all('/\[([^\]]+)\][^\)]+\)/', $result[$field], $matches);

            if ($match === false || $match === 0 || ! isset($matches[1])) {
                $matches = [
                    [],
                    [
                        $result[$field],
                    ],
                ];
            }

            collect($matches[1])
                ->filter(function (string $match) {
                    return ! empty($match);
                })
                ->each(function (string $match) use ($field) {
                    $this->article->properties()->updateOrCreate([
                        'name' => $field,
                        'content' => $match,
                    ]);
                });
        });
    }

    private function getTemplateFields(GalactapediaClient $client): ?Collection
    {
        $result = $client->query(<<<'QUERY'
query ArticleAfterCursor($type: String!) {
  template: __type(name: $type) {
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
QUERY, [
            'type' => sprintf(
                '%s%s',
                $this->article->templates[0]->template,
                $this->article->templates[0]->template,
            ),
        ]);

        if (! isset($result['template']['fields'])) {
            return null;
        }

        $data = $result['template']['fields'];

        return collect($data)->reject(function (array $field) {
            return $field['name'] === '_meta';
        })
            ->map(function (array $field) {
                return $field['name'];
            });
    }
}
