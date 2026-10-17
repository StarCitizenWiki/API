<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\ImportArticle;
use App\Jobs\StarCitizen\Galactapedia\ImportCategories;
use App\Jobs\StarCitizen\Galactapedia\Sync\DispatchArticleProperties;
use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use App\Models\StarCitizen\Galactapedia\Article;
use App\Services\RsiDownloadClient;
use App\Support\Filters\FilterCache;
use Illuminate\Bus\PendingBatch;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

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

it('busts the galactapedia filter cache when the sync job runs', function (): void {
    Bus::fake();

    Http::fake([
        '*' => Http::response(['data' => ['allArticle' => ['edges' => []]]], 200),
    ]);

    $indexKey = 'filters:index:galactapedia';
    $cacheKey = FilterCache::galactapediaKey();
    $secondaryCacheKey = 'filters:galactapedia:filters-hash';

    Cache::forever($indexKey, [$cacheKey, $secondaryCacheKey]);
    Cache::forever($cacheKey, ['cached-galactapedia-list']);
    Cache::forever($secondaryCacheKey, ['cached-filtered-galactapedia-list']);

    (new SyncGalactapedia)->handle(new RsiDownloadClient);

    expect(Cache::get($indexKey))->toBeNull()
        ->and(Cache::get($cacheKey))->toBeNull()
        ->and(Cache::get($secondaryCacheKey))->toBeNull();
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

it('derives the article slug from the title when the graphql response omits it', function (): void {
    Http::fake([
        '*' => Http::response([
            'data' => [
                'Article' => [
                    'id' => '42',
                    'title' => 'Banu Defender',
                    'body' => '',
                    'template' => [],
                    'thumbnail' => null,
                    'categories' => [],
                    'tags' => [],
                    'relatedArticles' => [],
                ],
            ],
        ], 200),
    ]);

    (new ImportArticle('42'))->handle(new RsiDownloadClient);

    expect(Article::query()->where('cig_id', '42')->value('slug'))->toBe('banu-defender');
});
