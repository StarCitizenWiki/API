<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->gameVersion = GameVersion::create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-compat-test',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('returns all required v2 base fields in item response', function () {
    $item = Item::create(['uuid' => 'test-item-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item',
        'type' => 'TestType',
        'sub_type' => 'TestSubType',
        'class_name' => 'test_item',
        'classification' => 'Test.Category.SubCategory',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'Mass' => 100.5,
                'Tags' => ['tag1', 'tag2'],
                'RequiredTags' => ['req1'],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'uuid',
                'name',
                'class_name',
                'classification',
                'description',
                'size',
                'mass',
                'is_base_variant',
                'description_data',
                'manufacturer',
                'type',
                'sub_type',
                'dimension',
                'tags',
                'required_tags',
                'entity_tags',
                'entity_tag_map',
                'interactions',
                // 'item_ports' is conditional - only included when ports exist
                'shops',
                'variants',
                'updated_at',
                'version',
            ],
        ])
        ->assertJsonPath('data.uuid', 'test-item-uuid')
        ->assertJsonPath('data.name', 'Test Item')
        ->assertJsonPath('data.type', 'TestType')
        ->assertJsonPath('data.sub_type', 'TestSubType')
        ->assertJsonPath('data.classification', 'Test.Category.SubCategory')
        ->assertJsonPath('data.version', '4.0.0-LIVE');
});

it('returns shield specification for shield items', function () {
    $item = Item::create(['uuid' => 'shield-item-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Shield',
        'type' => 'Shield',
        'class_name' => 'test_shield',
        'classification' => 'Ship.Shield.Standard',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'Shield' => [
                    'MaxShieldHealth' => 5000.0,
                    'MaxShieldRegen' => 100.0,
                    'DecayRatio' => 0.5,
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.type', 'Shield')
        ->assertJsonStructure([
            'data' => [
                'shield' => [
                    'max_shield_health',
                    'max_shield_regen',
                    'decay_ratio',
                ],
            ],
        ]);
});

it('returns quantum drive specification for quantum drive items', function () {
    $item = Item::create(['uuid' => 'qd-item-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Quantum Drive',
        'type' => 'QuantumDrive',
        'class_name' => 'test_qd',
        'classification' => 'Ship.QuantumDrive.Standard',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'QuantumDrive' => [
                    'QuantumFuelRequirement' => 1.5,
                    'JumpRange' => 50000000.0,
                    'DisconnectRange' => 5000.0,
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.type', 'QuantumDrive')
        ->assertJsonStructure([
            'data' => [
                'quantum_drive' => [
                    'quantum_fuel_requirement',
                    'jump_range',
                    'disconnect_range',
                ],
            ],
        ]);
});

it('returns clothing specification for clothing items', function () {
    $item = Item::create(['uuid' => 'clothing-item-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Jacket',
        'type' => 'Clothing',
        'class_name' => 'test_jacket',
        'classification' => 'FPS.Clothing.Torso',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'Clothing' => [
                    'Type' => 'Jacket',
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.classification', 'FPS.Clothing.Torso')
        ->assertJsonStructure([
            'data' => [
                'clothing',
            ],
        ]);
});

it('returns barrel_attach specification for barrel attachment items', function () {
    $item = Item::create(['uuid' => 'attachment-item-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Barrel Attachment',
        'type' => 'WeaponAttachment',
        'sub_type' => 'Barrel',
        'class_name' => 'test_barrel_attachment',
        'classification' => 'FPS.Attachment.Barrel',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'BarrelAttachment' => [
                    'Type' => 'Suppressor',
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.type', 'WeaponAttachment')
        ->assertJsonPath('data.sub_type', 'Barrel')
        ->assertJsonStructure([
            'data' => [
                'position',
                'barrel_attach',
            ],
        ]);
});

it('returns grade and class for ship items', function () {
    $item = Item::create(['uuid' => 'ship-item-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Ship Component',
        'type' => 'Shield',
        'class_name' => 'test_ship_shield',
        'classification' => 'Ship.Shield.Military',
        'grade' => 2,
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'Class' => 'Military',
                'Shield' => [
                    'MaxShieldHealth' => 10000.0,
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.classification', 'Ship.Shield.Military')
        ->assertJsonPath('data.grade', 2)
        ->assertJsonPath('data.class', 'Military');
});

it('returns turret data for turret items', function () {
    $item = Item::create(['uuid' => 'turret-item-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Turret',
        'type' => 'Turret',
        'class_name' => 'test_turret',
        'classification' => 'Ship.Turret.Standard',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'Ports' => [
                    ['PortName' => 'gun1', 'MinSize' => 1, 'MaxSize' => 3],
                    ['PortName' => 'gun2', 'MinSize' => 1, 'MaxSize' => 3],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.type', 'Turret')
        ->assertJsonStructure([
            'data' => [
                'max_mounts',
                'min_size',
                'max_size',
            ],
        ])
        ->assertJsonPath('data.max_mounts', 2);
});

it('returns manufacturer link in response', function () {
    $item = Item::create(['uuid' => 'manufacturer-test-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item',
        'type' => 'TestType',
        'class_name' => 'test_item_mfr',
        'classification' => 'Test.Category',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'manufacturer' => [
                    'uuid',
                    'name',
                    'code',
                    'link',
                ],
            ],
        ])
        ->assertJsonPath('data.manufacturer.name', 'Test Manufacturer')
        ->assertJsonPath('data.manufacturer.code', 'TEST');
});

it('returns legacy ports structure for items with ports', function () {
    $item = Item::create(['uuid' => 'ports-test-uuid']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item With Ports',
        'type' => 'TestType',
        'class_name' => 'test_item_ports',
        'classification' => 'Test.Category',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => [
            'stdItem' => [
                'Ports' => [
                    [
                        'PortName' => 'hardpoint_1',
                        'DisplayName' => 'Hardpoint 1',
                        'MinSize' => 1,
                        'MaxSize' => 3,
                        'Tags' => ['weapon'],
                    ],
                ],
            ],
        ],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'ports',
                'item_ports',
            ],
        ]);
});
