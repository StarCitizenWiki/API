<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameLabel;
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
use App\Services\Game\VehicleMatchingService;
use App\Services\Parser\SC\Labels;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    VehicleMatchingService::resetState();
    Labels::flushCache();
    Cache::flush();
});

it('fails when the game version does not exist', function (): void {
    assertFailsOnMissingVersion('game:import-vehicles');
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

    Storage::disk('scunpacked')->put('ships/alpha.json', json_encode(['UUID' => fake()->uuid()], JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('ships/alpha-raw.json', '{}');
    Storage::disk('scunpacked')->put('ships/beta.json', json_encode(['UUID' => fake()->uuid()], JSON_THROW_ON_ERROR));

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

    $manufacturerUuid = fake()->uuid();
    $manufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);

    $vehicleUuid = fake()->uuid();
    $payload = [
        'UUID' => $vehicleUuid,
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

    expect($data)->not->toBeNull()
        ->and($data->class_name)->toBe($payload['ClassName'])
        ->and(data_get($data->data, 'Mass'))->toBe($payload['Mass'])
        ->and(data_get($data->data, 'Quantum.QuantumSpeed'))->toEqual($payload['Quantum']['QuantumSpeed'])
        ->and(data_get($data->data, 'Fuel.Usage.Main'))->toBe($payload['Fuel']['Usage']['Main'])
        ->and($data->manufacturer_id)->toBe($manufacturer->id);

    // Re-run with updated payload to verify upsert
    $payload['Mass'] = 999;
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();

    $data = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect(data_get($data->data, 'Mass'))->toBe(999)
        ->and(VehicleData::query()->where('vehicle_id', $vehicle->id)->where('game_version_id', $version->id)->count())->toBe(1);
});

it('matches shipmatrix vehicle using override name and manufacturer code', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.23.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    $gameManufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Aegis Dynamics',
        'code' => 'AEG',
    ]);

    $shipmatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 99,
        'name' => 'Aegis Dynamics',
        'name_short' => 'AEG',
    ]);

    $productionStatus = ProductionStatus::query()->create(['slug' => 'in-production']);
    $productionNote = ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
    ]);
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

    $vehicleUuid = fake()->uuid();
    $payload = [
        'UUID' => $vehicleUuid,
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

    $vehicle = Vehicle::query()->firstWhere('uuid', $vehicleUuid);
    expect($vehicle)->not->toBeNull();

    $data = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull()
        ->and($data->shipmatrix_id)->toBe($shipmatrixVehicle->id);
});

it('matches shipmatrix vehicle by stripping manufacturer prefix', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.23.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    $gameManufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Anvil Aerospace',
        'code' => 'ANV',
    ]);

    $shipmatrixManufacturer = ShipMatrixManufacturer::query()->create([
        'cig_id' => 101,
        'name' => 'Anvil Aerospace',
        'name_short' => 'ANV',
    ]);

    $productionStatus = ProductionStatus::query()->create(['slug' => 'concept']);
    $productionNote = ProductionNote::query()->create([
        'translation' => ['en' => 'None'],
    ]);
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

    $vehicleUuid = fake()->uuid();
    $payload = [
        'UUID' => $vehicleUuid,
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

    $vehicle = Vehicle::query()->firstWhere('uuid', $vehicleUuid);
    expect($vehicle)->not->toBeNull();

    $data = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data)->not->toBeNull()
        ->and($data->shipmatrix_id)->toBe($shipmatrixVehicle->id);
});

