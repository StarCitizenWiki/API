<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeItemBaseIds;
use App\Jobs\Game\ImportItemData;
use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('skips compute base ids and backfill shipmatrix when flags are set', function (): void {
    Storage::fake('scunpacked');
    Storage::disk('scunpacked')->put('manufacturers.json', json_encode([
        ['reference' => fake()->uuid(), 'name' => 'ACME', 'code' => 'AC'],
    ], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('tags.json', json_encode([
        fake()->uuid() => 'Tag 1',
    ], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('items/item-1.json', json_encode(['Item' => ['reference' => fake()->uuid()]], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('ships/ship-1.json', json_encode(['UUID' => fake()->uuid(), 'Manufacturer' => ['UUID' => fake()->uuid()]], JSON_THROW_ON_ERROR));

    GameVersion::query()->create([
        'code' => '3.23.0',
        'channel' => 'PTU',
        'released_at' => now(),
        'is_default' => true,
    ]);

    Bus::fake();

    Artisan::call('game:sync-data', [
        '--game-version' => '3.23.0',
        '--skip-compute-item-base-ids' => true,
        '--skip-backfill-shipmatrix-ids' => true,
    ]);

    Bus::assertBatched(function ($batch): bool {
        return $batch->jobs->every(fn ($job) => $job instanceof ImportItemData)
            && $batch->thenCallbacks() === [];
    });

    Bus::assertBatched(function ($batch): bool {
        return $batch->jobs->every(fn ($job) => $job instanceof ImportVehicleData)
            && $batch->thenCallbacks() === [];
    });

    Bus::assertNotDispatched(ComputeItemBaseIds::class);
});
