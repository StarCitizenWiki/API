<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\GameVersionAlias;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

it('resolves default game version when no version parameter is provided', function (): void {
    $olderVersion = GameVersion::factory()->create([
        'code' => '3.20.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDay(),
    ]);

    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Version Labs',
        'code' => 'VER',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($olderVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Old Payload',
            'class_name' => 'old_payload',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($item)
        ->for($defaultVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Default Payload',
            'class_name' => 'default_payload',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.version', '3.21.0-LIVE')
        ->assertJsonPath('data.name', 'Default Payload')
        ->assertJsonPath('data.class_name', 'default_payload');
});

it('resolves specific game versions from the version query parameter', function (): void {
    $oldVersion = GameVersion::factory()->create([
        'code' => '3.20.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDays(7),
    ]);

    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now()->subDay(),
    ]);

    $ptuVersion = GameVersion::factory()->create([
        'code' => '3.21.0-PTU',
        'channel' => 'ptu',
        'is_default' => false,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Version Labs',
        'code' => 'VER',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($oldVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Legacy Variant',
            'class_name' => 'legacy_variant',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($item)
        ->for($defaultVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Live Variant',
            'class_name' => 'live_variant',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($item)
        ->for($ptuVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'PTU Variant',
            'class_name' => 'ptu_variant',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $oldVersionResponse = $this->getJson("/api/items/{$item->uuid}?version=3.20.0-LIVE");

    $oldVersionResponse->assertSuccessful()
        ->assertJsonPath('data.version', '3.20.0-LIVE')
        ->assertJsonPath('data.name', 'Legacy Variant')
        ->assertJsonPath('data.class_name', 'legacy_variant');

    $ptuVersionResponse = $this->getJson("/api/items/{$item->uuid}?version=3.21.0-PTU");

    $ptuVersionResponse->assertSuccessful()
        ->assertJsonPath('data.version', '3.21.0-PTU')
        ->assertJsonPath('data.name', 'PTU Variant')
        ->assertJsonPath('data.class_name', 'ptu_variant');
});

it('resolves game version aliases to their target version', function (): void {
    $targetVersion = GameVersion::factory()->create([
        'code' => '4.8.1-LIVE.11882409',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    GameVersionAlias::query()->create([
        'code' => '4.8.0-LIVE.11825000',
        'game_version_id' => $targetVersion->id,
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Alias Labs',
        'code' => 'ALS',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($targetVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Resolved Payload',
            'class_name' => 'resolved_payload',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}?version=4.8.0-live.11825000");

    $response->assertSuccessful()
        ->assertJsonPath('data.version', '4.8.1-LIVE.11882409')
        ->assertJsonPath('data.name', 'Resolved Payload')
        ->assertJsonPath('data.class_name', 'resolved_payload');
});

it('resolves crafting blueprints for the requested game version', function (): void {
    $defaultVersion = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now()->subDay(),
    ]);

    $ptuVersion = GameVersion::factory()->create([
        'code' => '3.21.0-PTU',
        'channel' => 'ptu',
        'is_default' => false,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Version Labs',
        'code' => 'VER',
    ]);

    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($defaultVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Live Crafted Item',
            'class_name' => 'live_crafted_item',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'is_craftable' => true,
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($item)
        ->for($ptuVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'PTU Crafted Item',
            'class_name' => 'ptu_crafted_item',
            'type' => 'Widget',
            'classification' => 'Test.Widget',
            'is_craftable' => true,
            'data' => ['stdItem' => []],
        ]);

    $defaultBlueprint = Blueprint::factory()->create();
    $ptuBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($defaultBlueprint, 'blueprint')
        ->for($defaultVersion, 'gameVersion')
        ->create([
            'key' => 'BP_LIVE_CRAFTED_ITEM',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Live Crafted Blueprint',
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Live Crafted Blueprint',
                    'class' => 'bp_live_crafted_item',
                ],
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for($ptuBlueprint, 'blueprint')
        ->for($ptuVersion, 'gameVersion')
        ->create([
            'key' => 'BP_PTU_CRAFTED_ITEM',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'PTU Crafted Blueprint',
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'PTU Crafted Blueprint',
                    'class' => 'bp_ptu_crafted_item',
                ],
                'tiers' => [],
            ],
        ]);

    $defaultResponse = $this->getJson("/api/items/{$item->uuid}");
    $ptuResponse = $this->getJson("/api/items/{$item->uuid}?version=3.21.0-PTU");

    $defaultResponse->assertSuccessful()
        ->assertJsonPath('data.version', '3.21.0-LIVE')
        ->assertJsonPath('data.is_craftable', true)
        ->assertJsonPath('data.blueprint.0.uuid', $defaultBlueprint->uuid)
        ->assertJsonPath('data.blueprint.0.link', route('blueprints.show', ['blueprint' => $defaultBlueprint->uuid]));

    $ptuResponse->assertSuccessful()
        ->assertJsonPath('data.version', '3.21.0-PTU')
        ->assertJsonPath('data.is_craftable', true)
        ->assertJsonPath('data.blueprint.0.uuid', $ptuBlueprint->uuid)
        ->assertJsonPath(
            'data.blueprint.0.link',
            route('blueprints.show', ['blueprint' => $ptuBlueprint->uuid, 'version' => '3.21.0-PTU'])
        );
});

it('resolves equipped items using the requested game version', function (): void {
    $liveVersion = GameVersion::factory()->create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now()->subDay(),
    ]);

    $ptuVersion = GameVersion::factory()->create([
        'code' => '3.21.0-PTU',
        'channel' => 'ptu',
        'is_default' => false,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Version Labs',
        'code' => 'VER',
    ]);

    $weaponUuid = '00000000-0000-4000-8000-000000000001';

    $mainItem = Item::factory()->create();

    ItemData::factory()
        ->for($mainItem)
        ->for($liveVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Main Frame Live',
            'class_name' => 'main_frame_live',
            'type' => 'WeaponMount',
            'classification' => 'Ship.WeaponMount',
            'data' => [
                'stdItem' => [
                    'Ports' => [
                        [
                            'PortName' => 'PrimaryWeapon',
                            'EquippedItem' => $weaponUuid,
                        ],
                    ],
                ],
            ],
        ]);

    ItemData::factory()
        ->for($mainItem)
        ->for($ptuVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Main Frame PTU',
            'class_name' => 'main_frame_ptu',
            'type' => 'WeaponMount',
            'classification' => 'Ship.WeaponMount',
            'data' => [
                'stdItem' => [
                    'Ports' => [
                        [
                            'PortName' => 'PrimaryWeapon',
                            'EquippedItem' => $weaponUuid,
                        ],
                    ],
                ],
            ],
        ]);

    $weapon = Item::factory()->create([
        'uuid' => $weaponUuid,
    ]);

    ItemData::factory()
        ->for($weapon)
        ->for($liveVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Laser Cannon Mk I',
            'class_name' => 'laser_cannon_mk_1',
            'type' => 'WeaponGun',
            'classification' => 'Ship.Weapon.Gun',
            'data' => [
                'stdItem' => [
                    'Weapon' => [
                        'Damage' => [
                            'AlphaTotal' => 100,
                        ],
                    ],
                ],
            ],
        ]);

    ItemData::factory()
        ->for($weapon)
        ->for($ptuVersion, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Laser Cannon Mk II',
            'class_name' => 'laser_cannon_mk_2',
            'type' => 'WeaponGun',
            'classification' => 'Ship.Weapon.Gun',
            'data' => [
                'stdItem' => [
                    'Weapon' => [
                        'Damage' => [
                            'AlphaTotal' => 200,
                        ],
                    ],
                ],
            ],
        ]);

    $liveResponse = $this->getJson("/api/items/{$mainItem->uuid}?version=3.21.0-LIVE");

    $liveResponse->assertSuccessful()
        ->assertJsonPath('data.version', '3.21.0-LIVE')
        ->assertJsonPath('data.ports.0.equipped_item.uuid', $weaponUuid)
        ->assertJsonPath('data.ports.0.equipped_item.name', 'Laser Cannon Mk I')
        ->assertJsonPath('data.ports.0.equipped_item.version', '3.21.0-LIVE');

    $ptuResponse = $this->getJson("/api/items/{$mainItem->uuid}?version=3.21.0-PTU");

    $ptuResponse->assertSuccessful()
        ->assertJsonPath('data.version', '3.21.0-PTU')
        ->assertJsonPath('data.ports.0.equipped_item.uuid', $weaponUuid)
        ->assertJsonPath('data.ports.0.equipped_item.name', 'Laser Cannon Mk II')
        ->assertJsonPath('data.ports.0.equipped_item.version', '3.21.0-PTU');
});
