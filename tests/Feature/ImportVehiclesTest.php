<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\Manufacturer\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\Vehicle\Size\Size as ShipSize;
use App\Models\StarCitizen\Vehicle\Type\Type as ShipType;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('fails when the game version does not exist', function (): void {
    $this->artisan('game:import-vehicles', ['version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist. Please create it first.');
});

it('dispatches an import job for each ship file, skipping raw files', function (): void {
    Storage::fake('scunpacked');
    Queue::fake();

    $version = GameVersion::query()->create([
        'code' => '3.22.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    Storage::disk('scunpacked')->put('ships/alpha.json', json_encode(['UUID' => 'uuid-alpha'], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('ships/alpha-raw.json', '{}');
    Storage::disk('scunpacked')->put('ships/beta.json', json_encode(['UUID' => 'uuid-beta'], JSON_THROW_ON_ERROR));

    $this->artisan('game:import-vehicles', ['version' => $version->code])
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Dispatched 2 vehicle import jobs for version 3.22.0.');

    Queue::assertPushed(ImportVehicleData::class, 2);
});

it('imports vehicle data and upserts when re-run', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.22.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-manufacturer',
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);

    $payload = [
        'UUID' => 'uuid-test',
        'ClassName' => 'TEST_SHIP',
        'Name' => 'Test Ship',
        'Career' => 'Test Career',
        'Role' => 'Test Role',
        'IsVehicle' => true,
        'IsGravlev' => false,
        'IsSpaceship' => true,
        'Size' => 3,
        'Length' => 10.5,
        'Width' => 5.2,
        'Height' => 2.1,
        'Crew' => 2,
        'Mass' => 123.45,
        'Cargo' => 10,
        'PersonalInventory' => 1,
        'VehicleInventory' => 2,
        'Insurance' => [
            'StandardClaimTime' => 12.5,
            'ExpeditedClaimTime' => 5.5,
            'ExpeditedCost' => 1000,
        ],
        'FlightCharacteristics' => [
            'ScmSpeed' => 200,
            'MaxSpeed' => 800,
            'BoostSpeedForward' => 300,
            'BoostSpeedBackward' => 150,
            'ZeroToScm' => 1.5,
            'ZeroToMax' => 4.5,
            'ScmToZero' => 3.2,
            'MaxToZero' => 8.1,
            'Pitch' => 50,
            'Yaw' => 40,
            'Roll' => 120,
            'Acceleration' => [
                'Main' => 10,
                'Retro' => 9,
                'Vtol' => 8,
                'Maneuvering' => 7,
            ],
            'AccelerationG' => [
                'Main' => 1,
                'Retro' => 0.9,
                'Vtol' => 0.8,
                'Maneuvering' => 0.7,
            ],
        ],
        'Fuel' => [
            'Capacity' => 900,
            'IntakeRate' => 20,
            'Usage' => [
                'Main' => 1.1,
                'Retro' => 1.2,
                'Vtol' => 1.3,
                'Maneuvering' => 1.4,
            ],
        ],
        'Quantum' => [
            'QuantumSpeed' => 123456,
            'QuantumSpoolTime' => 4,
            'QuantumFuelCapacity' => 2.5,
            'QuantumRange' => 999999,
        ],
        'ShieldFaceType' => 'Bubble',
        'ShieldHp' => 500,
        'Health' => 1000,
        'Manufacturer' => [
            'UUID' => $manufacturer->uuid,
            'Name' => $manufacturer->name,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();

    $vehicle = Vehicle::query()->where('uuid', $payload['UUID'])->first();
    expect($vehicle)->not->toBeNull();

    $data = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull();
    expect($data->class_name)->toBe($payload['ClassName']);
    expect($data->mass)->toBe($payload['Mass']);
    expect($data->quantum_speed)->toEqual($payload['Quantum']['QuantumSpeed']);
    expect($data->fuel_usage_main)->toBe($payload['Fuel']['Usage']['Main']);

    expect($data->manufacturer_id)->toBe($manufacturer->id);

    // Re-run with updated payload to verify upsert
    $payload['Mass'] = 999;
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();

    $data->refresh();
    expect($data->mass)->toBe(999.0);
    expect(VehicleData::query()->where('vehicle_id', $vehicle->id)->where('game_version_id', $version->id)->count())->toBe(1);
});

it('fails when manufacturer uuid is missing or unknown', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.22.2',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $payload = [
        'UUID' => 'uuid-test',
        'Manufacturer' => [
            'UUID' => 'missing-uuid',
        ],
    ];

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    $job = new ImportVehicleData($version->id, 'ships/test.json');

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Manufacturer with UUID missing-uuid does not exist.');
});

it('matches shipmatrix vehicle using override name and manufacturer code', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.23.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $gameManufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-aegis',
        'name' => 'Aegis Dynamics',
        'code' => 'AEG',
    ]);

    $shipmatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 99,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEG',
    ]);

    $productionStatus = ProductionStatus::query()->create(['slug' => 'in-production']);
    $productionNote = new ProductionNote;
    $productionNote->save();
    $size = ShipSize::query()->create(['slug' => 'medium']);
    $type = ShipType::query()->create(['slug' => 'combat']);

    $shipmatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 12345,
        'name' => 'Retaliator Bomber',
        'slug' => 'retaliator-bomber',
        'manufacturer_id' => $shipmatrixManufacturer->id,
        'production_status_id' => $productionStatus->id,
        'production_note_id' => $productionNote->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'chassis_id' => 1,
    ]);

    $payload = [
        'UUID' => 'uuid-retaliator',
        'ClassName' => 'AEGS_Retaliator_Bomber',
        'Name' => 'Aegis Retaliator',
        'Manufacturer' => [
            'UUID' => $gameManufacturer->uuid,
            'Name' => $gameManufacturer->name,
            'Code' => $gameManufacturer->code,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/retaliator.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/retaliator.json'))->handle();

    $vehicle = Vehicle::query()->firstWhere('uuid', 'uuid-retaliator');
    expect($vehicle)->not->toBeNull();

    $data = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull();
    expect($data->shipmatrix_id)->toBe($shipmatrixVehicle->id);
});

