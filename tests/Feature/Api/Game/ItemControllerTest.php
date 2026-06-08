<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\VariantGroup;
use App\Models\Game\VariantGroupItem;
use App\Models\Game\Vehicle;

beforeEach(function () {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('shows an item by uuid', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Faction Jacket Green',
            'type' => 'Clothing',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01')
        ->assertJsonPath('data.is_craftable', false)
        ->assertJsonMissingPath('data.blueprint');
});

it('treats percent characters as literal text for exact item lookups', function (): void {
    $decoy = Item::factory()->create();
    ItemData::factory()
        ->for($decoy)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'WildcardXItem',
            'type' => 'Clothing',
            'class_name' => 'wildcard_x_item',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $exact = Item::factory()->create();
    ItemData::factory()
        ->for($exact)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Wildcard%Item',
            'type' => 'Clothing',
            'class_name' => 'wildcard_percent_item',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items/'.rawurlencode('Wildcard%Item'));

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $exact->uuid)
        ->assertJsonPath('data.name', 'Wildcard%Item');
});

it('includes all crafting blueprints when an item is craftable', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Crafted Component',
            'type' => 'Widget',
            'class_name' => 'crafted_component',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $alphaBlueprint = Blueprint::factory()->create();
    $betaBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($alphaBlueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_ALPHA_COMPONENT',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Alpha Component Blueprint',
            'is_available_by_default' => false,
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Alpha Component Blueprint',
                    'class' => 'bp_alpha_component',
                ],
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for($betaBlueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_BETA_COMPONENT',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Beta Component Blueprint',
            'is_available_by_default' => false,
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Beta Component Blueprint',
                    'class' => 'bp_beta_component',
                ],
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.is_craftable', true)
        ->assertJsonCount(2, 'data.blueprint')
        ->assertJsonPath('data.blueprint.0.uuid', $alphaBlueprint->uuid)
        ->assertJsonPath('data.blueprint.0.name', 'Alpha Component Blueprint')
        ->assertJsonPath('data.blueprint.0.link', route('blueprints.show', ['blueprint' => $alphaBlueprint->uuid]))
        ->assertJsonPath('data.blueprint.1.uuid', $betaBlueprint->uuid)
        ->assertJsonPath('data.blueprint.1.name', 'Beta Component Blueprint')
        ->assertJsonPath('data.blueprint.1.link', route('blueprints.show', ['blueprint' => $betaBlueprint->uuid]));
});

it('uses recipe keys when multiple crafting blueprints share the same output name', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Karna Rifle',
            'type' => 'Weapon',
            'class_name' => 'karna_rifle',
            'classification' => 'FPS.Weapon.Rifle',
            'data' => ['stdItem' => []],
        ]);

    $alphaBlueprint = Blueprint::factory()->create();
    $betaBlueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($alphaBlueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_KARNA_RIFLE_DEFAULT',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Karna Rifle',
            'is_available_by_default' => false,
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Karna Rifle',
                    'class' => 'bp_karna_rifle_default',
                ],
                'tiers' => [],
            ],
        ]);

    BlueprintData::factory()
        ->for($betaBlueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_KARNA_RIFLE_EVENT',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Karna Rifle',
            'is_available_by_default' => false,
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Karna Rifle',
                    'class' => 'bp_karna_rifle_event',
                ],
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.is_craftable', true)
        ->assertJsonCount(2, 'data.blueprint')
        ->assertJsonPath('data.blueprint.0.uuid', $alphaBlueprint->uuid)
        ->assertJsonPath('data.blueprint.0.name', 'BP_KARNA_RIFLE_DEFAULT')
        ->assertJsonPath('data.blueprint.1.uuid', $betaBlueprint->uuid)
        ->assertJsonPath('data.blueprint.1.name', 'BP_KARNA_RIFLE_EVENT');
});

