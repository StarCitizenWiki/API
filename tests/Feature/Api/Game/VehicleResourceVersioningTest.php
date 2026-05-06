<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.4.0-TEST',
        'channel' => 'test',
        'is_default' => true,
    ]);

    $this->vehicle = Vehicle::factory()->create();

    $this->vehicleData = VehicleData::factory()
        ->for($this->vehicle)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Ship',
            'class_name' => 'TEST_Ship',
            'data' => [
                'Loadout' => [
                    [
                        'HardpointName' => 'hardpoint_weapon_left',
                        'Position' => 'left',
                        'ClassName' => 'WeaponMount_S1',
                        'MinSize' => 1,
                        'MaxSize' => 1,
                        'Type' => 'WeaponGun.Gun',
                        'ItemTypes' => [
                            ['Type' => 'WeaponGun', 'SubType' => 'Gun'],
                        ],
                        'Editable' => true,
                        'EditableChildren' => false,
                    ],
                ],
            ],
        ]);
});

it('returns v2 hardpoint format when accessing api/v2/vehicles endpoint', function (): void {
    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    expect($json)->toHaveKey('hardpoints');
    $hardpoint = $json['hardpoints'][0];

    // V2 format should have min_size and max_size (NOT sizes object)
    expect($hardpoint)->toHaveKeys(['name', 'position', 'class_name', 'min_size', 'max_size', 'type', 'sub_type']);
    expect($hardpoint)->toHaveKey('min_size', 1);
    expect($hardpoint)->toHaveKey('max_size', 1);

    // V2 format should NOT have these keys
    expect($hardpoint)->not->toHaveKey('sizes');
    expect($hardpoint)->not->toHaveKey('editable');
    expect($hardpoint)->not->toHaveKey('uuid');
    expect($hardpoint)->not->toHaveKey('compatible_types');

    // V2 uses 'children' not 'ports'
    expect($hardpoint)->not->toHaveKey('ports');
});

