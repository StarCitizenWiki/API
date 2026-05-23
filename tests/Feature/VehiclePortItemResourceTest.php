<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

it('returns the equipped item with specifications for the default game version', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.4.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test Power Plant',
        'class_name' => 'TEST_PowerPlant',
        'type' => 'PowerPlant',
        'classification' => 'Ship.PowerPlant',
        'size' => 1,
        'grade' => 1,
        'data' => [
            'stdItem' => [
                'PowerConnection' => [
                    'PowerDraw' => 500,
                ],
            ],
        ],
    ]);

    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'Test_Ship',
        'name' => 'Test Ship',
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'powerport',
                    'Type' => 'PowerPlant.UNDEFINED',
                    'UUID' => $item->uuid,
                ],
            ],
        ],
    ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk()
        ->assertJsonPath('data.ports.0.equipped_item.uuid', $item->uuid)
        ->assertJsonPath('data.ports.0.equipped_item.power_plant.power_output', 500);
});

it('uses the requested game version when resolving equipped items', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '4.3.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $requestedVersion = GameVersion::factory()->create([
        'code' => '4.4.0-PTU',
        'channel' => 'ptu',
        'is_default' => false,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Alt Manufacturer',
        'code' => 'ALTM',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $requestedVersion->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Alt Power Plant',
        'class_name' => 'ALT_PowerPlant',
        'type' => 'PowerPlant',
        'classification' => 'Ship.PowerPlant',
        'size' => 2,
        'grade' => 2,
        'data' => [
            'stdItem' => [
                'PowerConnection' => [
                    'PowerDraw' => 250,
                ],
            ],
        ],
    ]);

    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $requestedVersion->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'Alt_Ship',
        'name' => 'Alt Ship',
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'powerport',
                    'Type' => 'PowerPlant.UNDEFINED',
                    'UUID' => $item->uuid,
                ],
            ],
        ],
    ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]).'?version='.$requestedVersion->code);

    $response->assertOk()
        ->assertJsonPath('data.ports.0.equipped_item.version', $requestedVersion->code)
        ->assertJsonPath('data.ports.0.equipped_item.power_plant.power_output', 250);
});

it('returns null when the equipped item cannot be resolved for the version', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.4.1-LIVE',
        'channel' => 'live',
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Missing Manufacturer',
        'code' => 'MISS',
    ]);

    $vehicle = Vehicle::factory()->create();

    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'class_name' => 'Missing_Ship',
        'name' => 'Missing Ship',
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'powerport',
                    'Type' => 'PowerPlant.UNDEFINED',
                    'UUID' => fake()->uuid(),
                ],
            ],
        ],
    ]);

    $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

    $response->assertOk()
        ->assertJsonPath('data.ports.0.equipped_item', null);
});
