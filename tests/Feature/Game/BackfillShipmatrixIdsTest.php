<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\ShipMatrix\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size as ShipSize;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type as ShipType;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Console\Command;

beforeEach(function (): void {
    // Create required reference data
    $this->productionStatus = ProductionStatus::query()->create([
        'name' => 'In Production',
        'slug' => 'in-production',
    ]);

    $this->productionNote = ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
    ]);

    $this->size = ShipSize::query()->create([
        'slug' => 'small',
        'size' => 'Small',
    ]);

    $this->type = ShipType::query()->create([
        'slug' => 'fighter',
        'type' => 'Fighter',
    ]);

    $this->shipMatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 1,
        'name' => 'Anvil Aerospace',
        'name_short' => 'ANV',
        'slug' => 'anvil-aerospace',
    ]);

    $this->gameManufacturer = Manufacturer::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Anvil Aerospace',
        'code' => 'ANV',
    ]);

    $this->gameVersion = GameVersion::query()->create([
        'code' => '3.22.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

it('backfills unmatched vehicles successfully', function (): void {
    $shipMatrix = ShipMatrixVehicle::query()->create([
        'cig_id' => 1,
        'name' => 'Hornet',
        'slug' => 'hornet',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 1,
    ]);

    $vehicle = Vehicle::query()->create(['uuid' => fake()->uuid()]);

    $gameVehicleData = VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => null,
        'class_name' => 'ANVL_Hornet',
        'name' => 'Hornet',
        'is_vehicle' => false,
        'is_gravlev' => false,
        'is_spaceship' => true,
        'data' => [],
    ]);

    $this->artisan('game:backfill-shipmatrix-ids')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Found 1 unmatched vehicles')
        ->expectsOutput('Matched: Hornet')
        ->expectsOutput('Summary: 1 matched, 0 failed');

    expect($gameVehicleData->fresh()->shipmatrix_id)->toBe($shipMatrix->id);
});

it('handles dry-run mode without modifying data', function (): void {
    ShipMatrixVehicle::query()->create([
        'cig_id' => 2,
        'name' => 'Aurora',
        'slug' => 'aurora',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 2,
    ]);

    $vehicle = Vehicle::query()->create(['uuid' => fake()->uuid()]);

    $gameVehicleData = VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => null,
        'class_name' => 'ANVL_Aurora',
        'name' => 'Aurora',
        'is_vehicle' => false,
        'is_gravlev' => false,
        'is_spaceship' => true,
        'data' => [],
    ]);

    $this->artisan('game:backfill-shipmatrix-ids --dry-run')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('DRY RUN - No changes made');

    expect($gameVehicleData->fresh()->shipmatrix_id)->toBeNull();
});

it('filters by game version', function (): void {
    $otherVersion = GameVersion::query()->create([
        'code' => '3.21.0',
        'channel' => 'live',
        'released_at' => now()->subMonth(),
        'is_default' => false,
    ]);

    $vehicle1 = Vehicle::query()->create(['uuid' => fake()->uuid()]);
    $vehicle2 = Vehicle::query()->create(['uuid' => fake()->uuid()]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle1->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => null,
        'class_name' => 'CLASS_1',
        'name' => 'Vehicle 1',
        'is_vehicle' => false,
        'is_gravlev' => false,
        'is_spaceship' => true,
        'data' => [],
    ]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle2->id,
        'game_version_id' => $otherVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => null,
        'class_name' => 'CLASS_2',
        'name' => 'Vehicle 2',
        'is_vehicle' => false,
        'is_gravlev' => false,
        'is_spaceship' => true,
        'data' => [],
    ]);

    $this->artisan('game:backfill-shipmatrix-ids', ['--game-version' => '3.22.0'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Found 1 unmatched vehicles');
});

it('limits number of records processed', function (): void {
    for ($i = 1; $i <= 5; $i++) {
        $vehicle = Vehicle::query()->create(['uuid' => fake()->uuid()]);

        VehicleData::query()->create([
            'vehicle_id' => $vehicle->id,
            'game_version_id' => $this->gameVersion->id,
            'manufacturer_id' => $this->gameManufacturer->id,
            'shipmatrix_id' => null,
            'class_name' => "CLASS_{$i}",
            'name' => "Vehicle {$i}",
            'is_vehicle' => false,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'data' => [],
        ]);
    }

    $this->artisan('game:backfill-shipmatrix-ids', ['--limit' => '3'])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Found 3 unmatched vehicles');
});

it('reports no unmatched vehicles when all have shipmatrix_id', function (): void {
    $shipMatrix = ShipMatrixVehicle::query()->create([
        'cig_id' => 3,
        'name' => 'Gladius',
        'slug' => 'gladius',
        'manufacturer_id' => $this->shipMatrixManufacturer->id,
        'production_status_id' => $this->productionStatus->id,
        'production_note_id' => $this->productionNote->id,
        'size_id' => $this->size->id,
        'type_id' => $this->type->id,
        'chassis_id' => 3,
    ]);

    $vehicle = Vehicle::query()->create(['uuid' => fake()->uuid()]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => $shipMatrix->id, // Already has shipmatrix_id
        'class_name' => 'ANVL_Gladius',
        'name' => 'Gladius',
        'is_vehicle' => false,
        'is_gravlev' => false,
        'is_spaceship' => true,
        'data' => [],
    ]);

    $this->artisan('game:backfill-shipmatrix-ids')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('No unmatched vehicles found!');
});

it('reports failed matches and suggests manual review', function (): void {
    $vehicle = Vehicle::query()->create(['uuid' => fake()->uuid()]);

    VehicleData::query()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $this->gameVersion->id,
        'manufacturer_id' => $this->gameManufacturer->id,
        'shipmatrix_id' => null,
        'class_name' => 'UNKNOWN_CLASS',
        'name' => 'NonExistent Vehicle',
        'is_vehicle' => false,
        'is_gravlev' => false,
        'is_spaceship' => true,
        'data' => [],
    ]);

    $this->artisan('game:backfill-shipmatrix-ids')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Failed: NonExistent Vehicle')
        ->expectsOutput('Summary: 0 matched, 1 failed')
        ->expectsOutput("Run 'php artisan game:review-vehicle-matches' to manually match failed vehicles");
});
