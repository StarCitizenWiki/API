<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Starmap\Download\DownloadStarsystem;
use App\Jobs\StarCitizen\Starmap\Import\ImportJumppoint;
use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap;
use App\Services\RsiDownloadClient;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

it('uses existing bootup data when available', function (): void {
    Storage::fake('starmap');
    Bus::fake();

    $bootupPayload = [
        'success' => 1,
        'data' => [
            'systems' => [
                'resultset' => [
                    [
                        'id' => 1,
                        'code' => 'SOL',
                        'name' => 'Sol',
                        'celestial_objects' => [],
                    ],
                ],
            ],
            'tunnels' => [
                'resultset' => [
                    [
                        'id' => 10,
                        'entry_id' => 1,
                        'exit_id' => 2,
                        'direction' => 'bidirectional',
                        'name' => 'Sol-Pyro',
                        'size' => 'medium',
                        'entry' => ['status' => 'open'],
                        'exit' => ['status' => 'open'],
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('starmap')->put(
        now()->format('Y-m-d').'/bootup.json',
        json_encode($bootupPayload, JSON_THROW_ON_ERROR)
    );

    Http::fake();

    $job = new SyncStarmap;
    $job->handle(new RsiDownloadClient);

    Http::assertNothingSent();

    Bus::assertDispatchedTimes(ImportJumppoint::class, 1);

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 1
            && $batch->jobs->every(fn ($job) => $job instanceof DownloadStarsystem);
    });
});

it('dispatches downloads in a batch and imports jumppoints', function (): void {
    Storage::fake('starmap');
    Bus::fake();

    $bootupPayload = [
        'success' => 1,
        'data' => [
            'systems' => [
                'resultset' => [
                    [
                        'id' => 1,
                        'code' => 'SOL',
                        'name' => 'Sol',
                        'celestial_objects' => [],
                    ],
                    [
                        'id' => 2,
                        'code' => 'PYRO',
                        'name' => 'Pyro',
                        'celestial_objects' => [],
                    ],
                ],
            ],
            'tunnels' => [
                'resultset' => [
                    [
                        'id' => 10,
                        'entry_id' => 1,
                        'exit_id' => 2,
                        'direction' => 'bidirectional',
                        'name' => 'Sol-Pyro',
                        'size' => 'medium',
                        'entry' => ['status' => 'open'],
                        'exit' => ['status' => 'open'],
                    ],
                ],
            ],
        ],
    ];

    Http::fake([
        '*' => Http::response($bootupPayload, 200),
    ]);

    $job = new SyncStarmap;
    $job->handle(new RsiDownloadClient);

    Bus::assertDispatchedTimes(ImportJumppoint::class, 1);

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 2
            && $batch->jobs->every(fn ($job) => $job instanceof DownloadStarsystem);
    });

    Storage::disk('starmap')->assertExists(now()->format('Y-m-d').'/bootup.json');
});

it('skips starsystem downloads when data already exists', function (): void {
    Storage::fake('starmap');
    Bus::fake();

    $bootupPayload = [
        'success' => 1,
        'data' => [
            'systems' => [
                'resultset' => [
                    [
                        'id' => 1,
                        'code' => 'SOL',
                        'name' => 'Sol',
                        'celestial_objects' => [],
                    ],
                    [
                        'id' => 2,
                        'code' => 'PYRO',
                        'name' => 'Pyro',
                        'celestial_objects' => [],
                    ],
                ],
            ],
            'tunnels' => [
                'resultset' => [
                    [
                        'id' => 10,
                        'entry_id' => 1,
                        'exit_id' => 2,
                        'direction' => 'bidirectional',
                        'name' => 'Sol-Pyro',
                        'size' => 'medium',
                        'entry' => ['status' => 'open'],
                        'exit' => ['status' => 'open'],
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('starmap')->put(
        now()->format('Y-m-d').'/bootup.json',
        json_encode($bootupPayload, JSON_THROW_ON_ERROR)
    );

    Storage::disk('starmap')->put(
        now()->format('Y-m-d').'/sol_system.json',
        json_encode(['name' => 'Sol'], JSON_THROW_ON_ERROR)
    );

    Http::fake();

    $job = new SyncStarmap;
    $job->handle(new RsiDownloadClient);

    Http::assertNothingSent();

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 1
            && $batch->jobs->every(fn ($job) => $job instanceof DownloadStarsystem);
    });
});
