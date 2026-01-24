<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('port display renders collapsible details elements', function () {
    $version = GameVersion::factory()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    $vehicle = Vehicle::factory()->create([
        'uuid' => '7e3b7dd7-7a1b-4dcf-8d7b-7b7bcb3f8b76',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'shipmatrix_id' => null,
            'name' => 'Test Vehicle',
            'display_name' => 'Test Vehicle',
            'class_name' => 'TEST_VEHICLE',
            'career' => 'Exploration',
            'role' => 'Scout',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'size' => 3,
            'data' => [
                'Length' => 20,
                'Width' => 10,
                'Height' => 5,
                'Mass' => 10000,
                'Loadout' => [
                    [
                        'HardpointName' => 'S1',
                        'Type' => 'Weapon.Turret',
                        'MinSize' => 1,
                        'MaxSize' => 1,
                        'Loadout' => [
                            [
                                'HardpointName' => 'S1-1',
                                'Type' => 'Weapon.Gun',
                                'MinSize' => 1,
                                'MaxSize' => 1,
                                'Loadout' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $response->assertStatus(200);
    $response->assertViewIs('vehicles.show');

    $content = $response->getContent();

    expect($content)->toContain('<details');
    expect($content)->toContain('collapse collapse-arrow');
    expect($content)->toContain('collapse-title');
    expect($content)->toContain('collapse-content');
    expect($content)->toContain('aria-expanded="false"');
    expect($content)->toContain('summary');
});

test('port display has unique IDs for details elements', function () {
    $version = GameVersion::factory()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Aegis Dynamics',
        'code' => 'AEGS',
    ]);

    $vehicle = Vehicle::factory()->create([
        'uuid' => '7e3b7dd7-7a1b-4dcf-8d7b-7b7bcb3f8b77',
    ]);

    VehicleData::factory()
        ->for($vehicle)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'shipmatrix_id' => null,
            'name' => 'Test Vehicle',
            'display_name' => 'Test Vehicle',
            'class_name' => 'TEST_VEHICLE',
            'career' => 'Exploration',
            'role' => 'Scout',
            'is_vehicle' => true,
            'is_gravlev' => false,
            'is_spaceship' => true,
            'size' => 3,
            'data' => [
                'Length' => 20,
                'Width' => 10,
                'Height' => 5,
                'Mass' => 10000,
                'Loadout' => [
                    [
                        'HardpointName' => 'S1',
                        'Type' => 'Weapon.Turret',
                        'MinSize' => 1,
                        'MaxSize' => 1,
                        'Loadout' => [
                            [
                                'HardpointName' => 'S1-1',
                                'Type' => 'Weapon.Gun',
                                'MinSize' => 1,
                                'MaxSize' => 1,
                                'Loadout' => [],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->get(route('web.vehicles.show', $vehicle->uuid));

    $content = $response->getContent();

    expect($content)->toContain('id="port-0-0"');
    expect($content)->toContain('aria-controls="port-0-0-content"');
    expect($content)->toContain('id="port-0-0-content"');
});
