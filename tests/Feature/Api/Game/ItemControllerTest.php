<?php

declare(strict_types=1);

use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\VariantGroup;
use App\Models\Game\VariantGroupItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

it('returns not found for non-existent item', function () {
    $response = $this->getJson('/api/items/non-existent-item');

    $response->assertNotFound();
});

it('redirects to the vehicle endpoint for vehicle items', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Vehicle',
            'type' => 'NOITEM_Vehicle',
            'class_name' => 'test_vehicle',
            'classification' => 'Vehicle',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertRedirect("/api/vehicles/{$item->uuid}");
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

it('does not include related items on index route even when requested', function (): void {
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

    $response = $this->getJson('/api/items?include=related_items');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $item->uuid)
        ->assertJsonPath('data.0.name', 'Test Item')
        ->assertJsonMissingPath('data.0.related_items');
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
    expect($response->json('data.blueprint.0'))->not->toHaveKey('dismantle');
    expect($response->json('data.blueprint.0'))->not->toHaveKey('requirement_groups');
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
