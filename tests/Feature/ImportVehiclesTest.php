<?php

declare(strict_types=1);

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\ItemDescriptionData;
use App\Models\Game\ItemTranslation;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\Manufacturer\Manufacturer as ShipMatrixManufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\Vehicle\Size\Size as ShipSize;
use App\Models\StarCitizen\Vehicle\Type\Type as ShipType;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle as ShipMatrixVehicle;
use App\Models\System\Language;
use App\Services\Parser\SC\Labels;
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
    expect(data_get($data->data, 'Mass'))->toBe($payload['Mass']);
    expect(data_get($data->data, 'Quantum.QuantumSpeed'))->toEqual($payload['Quantum']['QuantumSpeed']);
    expect(data_get($data->data, 'Fuel.Usage.Main'))->toBe($payload['Fuel']['Usage']['Main']);

    expect($data->manufacturer_id)->toBe($manufacturer->id);

    // Re-run with updated payload to verify upsert
    $payload['Mass'] = 999;
    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/test.json'))->handle();

    $data->refresh();
    expect(data_get($data->data, 'Mass'))->toBe(999);
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

it('imports vehicle item data from vehicle payload and raw data', function (): void {
    Storage::fake('scunpacked');

    $labels = new Labels(
        labelsPath: base_path('tests/Fixtures/translations/labels.json'),
        chinesePath: base_path('tests/Fixtures/translations/global_zh.ini'),
        germanPath: base_path('tests/Fixtures/translations/global_de.ini')
    );

    $version = GameVersion::query()->create([
        'code' => '3.24.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-test-manufacturer',
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);

    $payload = [
        'UUID' => 'uuid-test-vehicle',
        'ClassName' => 'TEST_Vehicle',
        'Name' => 'Test Vehicle',
        'Description' => 'English description',
        'DescriptionText' => 'English description',
        'DescriptionData' => [
            'Manufacturer' => 'Test Manufacturer',
            'Focus' => 'Test Focus',
        ],
        'Manufacturer' => [
            'UUID' => $manufacturer->uuid,
            'Name' => $manufacturer->name,
        ],
    ];

    $rawPayload = [
        'Raw' => [
            'Entity' => [
                '__ref' => $payload['UUID'],
                'ClassName' => $payload['ClassName'],
                'Components' => [
                    'SAttachableComponentParams' => [
                        'AttachDef' => [
                            'Type' => 'NOITEM_Vehicle',
                            'SubType' => 'Vehicle_Spaceship',
                            'Size' => 2,
                            'Grade' => 1,
                            'Manufacturer' => [
                                'Code' => $manufacturer->code,
                                '__ref' => $manufacturer->uuid,
                            ],
                            'Localization' => [
                                '__Description' => '@item_Desc_test',
                                'English' => [
                                    'Name' => $payload['Name'],
                                    'Description' => 'English description',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    Storage::disk('scunpacked')->put('ships/test.json', json_encode($payload, JSON_THROW_ON_ERROR));
    Storage::disk('scunpacked')->put('ships/test-raw.json', json_encode($rawPayload, JSON_THROW_ON_ERROR));

    (new ImportVehicleData($version->id, 'ships/test.json', $labels))->handle();

    $item = Item::query()->firstWhere('uuid', $payload['UUID']);
    expect($item)->not->toBeNull();

    $itemData = ItemData::query()
        ->where('item_id', $item->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($itemData)->not->toBeNull();
    expect($itemData->manufacturer_id)->toBe($manufacturer->id);
    expect($itemData->name)->toBe($payload['Name']);
    expect($itemData->class_name)->toBe($payload['ClassName']);
    expect($itemData->type)->toBe('NOITEM_Vehicle');
    expect($itemData->sub_type)->toBe('Vehicle_Spaceship');
    expect($itemData->size)->toBe(2);
    expect($itemData->grade)->toBe(1);

    $descriptionData = ItemDescriptionData::query()
        ->where('item_id', $item->id)
        ->orderBy('name')
        ->get();

    expect($descriptionData)->toHaveCount(2);
    expect($descriptionData->first()->name)->toBe('Focus');
    expect($descriptionData->first()->value)->toBe('Test Focus');
    expect($descriptionData->last()->name)->toBe('Manufacturer');
    expect($descriptionData->last()->value)->toBe('Test Manufacturer');

    $english = ItemTranslation::query()
        ->where('item_data_id', $itemData->id)
        ->where('locale_code', Language::ENGLISH)
        ->first();

    expect($english)->not->toBeNull();
    expect($english->translation)->toBe('English description');

    $chinese = ItemTranslation::query()
        ->where('item_data_id', $itemData->id)
        ->where('locale_code', Language::CHINESE)
        ->first();

    expect($chinese)->not->toBeNull();
    expect($chinese->translation)->toBe('中文描述');

    $german = ItemTranslation::query()
        ->where('item_data_id', $itemData->id)
        ->where('locale_code', Language::GERMAN)
        ->first();

    expect($german)->not->toBeNull();
    expect($german->translation)->toBe('Deutsche Beschreibung');
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

it('generates display_name by stripping manufacturer prefix', function (): void {
    Storage::fake('scunpacked');

    $version = GameVersion::query()->create([
        'code' => '3.24.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => false,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-rsi',
        'name' => 'Roberts Space Industries',
        'code' => 'RSI',
    ]);

    // Test with manufacturer name prefix
    $payload = [
        'UUID' => 'uuid-constellation',
        'ClassName' => 'RSI_Constellation_Andromeda',
        'Name' => 'Roberts Space Industries Constellation Andromeda',
        'Manufacturer' => [
            'UUID' => $manufacturer->uuid,
            'Name' => $manufacturer->name,
            // Note: Real game data does NOT include 'Code' field
        ],
    ];

    Storage::disk('scunpacked')->put('ships/constellation.json', json_encode($payload, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/constellation.json'))->handle();

    $vehicle = Vehicle::query()->firstWhere('uuid', 'uuid-constellation');
    $data = VehicleData::query()
        ->where('vehicle_id', $vehicle->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data->name)->toBe('Roberts Space Industries Constellation Andromeda');
    expect($data->display_name)->toBe('Constellation Andromeda');

    // Test with manufacturer code prefix (tests special case mapping)
    $payload2 = [
        'UUID' => 'uuid-aurora',
        'ClassName' => 'RSI_Aurora',
        'Name' => 'RSI Aurora',
        'Manufacturer' => [
            'UUID' => $manufacturer->uuid,
            'Name' => $manufacturer->name,
            // Note: Real game data does NOT include 'Code' field
        ],
    ];

    Storage::disk('scunpacked')->put('ships/aurora.json', json_encode($payload2, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/aurora.json'))->handle();

    $vehicle2 = Vehicle::query()->firstWhere('uuid', 'uuid-aurora');
    $data2 = VehicleData::query()
        ->where('vehicle_id', $vehicle2->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data2->name)->toBe('RSI Aurora');
    expect($data2->display_name)->toBe('Aurora');

    // Test without manufacturer prefix
    $payload3 = [
        'UUID' => 'uuid-no-prefix',
        'ClassName' => 'Some_Ship',
        'Name' => 'F8C Lightning PYAM Exec',
        'Manufacturer' => [
            'UUID' => $manufacturer->uuid,
            'Name' => $manufacturer->name,
            // Note: Real game data does NOT include 'Code' field
        ],
    ];

    Storage::disk('scunpacked')->put('ships/no-prefix.json', json_encode($payload3, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/no-prefix.json'))->handle();

    $vehicle3 = Vehicle::query()->firstWhere('uuid', 'uuid-no-prefix');
    $data3 = VehicleData::query()
        ->where('vehicle_id', $vehicle3->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data3->name)->toBe('F8C Lightning PYAM Exec');
    expect($data3->display_name)->toBe('F8C Lightning PYAM Exec');

    // Test Aegis manufacturer (first word extraction)
    $aegisManufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-aegis',
        'name' => 'Aegis Dynamics',
        'code' => 'AEG',
    ]);

    $payload4 = [
        'UUID' => 'uuid-avenger',
        'ClassName' => 'AEGS_Avenger_Stalker',
        'Name' => 'Aegis Avenger Stalker',
        'Manufacturer' => [
            'UUID' => $aegisManufacturer->uuid,
            'Name' => $aegisManufacturer->name,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/avenger.json', json_encode($payload4, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/avenger.json'))->handle();

    $vehicle4 = Vehicle::query()->firstWhere('uuid', 'uuid-avenger');
    $data4 = VehicleData::query()
        ->where('vehicle_id', $vehicle4->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data4->name)->toBe('Aegis Avenger Stalker');
    expect($data4->display_name)->toBe('Avenger Stalker');

    // Test Anvil manufacturer (first word extraction)
    $anvilManufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-anvil',
        'name' => 'Anvil Aerospace',
        'code' => 'ANVL',
    ]);

    $payload5 = [
        'UUID' => 'uuid-arrow',
        'ClassName' => 'ANVL_Arrow',
        'Name' => 'Anvil Arrow',
        'Manufacturer' => [
            'UUID' => $anvilManufacturer->uuid,
            'Name' => $anvilManufacturer->name,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/arrow.json', json_encode($payload5, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/arrow.json'))->handle();

    $vehicle5 = Vehicle::query()->firstWhere('uuid', 'uuid-arrow');
    $data5 = VehicleData::query()
        ->where('vehicle_id', $vehicle5->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data5->name)->toBe('Anvil Arrow');
    expect($data5->display_name)->toBe('Arrow');

    // Test Consolidated Outland manufacturer (special case: C.O.)
    $cnoManufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-cno',
        'name' => 'Consolidated Outland',
        'code' => 'CNOU',
    ]);

    $payload6 = [
        'UUID' => 'uuid-mustang',
        'ClassName' => 'CNOU_Mustang',
        'Name' => 'C.O. Mustang Alpha',
        'Manufacturer' => [
            'UUID' => $cnoManufacturer->uuid,
            'Name' => $cnoManufacturer->name,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/mustang.json', json_encode($payload6, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/mustang.json'))->handle();

    $vehicle6 = Vehicle::query()->firstWhere('uuid', 'uuid-mustang');
    $data6 = VehicleData::query()
        ->where('vehicle_id', $vehicle6->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data6->name)->toBe('C.O. Mustang Alpha');
    expect($data6->display_name)->toBe('Mustang Alpha');

    // Test MISC manufacturer (special case)
    $miscManufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-misc',
        'name' => 'Musashi Industrial & Starflight Concern',
        'code' => 'MIS',
    ]);

    $payload7 = [
        'UUID' => 'uuid-prospector',
        'ClassName' => 'MISC_Prospector',
        'Name' => 'MISC Prospector',
        'Manufacturer' => [
            'UUID' => $miscManufacturer->uuid,
            'Name' => $miscManufacturer->name,
        ],
    ];

    Storage::disk('scunpacked')->put('ships/prospector.json', json_encode($payload7, JSON_THROW_ON_ERROR));
    (new ImportVehicleData($version->id, 'ships/prospector.json'))->handle();

    $vehicle7 = Vehicle::query()->firstWhere('uuid', 'uuid-prospector');
    $data7 = VehicleData::query()
        ->where('vehicle_id', $vehicle7->id)
        ->where('game_version_id', $version->id)
        ->first();

    expect($data7->name)->toBe('MISC Prospector');
    expect($data7->display_name)->toBe('Prospector');
});
