<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

it('returns full ports on vehicle alias show routes and lightweight ports on index routes', function (string $alias, string $type): void {
    $version = GameVersion::factory()->create(['is_default' => true]);
    $manufacturer = Manufacturer::factory()->create();
    $item = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'type' => 'PowerPlant',
        'data' => ['stdItem' => ['Durability' => ['Health' => 300], 'PowerConnection' => ['PowerDraw' => 500]]],
    ]);
    $attached = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $attached->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'is_player_relevant' => false,
    ]);
    $vehicle = Vehicle::factory()->create();
    VehicleData::factory()->create([
        'vehicle_id' => $vehicle->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'is_player_relevant' => true,
        $type => true,
        'data' => ['Loadout' => [[
            'HardpointName' => 'powerport',
            'Type' => 'PowerPlant.UNDEFINED',
            'UUID' => $item->uuid,
            'Loadout' => [[
                'HardpointName' => 'vehicle_attach',
                'Type' => 'NOITEM_Vehicle.Vehicle_Spaceship',
                'UUID' => $attached->uuid,
                'Loadout' => [['HardpointName' => 'nested_port']],
            ]],
        ]]],
    ]);

    $this->getJson("/api/{$alias}/{$vehicle->uuid}")
        ->assertOk()
        ->assertJsonPath('data.ports.0.health', 300)
        ->assertJsonPath('data.ports.0.equipped_item.uuid', $item->uuid)
        ->assertJsonPath('data.ports.0.equipped_item.power_plant.power_output', 500)
        ->assertJsonPath('data.ports.0.attached_vehicle.uuid', $attached->uuid)
        ->assertJsonPath('data.ports.0.ports.0.name', 'vehicle_attach')
        ->assertJsonPath('data.ports.0.ports.0.ports.0.name', 'nested_port');

    $this->getJson("/api/v2/vehicles/{$vehicle->uuid}")
        ->assertOk()
        ->assertJsonPath('data.hardpoints.0.health', 300)
        ->assertJsonPath('data.hardpoints.0.item.uuid', $item->uuid)
        ->assertJsonPath('data.hardpoints.0.children.0.name', 'vehicle_attach');

    $this->getJson("/api/{$alias}?include=ports")
        ->assertOk()
        ->assertJsonPath('data.0.ports.0.health', null)
        ->assertJsonPath('data.0.ports.0.equipped_item.uuid', $item->uuid)
        ->assertJsonPath('data.0.ports.0.ports', null)
        ->assertJsonPath('data.0.ports.0.attached_vehicle', null);
})->with([
    'ground vehicles' => ['ground-vehicles', 'is_vehicle'],
    'gravlev vehicles' => ['gravlev-vehicles', 'is_gravlev'],
]);