it('returns v3 port format when accessing api/v3/vehicles endpoint', function (): void {
    $response = $this->getJson("/api/v3/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    expect($json)->toHaveKey('ports');
    $port = $json['ports'][0];

    // V3 format should have sizes object (NOT min_size/max_size directly)
    expect($port)->toHaveKey('sizes');
    expect($port['sizes'])->toHaveKeys(['min', 'max']);
    expect($port['sizes']['min'])->toBe(1);
    expect($port['sizes']['max'])->toBe(1);

    // V3 format should NOT have min_size/max_size at root level
    expect($port)->not->toHaveKey('min_size');
    expect($port)->not->toHaveKey('max_size');

    // V3 format should have these additional keys
    expect($port)->toHaveKey('editable', true);
    expect($port)->toHaveKey('compatible_types');
    expect($port)->toHaveKey('type', 'WeaponGun');
    expect($port)->toHaveKey('subtype', 'Gun');
});

it('returns cargo limits when accessing api/v2/vehicles endpoint', function (): void {
    $this->vehicleData->update([
        'data' => array_merge(collect($this->vehicleData->data)->toArray(), [
            'CargoGrids' => [
                [
                    'MinSize' => ['X' => 1.0, 'Y' => 1.0, 'Z' => 1.0],
                    'MaxSize' => ['X' => 2.0, 'Y' => 2.0, 'Z' => 2.0],
                ],
                [
                    'MinSize' => ['X' => 2.0, 'Y' => 2.0, 'Z' => 2.0],
                    'MaxSize' => ['X' => 4.0, 'Y' => 4.0, 'Z' => 4.0],
                ],
            ],
        ]),
    ]);

    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();

    expect($response->json('data.cargo_limits'))->toBe([
        'min_size' => ['x' => 1, 'y' => 1, 'z' => 1],
        'max_size' => ['x' => 4, 'y' => 4, 'z' => 4],
        'max_scu_box' => 8,
    ]);
});

it('returns cargo limits when accessing api/v3/vehicles endpoint', function (): void {
    $this->vehicleData->update([
        'data' => array_merge(collect($this->vehicleData->data)->toArray(), [
            'CargoGrids' => [
                [
                    'MinSize' => ['X' => 1.0, 'Y' => 1.0, 'Z' => 1.0],
                    'MaxSize' => ['X' => 2.0, 'Y' => 2.0, 'Z' => 2.0],
                ],
                [
                    'MinSize' => ['X' => 2.0, 'Y' => 2.0, 'Z' => 2.0],
                    'MaxSize' => ['X' => 4.0, 'Y' => 4.0, 'Z' => 4.0],
                ],
            ],
        ]),
    ]);

    $response = $this->getJson("/api/v3/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();

    expect($response->json('data.cargo_limits'))->toBe([
        'min_size' => ['x' => 1, 'y' => 1, 'z' => 1],
        'max_size' => ['x' => 4, 'y' => 4, 'z' => 4],
        'max_scu_box' => 8,
    ]);
});

it('returns v3 port format when accessing api/vehicles endpoint without version prefix', function (): void {
    $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    expect($json)->toHaveKey('ports');
    $port = $json['ports'][0];

    // Default should be v3 format with sizes object
    expect($port)->toHaveKey('sizes');
    expect($port['sizes'])->toHaveKeys(['min', 'max']);
});

it('returns nested children in v2 format', function (): void {
    $this->vehicleData->update([
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'parent_hardpoint',
                    'MinSize' => 2,
                    'MaxSize' => 2,
                    'Type' => 'Turret.',
                    'ItemTypes' => [],
                    'Loadout' => [
                        [
                            'HardpointName' => 'child_hardpoint',
                            'MinSize' => 1,
                            'MaxSize' => 1,
                            'Type' => 'WeaponGun.Gun',
                            'ItemTypes' => [],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/v2/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    $hardpoint = $json['hardpoints'][0];

    // V2 uses 'children' key
    expect($hardpoint)->toHaveKey('children');
    expect($hardpoint['children'])->toHaveCount(1);
    expect($hardpoint['children'][0])->toHaveKey('name', 'child_hardpoint');
});

it('returns nested ports in v3 format', function (): void {
    $this->vehicleData->update([
        'data' => [
            'Loadout' => [
                [
                    'HardpointName' => 'parent_port',
                    'MinSize' => 2,
                    'MaxSize' => 2,
                    'Type' => 'Turret.',
                    'ItemTypes' => [],
                    'Loadout' => [
                        [
                            'HardpointName' => 'child_port',
                            'MinSize' => 1,
                            'MaxSize' => 1,
                            'Type' => 'WeaponGun.Gun',
                            'ItemTypes' => [],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/v3/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();
    $json = $response->json('data');

    $port = $json['ports'][0];

    // V3 uses 'ports' key for nested items
    expect($port)->toHaveKey('ports');
    expect($port['ports'])->toHaveCount(1);
    expect($port['ports'][0])->toHaveKey('name', 'child_port');
});

it('includes version in api link when version is requested in vehicle show', function (): void {
    $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}?version=4.4.0-TEST");

    $response->assertSuccessful();

    expect($response->json('data.link'))->toContain('version=4.4.0-TEST');
});

it('includes version in web url when version is requested in vehicle show', function (): void {
    $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}?version=4.4.0-TEST");

    $response->assertSuccessful();

    expect($response->json('data.web_url'))->toContain('version=4.4.0-TEST');
});

it('does not include version in api link when version is not requested in vehicle show', function (): void {
    $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();

    expect($response->json('data.link'))->not->toContain('version=');
});

it('does not include version in web url when version is not requested in vehicle show', function (): void {
    $response = $this->getJson("/api/vehicles/{$this->vehicle->uuid}");

    $response->assertSuccessful();

    expect($response->json('data.web_url'))->not->toContain('version=');
});