it('matches shipmatrix vehicle by stripping manufacturer prefix', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.23.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $gameManufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-anvil',
        'name' => 'Anvil Aerospace',
        'code' => 'ANV',
    ]);

    $shipmatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 101,
        'name' => 'Anvil Aerospace',
        'name_short' => 'ANV',
    ]);

    $productionStatus = ProductionStatus::query()->create(['slug' => 'concept']);
    $productionNote = new ProductionNote;
    $productionNote->save();
    $size = ShipSize::query()->create(['slug' => 'small']);
    $type = ShipType::query()->create(['slug' => 'fighter']);

    $shipmatrixVehicle = ShipMatrixVehicle::query()->create([
        'cig_id' => 56789,
        'name' => 'F7C Hornet',
        'slug' => 'f7c-hornet',
        'manufacturer_id' => $shipmatrixManufacturer->id,
        'production_status_id' => $productionStatus->id,
        'production_note_id' => $productionNote->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'chassis_id' => 2,
    ]);

    $payload = [
        'UUID' => 'uuid-hornet',
        'ClassName' => 'ANVL_Hornet_F7C',
        'Name' => 'Anvil F7C Hornet',
        'Manufacturer' => [
            'UUID' => $gameManufacturer->uuid,
            'Name' => $gameManufacturer->name,
            'Code' => $gameManufacturer->code,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/hornet.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/hornet.json'))->handle();

    $vehicle = Vehicle::query()->firstWhere('uuid', 'uuid-hornet');
    expect($vehicle)->not->toBeNull();

    $data = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull();
    expect($data->shipmatrix_id)->toBe($shipmatrixVehicle->id);
});
