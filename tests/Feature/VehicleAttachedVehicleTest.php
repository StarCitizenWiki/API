<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;

describe('Docked Vehicles category', function (): void {
    it('categorizes DockingCollar with attached vehicle child as Docked Vehicles', function (): void {
        $version = GameVersion::factory()->create(['is_default' => true]);
        $manufacturer = Manufacturer::factory()->create();

        $attachedVehicle = Vehicle::factory()->create(['slug' => 'drak-command-module']);
        VehicleData::factory()->create([
            'vehicle_id' => $attachedVehicle->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $manufacturer->id,
            'class_name' => 'DRAK_Command_Module',
            'name' => 'Command Module',
            'size' => 2,
            'is_spaceship' => true,
        ]);

        $parentVehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $parentVehicle->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $manufacturer->id,
            'class_name' => 'DRAK_Caterpillar',
            'name' => 'Caterpillar',
            'data' => [
                'Loadout' => [
                    [
                        'HardpointName' => 'hardpoint_docking_module',
                        'Type' => 'DockingCollar.UNDEFINED',
                        'UUID' => 'd6c3310d-9978-48d1-b888-a934550251da',
                        'ClassName' => 'DRAK_Caterpillar_Command_Module_DockingTube',
                        'Loadout' => [
                            [
                                'HardpointName' => 'itemport_vehicle_attach',
                                'Type' => 'NOITEM_Vehicle.Vehicle_Spaceship',
                                'UUID' => $attachedVehicle->uuid,
                                'ClassName' => 'DRAK_Command_Module',
                                'ItemTypes' => [['Type' => 'NOITEM_Vehicle']],
                                'Loadout' => [
                                    ['HardpointName' => 'hardpoint_controller_fuel', 'MaxSize' => 1, 'MinSize' => 1],
                                    ['HardpointName' => 'hardpoint_fuel_port', 'MaxSize' => 1, 'MinSize' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $parentVehicle->uuid]));

        $response->assertOk()
            ->assertJsonPath('data.ports.0.category_label', 'Docked Vehicles');
    });

    it('keeps empty DockingCollar as Docking category', function (): void {
        $version = GameVersion::factory()->create(['is_default' => true]);
        $manufacturer = Manufacturer::factory()->create();

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
                        'HardpointName' => 'hardpoint_fuel_port',
                        'Type' => 'DockingCollar.UNDEFINED',
                        'UUID' => fake()->uuid(),
                        'Loadout' => [
                            [
                                'HardpointName' => 'itemport_vehicle_attach',
                                'ItemTypes' => [['Type' => 'NOITEM_Vehicle']],
                                'MaxSize' => 8,
                                'MinSize' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

        $response->assertOk()
            ->assertJsonPath('data.ports.0.category_label', 'Docking');
    });
});

describe('attached_vehicle bubbling', function (): void {
    it('bubbles attached_vehicle from child to parent and flattens ports', function (): void {
        $version = GameVersion::factory()->create(['is_default' => true]);
        $manufacturer = Manufacturer::factory()->create();

        $attachedVehicle = Vehicle::factory()->create(['slug' => 'drak-command-module']);
        VehicleData::factory()->create([
            'vehicle_id' => $attachedVehicle->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $manufacturer->id,
            'class_name' => 'DRAK_Command_Module',
            'name' => 'Command Module',
            'display_name' => 'Drake Command Module',
            'size' => 2,
            'is_spaceship' => true,
        ]);

        $parentVehicle = Vehicle::factory()->create();
        VehicleData::factory()->create([
            'vehicle_id' => $parentVehicle->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $manufacturer->id,
            'class_name' => 'DRAK_Caterpillar',
            'name' => 'Caterpillar',
            'data' => [
                'Loadout' => [
                    [
                        'HardpointName' => 'hardpoint_docking_module',
                        'Type' => 'DockingCollar.UNDEFINED',
                        'UUID' => fake()->uuid(),
                        'Loadout' => [
                            [
                                'HardpointName' => 'itemport_vehicle_attach',
                                'Type' => 'NOITEM_Vehicle.Vehicle_Spaceship',
                                'UUID' => $attachedVehicle->uuid,
                                'ClassName' => 'DRAK_Command_Module',
                                'ItemTypes' => [['Type' => 'NOITEM_Vehicle']],
                                'Loadout' => [
                                    ['HardpointName' => 'hardpoint_controller_fuel', 'MaxSize' => 1, 'MinSize' => 1],
                                    ['HardpointName' => 'hardpoint_fuel_port', 'MaxSize' => 1, 'MinSize' => 1],
                                    ['HardpointName' => 'hardpoint_relay', 'MaxSize' => 1, 'MinSize' => 1],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $parentVehicle->uuid]));

        $response->assertOk();

        $parentPort = $response->json('data.ports.0');

        // Parent has the attached vehicle directly
        expect($parentPort['attached_vehicle'])
            ->uuid->toBe($attachedVehicle->uuid)
            ->name->toBe('Drake Command Module')
            ->class_name->toBe('DRAK_Command_Module')
            ->size_class->toBe(2)
            ->is_spaceship->toBeTrue()
            ->web_url->toContain('drak-command-module');

        expect($parentPort['ports'])->not->toBeNull();
        expect($parentPort['ports'])->toHaveCount(1);

        // The NOITEM_Vehicle child also carries attached_vehicle
        $childPort = $parentPort['ports'][0];
        expect($childPort['name'])->toBe('itemport_vehicle_attach');
        expect($childPort['attached_vehicle'])
            ->uuid->toBe($attachedVehicle->uuid)
            ->name->toBe('Drake Command Module');

        // Child's own ports are preserved
        expect($childPort['ports'])->not->toBeNull();
    });

    it('does not bubble for empty vehicle docks', function (): void {
        $version = GameVersion::factory()->create(['is_default' => true]);
        $manufacturer = Manufacturer::factory()->create();

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
                        'HardpointName' => 'hardpoint_fuel_port',
                        'Type' => 'DockingCollar.UNDEFINED',
                        'UUID' => fake()->uuid(),
                        'Loadout' => [
                            [
                                'HardpointName' => 'itemport_vehicle_attach',
                                'ItemTypes' => [['Type' => 'NOITEM_Vehicle']],
                                'MaxSize' => 8,
                                'MinSize' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

        $response->assertOk();

        $parentPort = $response->json('data.ports.0');
        expect($parentPort['attached_vehicle'])->toBeNull();

        // Empty vehicle attach child is still present as a port
        $childNames = collect($parentPort['ports'] ?? [])->map(fn ($p) => $p['name'] ?? null)->filter()->all();
        expect($childNames)->toContain('itemport_vehicle_attach');
    });

    it('returns null attached_vehicle for non-vehicle ports', function (): void {
        $version = GameVersion::factory()->create(['is_default' => true]);
        $manufacturer = Manufacturer::factory()->create();

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
                        'HardpointName' => 'hardpoint_shield',
                        'Type' => 'Shield.UNDEFINED',
                        'UUID' => fake()->uuid(),
                    ],
                ],
            ],
        ]);

        $response = $this->getJson(route('vehicles.show', ['vehicle' => $vehicle->uuid]));

        $response->assertOk()
            ->assertJsonPath('data.ports.0.attached_vehicle', null);
    });
});
