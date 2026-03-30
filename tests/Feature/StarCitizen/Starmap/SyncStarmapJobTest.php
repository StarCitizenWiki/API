<?php

declare(strict_types=1);

use App\Jobs\StarCitizen\Starmap\Download\DownloadStarsystem;
use App\Jobs\StarCitizen\Starmap\Import\ImportJumppoint;
use App\Jobs\StarCitizen\Starmap\Import\ImportStarsystem;
use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap;
use App\Services\RsiDownloadClient;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Facades\Bus;
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

    $job = new SyncStarmap;
    $job->handle(new RsiDownloadClient);

    Http::assertNothingSent();

    Bus::assertDispatched(ImportJumppoint::class, function (ImportJumppoint $job): bool {
        return $job->getData()->all() === [
            'cig_id' => 10,
            'direction' => 'bidirectional',
            'entry_id' => 1,
            'exit_id' => 2,
            'name' => 'Sol-Pyro',
            'size' => 'medium',
            'entry_status' => 'open',
            'exit_status' => 'open',
        ];
    });

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 1
            && $batch->hasJobs([
                fn (DownloadStarsystem $job): bool => $job->systemCode === 'SOL'
                    && $job->folder === now()->format('Y-m-d')
                    && $job->bootupData?->all() === [
                        'id' => 1,
                        'code' => 'SOL',
                        'name' => 'Sol',
                        'celestial_objects' => [],
                    ],
            ]);
    });

    Storage::disk('starmap')->assertExists(now()->format('Y-m-d').'/bootup.json');
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

    Http::assertSent(function ($request) {
        return $request->url() === config('services.rsi_url').'/api/starmap/bootup'
            && $request->method() === 'POST';
    });

    Bus::assertDispatched(ImportJumppoint::class, function (ImportJumppoint $job): bool {
        return $job->getData()->all() === [
            'cig_id' => 10,
            'direction' => 'bidirectional',
            'entry_id' => 1,
            'exit_id' => 2,
            'name' => 'Sol-Pyro',
            'size' => 'medium',
            'entry_status' => 'open',
            'exit_status' => 'open',
        ];
    });

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 2
            && $batch->hasJobs([
                fn (DownloadStarsystem $job): bool => $job->systemCode === 'SOL'
                    && $job->folder === now()->format('Y-m-d')
                    && $job->bootupData?->all() === [
                        'id' => 1,
                        'code' => 'SOL',
                        'name' => 'Sol',
                        'celestial_objects' => [],
                    ],
                fn (DownloadStarsystem $job): bool => $job->systemCode === 'PYRO'
                    && $job->folder === now()->format('Y-m-d')
                    && $job->bootupData?->all() === [
                        'id' => 2,
                        'code' => 'PYRO',
                        'name' => 'Pyro',
                        'celestial_objects' => [],
                    ],
            ]);
    });

    Storage::disk('starmap')->assertExists(now()->format('Y-m-d').'/bootup.json');
});

it('skips starsystem downloads and imports from disk when data already exists', function (): void {
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
        json_encode([
            'id' => 1,
            'code' => 'SOL',
            'name' => 'Sol',
            'description' => '',
            'affiliation' => [],
            'celestial_objects' => [],
        ], JSON_THROW_ON_ERROR)
    );
    Storage::disk('starmap')->put(
        now()->format('Y-m-d').'/pyro_system.json',
        json_encode([
            'id' => 2,
            'code' => 'PYRO',
            'name' => 'Pyro',
            'description' => '',
            'affiliation' => [],
            'celestial_objects' => [],
        ], JSON_THROW_ON_ERROR)
    );

    Http::fake([
        '*' => Http::response($bootupPayload, 200),
    ]);

    $job = new SyncStarmap;
    $job->handle(new RsiDownloadClient);

    Http::assertNothingSent();

    Bus::assertDispatched(ImportJumppoint::class, function (ImportJumppoint $job): bool {
        return $job->getData()->all() === [
            'cig_id' => 10,
            'direction' => 'bidirectional',
            'entry_id' => 1,
            'exit_id' => 2,
            'name' => 'Sol-Pyro',
            'size' => 'medium',
            'entry_status' => 'open',
            'exit_status' => 'open',
        ];
    });

    Bus::assertBatched(function (PendingBatch $batch): bool {
        return $batch->jobs->count() === 2
            && $batch->hasJobs([
                fn (ImportStarsystem $job): bool => $job->getData()->all() === [
                    'cig_id' => 1,
                    'code' => 'SOL',
                    'status' => null,
                    'info_url' => null,
                    'name' => 'Sol',
                    'type' => null,
                    'position_x' => null,
                    'position_y' => null,
                    'position_z' => null,
                    'frost_line' => null,
                    'habitable_zone_inner' => null,
                    'habitable_zone_outer' => null,
                    'aggregated_size' => null,
                    'aggregated_population' => null,
                    'aggregated_economy' => null,
                    'aggregated_danger' => null,
                    'time_modified' => null,
                    'description' => '',
                    'affiliation' => [],
                ],
                fn (ImportStarsystem $job): bool => $job->getData()->all() === [
                    'cig_id' => 2,
                    'code' => 'PYRO',
                    'status' => null,
                    'info_url' => null,
                    'name' => 'Pyro',
                    'type' => null,
                    'position_x' => null,
                    'position_y' => null,
                    'position_z' => null,
                    'frost_line' => null,
                    'habitable_zone_inner' => null,
                    'habitable_zone_outer' => null,
                    'aggregated_size' => null,
                    'aggregated_population' => null,
                    'aggregated_economy' => null,
                    'aggregated_danger' => null,
                    'time_modified' => null,
                    'description' => '',
                    'affiliation' => [],
                ],
            ]);
    });

    Storage::disk('starmap')->assertExists(now()->format('Y-m-d').'/bootup.json');
});
