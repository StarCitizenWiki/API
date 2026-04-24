<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichImages;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('dispatches image import batches for items and vehicles', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Gladius',
    ]);

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'display_name' => 'Avenger Titan',
    ]);

    Bus::fake();

    $this->artisan('game:import-images')
        ->assertSuccessful()
        ->expectsOutputToContain('Importing images for version');

    Bus::assertBatchCount(2);
});

it('fails when no default game version exists', function (): void {
    GameVersion::factory()->create(['is_default' => false]);

    Bus::fake();

    $this->artisan('game:import-images')
        ->assertFailed()
        ->expectsOutput('No default game version found.');

    Bus::assertNothingBatched();
});

it('dispatches item enrichment jobs with correct idNameMap and idUuidMap', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Test Item',
    ]);

    Bus::fake();

    $this->artisan('game:import-images --chunk=500')
        ->assertSuccessful();

    Bus::assertBatched(function ($batch) use ($item): bool {
        $itemJob = $batch->jobs->first();

        if (! ($itemJob instanceof EnrichImages)) {
            return false;
        }

        $reflection = new ReflectionProperty($itemJob, 'modelClass');
        $modelClass = $reflection->getValue($itemJob);

        $idMapReflection = new ReflectionProperty($itemJob, 'idNameMap');
        $idNameMap = $idMapReflection->getValue($itemJob);

        $uuidMapReflection = new ReflectionProperty($itemJob, 'idUuidMap');
        $idUuidMap = $uuidMapReflection->getValue($itemJob);

        return $modelClass === Item::class
            && count($idNameMap) === 1
            && isset($idUuidMap[$item->id])
            && $idUuidMap[$item->id] === $item->uuid;
    });
});

it('skips items when no items exist', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'display_name' => 'Test Vehicle',
    ]);

    Bus::fake();

    $this->artisan('game:import-images')
        ->assertSuccessful()
        ->expectsOutput('No items found for image import.');

    Bus::assertBatchCount(1);
});

it('skips vehicles when no vehicles exist', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Test Item',
    ]);

    Bus::fake();

    $this->artisan('game:import-images')
        ->assertSuccessful()
        ->expectsOutput('No vehicles found for image import.');

    Bus::assertBatchCount(1);
});

it('chunks jobs correctly', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $items = Item::factory()->count(5)->create();
    foreach ($items as $item) {
        ItemData::factory()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'name' => "Item {$item->id}",
        ]);
    }

    Bus::fake();

    $this->artisan('game:import-images --chunk=2')
        ->assertSuccessful();

    Bus::assertBatched(function ($batch): bool {
        return $batch->jobs->count() === 3; // ceil(5/2) = 3
    });
});
