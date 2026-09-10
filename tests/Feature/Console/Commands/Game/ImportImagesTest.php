<?php

declare(strict_types=1);

use App\Jobs\Game\EnrichImages;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleCuratedData;
use App\Models\Game\VehicleData;
use Illuminate\Support\Facades\Bus;

it('dispatches image import batches for items, vehicles, and starmap locations', function (): void {
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

    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()->create([
        'starmap_location_id' => $location->id,
        'game_version_id' => $version->id,
        'name' => 'Crusader',
    ]);

    Commodity::factory()->create(['name' => 'Quantanium']);

    Bus::fake();

    $this->artisan('game:import-images')
        ->assertSuccessful()
        ->expectsOutputToContain('Importing images for version');

    Bus::assertBatchCount(4);
});

it('fails when no default game version exists', function (): void {
    GameVersion::factory()->create(['is_default' => false]);

    Bus::fake();

    $this->artisan('game:import-images')
        ->assertFailed()
        ->expectsOutput('No default game version found.');

    Bus::assertNothingBatched();
});

it('dispatches item enrichment jobs as EnrichImages instances', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => Item::first()->id,
        'game_version_id' => $version->id,
        'name' => 'Test Item',
    ]);

    Bus::fake();

    $this->artisan('game:import-images --chunk=500')
        ->assertSuccessful();

    Bus::assertBatched(function ($batch): bool {
        $itemJob = $batch->jobs->first();

        return $itemJob instanceof EnrichImages;
    });

    Bus::assertBatchCount(1);
});

it('skips entity types with no records and logs a warning', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    // Only items exist; vehicles, starmap locations, and commodities are absent.
    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => 'Test Item',
    ]);

    Bus::fake();

    $this->artisan('game:import-images')
        ->assertSuccessful()
        ->expectsOutput('No vehicles found for image import.')
        ->expectsOutput('No starmap locations found for image import.')
        ->expectsOutput('No commodities found for image import.');

    // Only 1 batch: items. The other 3 entity types are skipped.
    Bus::assertBatchCount(1);
});

it('prefers curated wiki page titles over display names for vehicles', function (): void {
    $version = GameVersion::factory()->create(['is_default' => true]);

    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'display_name' => 'Vanduul Scythe',
    ]);

    VehicleCuratedData::query()->create([
        'game_vehicle_id' => $vehicle->id,
        'wiki_page_title' => 'Scythe',
    ]);

    Bus::fake();

    $this->artisan('game:import-images')->assertSuccessful();

    Bus::assertBatched(function ($batch) use ($vehicle): bool {
        return $batch->jobs
            ->filter(fn ($job): bool => $job instanceof EnrichImages)
            ->contains(fn ($job): bool => $job->idNameMap === [$vehicle->id => 'Scythe']);
    });
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