it('uses uuid-specific lookup before name or class_name fallbacks', function (): void {
    $item = Item::factory()->create();
    $decoyItem = Item::factory()->create();
    $classNameDecoy = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Primary Item',
            'type' => 'Clothing',
            'class_name' => 'primary_item',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($decoyItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => $item->uuid,
            'type' => 'Clothing',
            'class_name' => 'uuid_named_item',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($classNameDecoy)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Class Name Decoy',
            'type' => 'Clothing',
            'class_name' => $item->uuid,
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Primary Item')
        ->assertJsonPath('data.class_name', 'primary_item');
});

it('shows an item by name permutations', function (string $requestPath, string $itemClassName): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Faction Jacket Green',
            'type' => 'Clothing',
            'class_name' => $itemClassName,
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson($requestPath);

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Faction Jacket Green');
})->with([
    'exact name' => ['/api/items/Faction Jacket Green', 'cds_armor_heavy_arms_01_02_01'],
    'case-insensitive name' => ['/api/items/faction jacket green', 'cds_armor_heavy_arms_01_02_01'],
    'name with underscores converted from spaces' => ['/api/items/Faction_Jacket_Green', 'faction_jacket_green'],
]);

it('shows an item by class_name permutations', function (string $requestPath): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Heavy Armor Arms',
            'type' => 'Armor',
            'class_name' => 'cds_armor_heavy_arms_01_02_01',
            'classification' => 'FPS.Armor.Arms',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson($requestPath);

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $item->uuid)
        ->assertJsonPath('data.name', 'Heavy Armor Arms')
        ->assertJsonPath('data.class_name', 'cds_armor_heavy_arms_01_02_01');
})->with([
    'exact class_name' => ['/api/items/cds_armor_heavy_arms_01_02_01'],
    'case-insensitive class_name' => ['/api/items/CDS_ARMOR_HEAVY_ARMS_01_02_01'],
    'class_name with spaces converted to underscores' => ['/api/items/cds armor heavy arms 01 02 01'],
]);

it('does not treat wildcard characters as show route patterns', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Wildcard Decoy Item',
            'type' => 'Clothing',
            'class_name' => 'wildcard_decoy_item',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $this->getJson('/api/items/'.rawurlencode('%'))
        ->assertNotFound();
});

it('returns not found for non-existent item', function () {
    $response = $this->getJson('/api/items/non-existent-item');

    $response->assertNotFound();
});

it('redirects to the vehicle endpoint for vehicle items', function (): void {
    $item = Item::factory()->create();
    Vehicle::factory()->create(['uuid' => $item->uuid]);

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Vehicle',
            'type' => null,
            'class_name' => 'test_vehicle',
            'classification' => 'Vehicle',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertRedirect("/api/vehicles/{$item->uuid}");
});

it('excludes vehicle items from the items index', function (): void {
    $normalItem = Item::factory()->create();
    $vehicleItem = Item::factory()->create();
    Vehicle::factory()->create(['uuid' => $vehicleItem->uuid]);

    ItemData::factory()
        ->for($normalItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Normal Item',
            'type' => 'WeaponPersonal',
            'class_name' => 'normal_item',
        ]);

    ItemData::factory()
        ->for($vehicleItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Vehicle Item',
            'type' => null,
            'class_name' => 'vehicle_item',
        ]);

    $response = $this->getJson('/api/items');

    $response->assertOk();
    $response->assertJsonCount(1, 'data');
    $response->assertJsonPath('data.0.name', 'Normal Item');
});

