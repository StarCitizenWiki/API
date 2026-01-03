<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Galactapedia\ImportArticle;
use App\Jobs\StarCitizen\Galactapedia\ImportCategories;
use App\Jobs\StarCitizen\Galactapedia\Sync\DispatchArticleProperties;
use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use App\Services\RsiDownloadClient;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

it('dispatches categories, batches articles, then queues properties', function (): void {
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
        $callbacks = $batch->thenCallbacks();

        if (! empty($callbacks)) {
            $callbacks[0]();
        }

        return $batch->jobs->count() === 2
            && $batch->jobs->every(fn ($job) => $job instanceof ImportArticle)
            && ! empty($callbacks);
    });

    Bus::assertDispatched(DispatchArticleProperties::class);
});
