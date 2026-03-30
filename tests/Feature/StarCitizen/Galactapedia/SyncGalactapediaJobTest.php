<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\ImportArticle;
use App\Jobs\StarCitizen\Galactapedia\ImportCategories;
use App\Jobs\StarCitizen\Galactapedia\Sync\DispatchArticleProperties;
use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Services\RsiDownloadClient;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('dispatches categories and batches article imports', function (): void {
    Bus::fake();

    $payload = [
        'data' => [
            'allArticle' => [
                'edges' => [
                    ['node' => ['id' => '1', 'title' => 'Alpha', 'slug' => 'alpha']],
                    ['node' => ['id' => '2', 'title' => 'Beta', 'slug' => 'beta']],
                ],
            ],
        ],
    ];

    Http::fake([
        '*' => Http::response($payload, 200),
    ]);

    $job = new SyncGalactapedia;
    $job->handle(new RsiDownloadClient);

    Bus::assertDispatched(ImportCategories::class);

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 2
            && $batch->hasJobs([
                fn (ImportArticle $job): bool => $job->articleId === '1',
                fn (ImportArticle $job): bool => $job->articleId === '2',
            ]);
    });

    Bus::assertNotDispatched(DispatchArticleProperties::class);
});

it('queues article properties after the import batch completes', function (): void {
    config()->set('queue.default', 'sync');

    Queue::fake([
        ImportCategories::class,
        DispatchArticleProperties::class,
    ]);

    Http::fake(function (Request $request) {
        $payload = json_decode($request->body(), true);
        $query = $payload['query'] ?? '';

        if (str_contains($query, 'query GetArticles')) {
            return Http::response([
                'data' => [
                    'allArticle' => [
                        'edges' => [
                            ['node' => ['id' => '1', 'title' => 'Alpha', 'slug' => 'alpha']],
                            ['node' => ['id' => '2', 'title' => 'Beta', 'slug' => 'beta']],
                        ],
                    ],
                ],
            ], 200);
        }

        $articleId = $payload['variables']['query'] ?? null;

        return Http::response([
            'data' => [
                'Article' => [
                    'id' => $articleId,
                    'title' => $articleId === '1' ? 'Alpha' : 'Beta',
                    'slug' => $articleId === '1' ? 'alpha' : 'beta',
                    'body' => '',
                    'template' => [],
                    'thumbnail' => null,
                    'categories' => [],
                    'tags' => [],
                    'relatedArticles' => [],
                ],
            ],
        ], 200);
    });

    $job = new SyncGalactapedia;
    $job->handle(new RsiDownloadClient);

    expect(Article::query()->orderBy('cig_id')->pluck('slug', 'cig_id')->all())
        ->toBe([
            '1' => 'alpha',
            '2' => 'beta',
        ]);

    Queue::assertPushedTimes(ImportCategories::class, 1);
    Queue::assertPushedTimes(DispatchArticleProperties::class, 1);
});