it('includes related items when requested', function (): void {
    $baseItem = Item::factory()->create();
    $baseData = ItemData::factory()
        ->for($baseItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Base Item',
            'type' => 'Weapon',
            'class_name' => 'test_base',
            'classification' => 'WeaponPersonal',
            'data' => ['stdItem' => []],
        ]);

    $variantSiblingItem = Item::factory()->create();
    $variantSiblingData = ItemData::factory()
        ->for($variantSiblingItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Variant Item Beta',
            'type' => 'Weapon',
            'class_name' => 'test_variant_beta',
            'classification' => 'WeaponPersonal',
            'base_id' => $baseData->id,
            'data' => ['stdItem' => []],
        ]);

    $variantItem = Item::factory()->create();
    $variantData = ItemData::factory()
        ->for($variantItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Variant Item',
            'type' => 'Weapon',
            'class_name' => 'test_variant',
            'classification' => 'WeaponPersonal',
            'base_id' => $baseData->id,
            'data' => ['stdItem' => []],
        ]);

    $variantGroup = VariantGroup::query()->create([
        'game_version_id' => $this->gameVersion->id,
        'set_name' => 'Test Base',
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $baseData->id,
        'variant_name' => 'Base',
        'sort_order' => 0,
        'is_base' => true,
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $variantData->id,
        'variant_name' => 'Item',
        'sort_order' => 1,
        'is_base' => false,
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $variantSiblingData->id,
        'variant_name' => 'Item Beta',
        'sort_order' => 2,
        'is_base' => false,
    ]);

    $response = $this->getJson("/api/items/{$variantItem->uuid}?include=related_items");

    $response->assertSuccessful()
        ->assertJsonPath('data.uuid', $variantItem->uuid)
        ->assertJsonPath('data.related_items.base_item.uuid', $baseItem->uuid)
        ->assertJsonPath('data.related_items.base_item.name', 'Test Base Item')
        ->assertJsonCount(1, 'data.related_items.variant_items')
        ->assertJsonPath('data.related_items.variant_items.0.uuid', $variantSiblingItem->uuid)
        ->assertJsonPath('data.related_items.variant_items.0.name', 'Test Variant Item Beta')
        ->assertJsonCount(0, 'data.related_items.set_items');
});

it('includes variant names for items equipped in ports', function (): void {
    $parentItem = Item::factory()->create();
    $equippedItem = Item::factory()->create();

    ItemData::factory()
        ->for($parentItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Ported Storage Rack',
            'type' => 'Container',
            'class_name' => 'ported_storage_rack',
            'classification' => 'Test.Container',
            'data' => [
                'stdItem' => [
                    'Ports' => [
                        [
                            'PortName' => 'weapon_slot',
                            'DisplayName' => 'Weapon Slot',
                            'Position' => 'NOSE',
                            'MinSize' => 1,
                            'MaxSize' => 1,
                            'CompatibleTypes' => [
                                [
                                    'Type' => 'WeaponPersonal',
                                    'SubTypes' => ['Rifle'],
                                ],
                            ],
                            'EquippedItem' => $equippedItem->uuid,
                        ],
                    ],
                ],
            ],
        ]);

    $equippedItemData = ItemData::factory()
        ->for($equippedItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Shadow Rifle',
            'type' => 'WeaponPersonal',
            'sub_type' => 'Rifle',
            'class_name' => 'shadow_rifle',
            'classification' => 'FPS.WeaponPersonal',
            'data' => ['stdItem' => []],
        ]);

    $variantGroup = VariantGroup::query()->create([
        'game_version_id' => $this->gameVersion->id,
        'set_name' => 'Shadow Rifle',
    ]);

    VariantGroupItem::query()->create([
        'variant_group_id' => $variantGroup->id,
        'item_data_id' => $equippedItemData->id,
        'variant_name' => 'Shadow',
        'sort_order' => 0,
        'is_base' => false,
    ]);

    $response = $this->getJson("/api/items/{$parentItem->uuid}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'ports' => [
                    [
                        'equipped_item' => [
                            'uuid',
                            'name',
                            'variant_name',
                        ],
                    ],
                ],
            ],
        ])
        ->assertJsonPath('data.ports.0.equipped_item.uuid', $equippedItem->uuid)
        ->assertJsonPath('data.ports.0.equipped_item.variant_name', 'Shadow');
});