it('generates display_name by stripping manufacturer prefix', function (string $mfgName, string $mfgCode, string $vehicleName, string $expectedDisplayName): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.24.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $mfgUuid = fake()->uuid();
    Manufacturer::query()->create([
        'uuid' => $mfgUuid,
        'name' => $mfgName,
        'code' => $mfgCode,
    ]);

    $vehicleUuid = fake()->uuid();
    $payload = [
        'UUID' => $vehicleUuid,
        'ClassName' => 'TEST_SHIP',
        'Name' => $vehicleName,
        'Manufacturer' => [
            'UUID' => $mfgUuid,
            'Name' => $mfgName,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/display_name.json', json_encode($payload, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/display_name.json'))->handle();

    $data = VehicleData::query()
        ->where('vehicle_id', Vehicle::query()->firstWhere('uuid', $vehicleUuid)->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data->name)->toBe($vehicleName)
        ->and($data->display_name)->toBe($expectedDisplayName);
})->with([
    'RSI full name' => ['Roberts Space Industries', 'RSI', 'Roberts Space Industries Constellation Andromeda', 'Constellation Andromeda'],
    'RSI code prefix' => ['Roberts Space Industries', 'RSI', 'RSI Aurora', 'Aurora'],
    'no prefix match' => ['Roberts Space Industries', 'RSI', 'F8C Lightning PYAM Exec', 'F8C Lightning PYAM Exec'],
    'Aegis first word' => ['Aegis Dynamics', 'AEG', 'Aegis Avenger Stalker', 'Avenger Stalker'],
    'Anvil first word' => ['Anvil Aerospace', 'ANVL', 'Anvil Arrow', 'Arrow'],
    'Consolidated Outland (C.O.)' => ['Consolidated Outland', 'CNOU', 'C.O. Mustang Alpha', 'Mustang Alpha'],
    'MISC abbreviation' => ['Musashi Industrial & Starflight Concern', 'MIS', 'MISC Prospector', 'Prospector'],
]);

it('syncs description translations when DescriptionKey is present', function (): void {
    Storage::fake('scunpacked');

    GameLabel::factory()->create([
        'key' => 'vehicle_Desc_Test_Ship',
        'translation' => [
            'en' => 'English ship description',
            'zh' => '中文描述',
            'de' => 'Deutsche Beschreibung',
        ],
    ]);

    $version = GameVersion::query()->create([
        'code' => '3.24.1',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);

    $vehicleUuid = fake()->uuid();
    $payload = [
        'UUID' => $vehicleUuid,
        'ClassName' => 'TEST_SHIP',
        'Name' => 'Test Ship',
        'DescriptionKey' => 'vehicle_Desc_Test_Ship',
        'DescriptionText' => 'English ship description',
        'Career' => 'Combat',
        'Role' => 'Fighter',
        'IsVehicle' => false,
        'IsGravlev' => false,
        'IsSpaceship' => true,
        'Size' => 2,
        'Manufacturer' => [
            'UUID' => $manufacturerUuid,
            'Name' => 'Test Manufacturer',
        ],
    ];

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();

    $vehicle = Vehicle::query()->firstWhere('uuid', $vehicleUuid);
    expect($vehicle)->not->toBeNull()
        ->and($vehicle->getTranslation('translation', 'en', false))->toBe('English ship description')
        ->and($vehicle->getTranslation('translation', 'zh', false))->toBe('中文描述')
        ->and($vehicle->getTranslation('translation', 'de', false))->toBe('Deutsche Beschreibung')
        ->and($vehicle->getTranslation('translation', 'fr', false))->toBeEmpty();
});

it('skips translation sync when DescriptionKey is null', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.24.2',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturerUuid = fake()->uuid();
    Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);

    $vehicleUuid = fake()->uuid();
    $payload = [
        'UUID' => $vehicleUuid,
        'ClassName' => 'TEST_SHIP_NOKEY',
        'Name' => 'No Key Ship',
        'DescriptionKey' => null,
        'Career' => 'Transport',
        'Role' => 'Hauler',
        'IsVehicle' => false,
        'IsGravlev' => false,
        'IsSpaceship' => true,
        'Size' => 3,
        'Manufacturer' => [
            'UUID' => $manufacturerUuid,
            'Name' => 'Test Manufacturer',
        ],
    ];

    Storage::disk('scunpacked')->put('ships/nokey.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/nokey.json'))->handle();

    $vehicle = Vehicle::query()->firstWhere('uuid', $vehicleUuid);
    expect($vehicle)->not->toBeNull()
        ->and($vehicle->getTranslations('translation'))->toBe([]);
});