it('counts weapon rack slots from compatible types', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Weapon Rack',
            'type' => 'WeaponRack',
            'class_name' => 'Weapon_Rack_Test',
            'classification' => 'FPS.Utility',
            'data' => [
                'stdItem' => [
                    'Ports' => [
                        [
                            'PortName' => 'pistol_slot',
                            'MaxSize' => 1,
                            'CompatibleTypes' => [
                                [
                                    'Type' => 'WeaponPersonal',
                                    'SubTypes' => ['Pistol'],
                                ],
                            ],
                        ],
                        [
                            'PortName' => 'rifle_slot',
                            'MaxSize' => 2,
                            'CompatibleTypes' => [
                                [
                                    'Type' => 'WeaponPersonal',
                                    'SubTypes' => ['Rifle'],
                                ],
                            ],
                        ],
                        [
                            'PortName' => 'gadget_slot',
                            'MaxSize' => 1,
                            'CompatibleTypes' => [
                                [
                                    'Type' => 'WeaponPersonal',
                                    'SubTypes' => ['Gadget'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'weapon_rack' => [
                    'pistols',
                    'rifles',
                    'gadgets',
                    'total_weapon_slots',
                ],
            ],
        ])
        ->assertJsonPath('data.weapon_rack.pistols', 1)
        ->assertJsonPath('data.weapon_rack.rifles', 1)
        ->assertJsonPath('data.weapon_rack.gadgets', 1)
        ->assertJsonPath('data.weapon_rack.total_weapon_slots', 3);
});

it('does not include related items when not requested', function (): void {
    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Weapon',
            'class_name' => 'test_item',
            'classification' => 'WeaponPersonal',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonMissingPath('data.related_items');
});

it('includes web urls with version in item index', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Widget',
            'class_name' => 'test_item',
            'classification' => 'Test',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items?version=4.0.0-LIVE');

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $item->uuid);

    expect($response->json('data.0.web_url'))->toContain('version=4.0.0-LIVE');
    expect($response->json('data.0.type_web_url'))->toContain('version=4.0.0-LIVE');
});

it('includes craftability in item index results', function (): void {
    $craftableItem = Item::factory()->create();
    $nonCraftableItem = Item::factory()->create();

    ItemData::factory()
        ->for($craftableItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Crafted Item',
            'type' => 'Widget',
            'class_name' => 'alpha_crafted_item',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($nonCraftableItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Beta Non Crafted Item',
            'type' => 'Widget',
            'class_name' => 'beta_non_crafted_item',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_ALPHA_CRAFTED_ITEM',
            'output_item_uuid' => $craftableItem->uuid,
            'output_name' => 'Alpha Crafted Item Blueprint',
            'data' => [
                'output' => [
                    'uuid' => $craftableItem->uuid,
                    'name' => 'Alpha Crafted Item Blueprint',
                    'class' => 'bp_alpha_crafted_item',
                ],
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson('/api/items');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.uuid', $craftableItem->uuid)
        ->assertJsonPath('data.0.is_craftable', true)
        ->assertJsonPath('data.0.blueprint.0.uuid', $blueprint->uuid)
        ->assertJsonPath('data.1.uuid', $nonCraftableItem->uuid)
        ->assertJsonPath('data.1.is_craftable', false)
        ->assertJsonMissingPath('data.1.blueprint');
});

it('includes version in api link when version is requested in item show', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Widget',
            'class_name' => 'test_item',
            'classification' => 'Test',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}?version=4.0.0-LIVE");

    $response->assertSuccessful();

    expect($response->json('data.link'))->toContain('version=4.0.0-LIVE');
});

it('searches items with plain text queries that are not uuids', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Decari Polo',
            'type' => 'Clothing',
            'sub_type' => 'Shirt',
            'class_name' => 'decari_polo',
            'classification' => 'FPS.Clothing.Torso',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->postJson('/api/items/search', [
        'query' => 'decari po',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $item->uuid)
        ->assertJsonPath('data.0.name', 'Decari Polo');
});

it('does not include version in api link when version is not requested in item show', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Widget',
            'class_name' => 'test_item',
            'classification' => 'Test',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful();

    expect($response->json('data.link'))->not->toContain('version=');
});

it('enriches resource container default composition with commodity data', function (): void {
    $item = Item::factory()->create();

    $commodity = Commodity::factory()->create([
        'name' => 'Aphorite',
        'slug' => 'aphorite',
    ]);

    $itemData = ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Aphorite Mineable',
            'type' => 'Cargo',
            'class_name' => 'aphorite_mineable',
            'classification' => 'Cargo',
            'data' => [
                'stdItem' => [
                    'ResourceContainer' => [
                        'Capacity' => [
                            'SCU' => 0.001,
                            'Unit' => 'SMicroCargoUnit',
                            'Value' => 1000,
                            'UnitName' => 'µSCU',
                        ],
                        'Immutable' => false,
                        'DefaultComposition' => [
                            ['Entry' => $commodity->uuid, 'Weight' => 1],
                        ],
                        'DefaultFillFraction' => 1,
                    ],
                ],
            ],
        ]);

    $itemData->commodities()->attach($commodity);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.resource_container.default_composition.0.entry', $commodity->uuid)
        ->assertJsonPath('data.resource_container.default_composition.0.weight', 1)
        ->assertJsonPath('data.resource_container.default_composition.0.commodity.uuid', $commodity->uuid)
        ->assertJsonPath('data.resource_container.default_composition.0.commodity.name', 'Aphorite')
        ->assertJsonPath('data.resource_container.default_composition.0.commodity.slug', 'aphorite');

    expect($response->json('data.resource_container.default_composition.0.commodity.link'))->toContain('/api/commodities/'.$commodity->uuid);
});

it('omits commodity data when commodity is not in pivot table', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Unknown Commodity Item',
            'type' => 'Cargo',
            'class_name' => 'unknown_commodity_item',
            'classification' => 'Cargo',
            'data' => [
                'stdItem' => [
                    'ResourceContainer' => [
                        'DefaultComposition' => [
                            ['Entry' => '00000000-0000-0000-0000-000000000000', 'Weight' => 1],
                        ],
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.resource_container.default_composition.0.entry', '00000000-0000-0000-0000-000000000000')
        ->assertJsonPath('data.resource_container.default_composition.0.weight', 1);

    expect($response->json('data.resource_container.default_composition.0'))->not->toHaveKey('commodity');
});

it('returns full blueprint data when include=blueprints is requested on show route', function (): void {
    $item = Item::factory()->create();
    $commodity = Commodity::factory()->create(['name' => 'Quantainium', 'uuid' => fake()->uuid()]);

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Crafted Widget',
            'type' => 'Widget',
            'class_name' => 'crafted_widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->withIngredients($commodity)
        ->create([
            'key' => 'BP_CRAFTED_WIDGET',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Crafted Widget',
            'output_class' => 'crafted_widget',
            'craft_time_seconds' => 120,
            'is_available_by_default' => true,
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Crafted Widget',
                    'class' => 'crafted_widget',
                ],
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}?include=blueprints");

    $response->assertSuccessful()
        ->assertJsonPath('data.is_craftable', true)
        ->assertJsonPath('data.blueprint.0.uuid', $blueprint->uuid)
        ->assertJsonPath('data.blueprint.0.key', 'BP_CRAFTED_WIDGET')
        ->assertJsonPath('data.blueprint.0.output_name', 'Crafted Widget')
        ->assertJsonPath('data.blueprint.0.craft_time_seconds', 120)
        ->assertJsonPath('data.blueprint.0.is_available_by_default', true);

    expect($response->json('data.blueprint.0'))->toHaveKey('ingredients');
    expect($response->json('data.blueprint.0'))->toHaveKey('dismantle_returns');
    expect($response->json('data.blueprint.0'))->toHaveKey('output');
    expect($response->json('data.blueprint.0'))->toHaveKey('link');
    expect($response->json('data.blueprint.0'))->toHaveKey('dismantle');
    expect($response->json('data.blueprint.0'))->toHaveKey('requirement_groups');
    expect($response->json('data.blueprint.0'))->toHaveKey('summary_properties');
    expect($response->json('data.blueprint.0'))->toHaveKey('unlocking_missions');
    expect($response->json('data.blueprint.0'))->toHaveKey('tiers');
    expect($response->json('data.blueprint.0.unlocking_missions'))->toBe([]);
    expect($response->json('data.blueprint.0.tiers'))->toBe([]);
});

it('returns link-only blueprint data without include=blueprints', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Crafted Widget',
            'type' => 'Widget',
            'class_name' => 'crafted_widget',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $blueprint = Blueprint::factory()->create();

    BlueprintData::factory()
        ->for($blueprint, 'blueprint')
        ->for($this->gameVersion, 'gameVersion')
        ->create([
            'key' => 'BP_CRAFTED_WIDGET',
            'output_item_uuid' => $item->uuid,
            'output_name' => 'Crafted Widget',
            'data' => [
                'output' => [
                    'uuid' => $item->uuid,
                    'name' => 'Crafted Widget',
                ],
                'tiers' => [],
            ],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.is_craftable', true)
        ->assertJsonCount(1, 'data.blueprint')
        ->assertJsonPath('data.blueprint.0.uuid', $blueprint->uuid)
        ->assertJsonPath('data.blueprint.0.name', 'Crafted Widget')
        ->assertJsonPath('data.blueprint.0.link', route('blueprints.show', ['blueprint' => $blueprint->uuid]));

    expect($response->json('data.blueprint.0'))->not->toHaveKey('key');
    expect($response->json('data.blueprint.0'))->not->toHaveKey('ingredients');
});

it('lists blueprint as a valid include', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'Widget',
            'class_name' => 'test_item',
            'classification' => 'Test.Widget',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}?include=blueprints");

    $response->assertSuccessful();
});

describe('entity tags', function (): void {
    it('returns entity tags and entity tag map when tags are attached', function (): void {
        $tag1 = EntityTag::factory()->create(['name' => 'Tag One']);
        $tag2 = EntityTag::factory()->create(['name' => 'Tag Two']);

        $item = Item::factory()->create();

        $itemData = ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Item',
                'type' => 'TestType',
                'class_name' => 'test_item',
                'classification' => 'Test.Category',
                'data' => ['stdItem' => []],
            ]);

        $itemData->entityTags()->attach([$tag1->id, $tag2->id]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful();

        $expectedEntityTagMap = [
            [
                'uuid' => $tag1->uuid,
                'name' => 'Tag One',
            ],
            [
                'uuid' => $tag2->uuid,
                'name' => 'Tag Two',
            ],
        ];

        $expectedEntityTags = [
            $tag1->uuid,
            $tag2->uuid,
        ];

        expect(collect($response->json('data.entity_tag_map'))->sortBy('uuid')->values()->all())->toBe(
            collect($expectedEntityTagMap)->sortBy('uuid')->values()->all()
        )->and(collect($response->json('data.entity_tags'))->sort()->values()->all())->toBe(
            collect($expectedEntityTags)->sort()->values()->all()
        );
    });

    it('returns empty array when no entity tags are attached', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Item Without Tags',
                'type' => 'TestType',
                'class_name' => 'test_item_no_tags',
                'classification' => 'Test.Category',
                'data' => ['stdItem' => []],
            ]);

        $response = $this->getJson("/api/items/{$item->uuid}");

        $response->assertSuccessful()
            ->assertJsonPath('data.entity_tag_map', [])
            ->assertJsonPath('data.entity_tags', []);
    });
});

describe('rarity', function (): void {
    it('includes rarity in item response when present in stdItem', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Rare Gun',
                'type' => 'Weapon',
                'classification' => 'FPS.Weapon',
                'data' => [
                    'stdItem' => [
                        'Rarity' => 'Rare',
                    ],
                ],
                'rarity' => 'Rare',
            ]);

        $response = $this->getJson('/api/items');
        $response->assertSuccessful();

        $itemData = collect($response->json('data'))->first(fn (array $i) => $i['name'] === 'Rare Gun');

        expect($itemData)->not->toBeNull()
            ->and($itemData['rarity'])->toBe('Rare');
    });

    it('omits rarity from item response when not present in stdItem', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Plain Item',
                'type' => 'Weapon',
                'classification' => 'FPS.Weapon',
                'data' => [],
            ]);

        $response = $this->getJson('/api/items');
        $response->assertSuccessful();

        $itemData = collect($response->json('data'))->first(fn (array $i) => $i['name'] === 'Plain Item');

        expect($itemData)->not->toBeNull()
            ->and($itemData)->not->toHaveKey('rarity');
    });
});
