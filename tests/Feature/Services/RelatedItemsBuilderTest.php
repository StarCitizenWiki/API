<?php

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Services\RelatedItemsBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create default game version
    $this->defaultVersion = GameVersion::create([
        'code' => '3.21.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    // Create older game version
    $this->oldVersion = GameVersion::create([
        'code' => '3.20.0-LIVE',
        'channel' => 'live',
        'is_default' => false,
        'released_at' => now()->subDays(7),
    ]);

    // Create a manufacturer
    $this->manufacturer = Manufacturer::create([
        'uuid' => 'test-manufacturer-uuid',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('builds related items with default game version', function () {
    // Create base item
    $baseItem = Item::create(['uuid' => 'base-uuid']);
    ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Test Base',
        'class_name' => 'TestBase',
        'classification' => 'TestClass',
        'data' => [],
    ]);

    // Create variant
    $variantItem = Item::create(['uuid' => 'variant-uuid']);
    $variantData = ItemData::create([
        'item_id' => $variantItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Test Variant',
        'class_name' => 'TestVariant',
        'classification' => 'TestClass',
        'data' => [],
    ]);

    // Set variant relationship
    $baseData = ItemData::where('item_id', $baseItem->id)->first();
    $variantData->base_id = $baseData->id;
    $variantData->save();

    // Build related items using default version (null)
    $builder = new RelatedItemsBuilder;
    $result = $builder->build($variantItem);

    expect($result)->toHaveKeys(['set_name', 'base_item', 'variant_items', 'set_items']);
    expect($result['base_item'])->not->toBeNull();
    expect($result['base_item']['uuid'])->toBe('base-uuid');
    expect($result['base_item']['name'])->toBe('Test Base');
});

it('builds related items with specific game version', function () {
    // Create base item with data for both versions
    $baseItem = Item::create(['uuid' => 'base-uuid-2']);

    $oldBaseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->oldVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Old Base Name',
        'class_name' => 'OldBase',
        'classification' => 'TestClass',
        'data' => [],
    ]);

    $newBaseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'New Base Name',
        'class_name' => 'NewBase',
        'classification' => 'TestClass',
        'data' => [],
    ]);

    // Create variant with data for both versions
    $variantItem = Item::create(['uuid' => 'variant-uuid-2']);

    $oldVariantData = ItemData::create([
        'item_id' => $variantItem->id,
        'game_version_id' => $this->oldVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Old Variant Name',
        'class_name' => 'OldVariant',
        'classification' => 'TestClass',
        'base_id' => $oldBaseData->id,
        'data' => [],
    ]);

    $newVariantData = ItemData::create([
        'item_id' => $variantItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'New Variant Name',
        'class_name' => 'NewVariant',
        'classification' => 'TestClass',
        'base_id' => $newBaseData->id,
        'data' => [],
    ]);

    // Build related items with old version
    $builder = new RelatedItemsBuilder($this->oldVersion->code);
    $result = $builder->build($variantItem);

    expect($result['base_item']['name'])->toBe('Old Base Name');

    // Build related items with new version
    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($variantItem);

    expect($result['base_item']['name'])->toBe('New Base Name');
});

it('detects variant items for correct game version', function () {
    // Create base item
    $baseItem = Item::create(['uuid' => 'base-uuid-3']);
    $baseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Weapon Base',
        'class_name' => 'WeaponBase',
        'classification' => 'WeaponGun',
        'data' => [],
    ]);

    // Create variant 1
    $variant1 = Item::create(['uuid' => 'variant-1-uuid']);
    ItemData::create([
        'item_id' => $variant1->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Weapon Blue',
        'class_name' => 'WeaponBlue',
        'classification' => 'WeaponGun',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    // Create variant 2
    $variant2 = Item::create(['uuid' => 'variant-2-uuid']);
    ItemData::create([
        'item_id' => $variant2->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Weapon Red',
        'class_name' => 'WeaponRed',
        'classification' => 'WeaponGun',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    // Build related items from variant 1
    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($variant1);

    expect($result['base_item'])->not->toBeNull()
        ->and($result['base_item']['uuid'])->toBe('base-uuid-3')
        ->and($result['variant_items'])->toHaveCount(1)
        ->and($result['variant_items'][0]['uuid'])->toBe('variant-2-uuid')
        ->and($result['variant_items'][0]['name'])->toBe('Weapon Red');
});

it('falls back to stdItem tags for variant grouping', function () {
    $firstItem = Item::create(['uuid' => 'tag-variant-1']);
    ItemData::create([
        'item_id' => $firstItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Geist Armor Helmet',
        'class_name' => 'kap_combat_light_helmet_01_01_01',
        'classification' => 'FPS.Armor.Helmet',
        'data' => [
            'stdItem' => [
                'Tags' => ['kap_light', 'Set_01', 'Color_01', 'Helmet', 'HelmetCarryable'],
            ],
        ],
    ]);

    $secondItem = Item::create(['uuid' => 'tag-variant-2']);
    ItemData::create([
        'item_id' => $secondItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Geist Armor Helmet Rogue',
        'class_name' => 'kap_combat_light_helmet_01_01_02',
        'classification' => 'FPS.Armor.Helmet',
        'data' => [
            'stdItem' => [
                'Tags' => ['kap_light', 'Set_01', 'Color_02', 'Helmet', 'HelmetCarryable'],
            ],
        ],
    ]);

    $thirdItem = Item::create(['uuid' => 'tag-variant-3']);
    ItemData::create([
        'item_id' => $thirdItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Geist Armor Helmet Whiteout',
        'class_name' => 'kap_combat_light_helmet_01_01_10',
        'classification' => 'FPS.Armor.Helmet',
        'data' => [
            'stdItem' => [
                'Tags' => ['kap_light', 'Set_01', 'Color_10', 'Helmet', 'HelmetCarryable'],
            ],
        ],
    ]);

    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($firstItem);

    expect($result['base_item'])->toBeNull()
        ->and($result['set_name'])->toBe('Geist Armor Helmet')
        ->and($result['variant_items'])->toHaveCount(2);

    $variantUuids = collect($result['variant_items'])->pluck('uuid')->all();
    expect($variantUuids)->toContain('tag-variant-2', 'tag-variant-3');
});

it('finds set items filtered by game version', function () {
    // Create helmet item for default version
    $helmetItem = Item::create(['uuid' => 'helmet-uuid']);
    ItemData::create([
        'item_id' => $helmetItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Test Armor Helmet',
        'class_name' => 'char_armor_test_helmet_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    // Create core item for default version
    $coreItem = Item::create(['uuid' => 'core-uuid']);
    ItemData::create([
        'item_id' => $coreItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Test Armor Core',
        'class_name' => 'char_armor_test_core_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    // Create arms item for default version
    $armsItem = Item::create(['uuid' => 'arms-uuid']);
    ItemData::create([
        'item_id' => $armsItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Test Armor Arms',
        'class_name' => 'char_armor_test_arms_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    // Create legs item for default version
    $legsItem = Item::create(['uuid' => 'legs-uuid']);
    ItemData::create([
        'item_id' => $legsItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Test Armor Legs',
        'class_name' => 'char_armor_test_legs_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    // Build related items from helmet
    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($helmetItem);

    expect($result['set_items'])->toHaveCount(3);

    $setItemUuids = collect($result['set_items'])->pluck('uuid')->all();
    expect($setItemUuids)->toContain('core-uuid', 'arms-uuid', 'legs-uuid');
    expect($setItemUuids)->not->toContain('helmet-uuid');
});

it('filters set items by game version correctly', function () {
    // Create helmet for both versions
    $helmetItem = Item::create(['uuid' => 'helmet-uuid-versioned']);
    ItemData::create([
        'item_id' => $helmetItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'New Helmet',
        'class_name' => 'char_armor_versioned_helmet_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    ItemData::create([
        'item_id' => $helmetItem->id,
        'game_version_id' => $this->oldVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Old Helmet',
        'class_name' => 'char_armor_versioned_helmet_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    // Create core only for old version
    $coreOldItem = Item::create(['uuid' => 'core-old-uuid']);
    ItemData::create([
        'item_id' => $coreOldItem->id,
        'game_version_id' => $this->oldVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Old Core',
        'class_name' => 'char_armor_versioned_core_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    // Create core only for new version
    $coreNewItem = Item::create(['uuid' => 'core-new-uuid']);
    ItemData::create([
        'item_id' => $coreNewItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'New Core',
        'class_name' => 'char_armor_versioned_core_01',
        'classification' => 'Char_Armor',
        'data' => [],
    ]);

    // Build with old version - should find old core
    $builder = new RelatedItemsBuilder($this->oldVersion->code);
    $result = $builder->build($helmetItem);

    expect($result['set_items'])->toHaveCount(1);
    expect($result['set_items'][0]['uuid'])->toBe('core-old-uuid');
    expect($result['set_items'][0]['name'])->toBe('Old Core');

    // Build with new version - should find new core
    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($helmetItem);

    expect($result['set_items'])->toHaveCount(1);
    expect($result['set_items'][0]['uuid'])->toBe('core-new-uuid');
    expect($result['set_items'][0]['name'])->toBe('New Core');
});

it('computes correct set names for variant groups', function () {
    // Create base item
    $baseItem = Item::create(['uuid' => 'base-uuid-name']);
    $baseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Gemini A03 Sniper Rifle',
        'class_name' => 'GeminiA03',
        'classification' => 'WeaponPersonal',
        'data' => [],
    ]);

    // Create variants
    $variant1 = Item::create(['uuid' => 'variant-1-name']);
    ItemData::create([
        'item_id' => $variant1->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Gemini A03 Sniper Rifle Eclipse',
        'class_name' => 'GeminiA03Eclipse',
        'classification' => 'WeaponPersonal',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    $variant2 = Item::create(['uuid' => 'variant-2-name']);
    ItemData::create([
        'item_id' => $variant2->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Gemini A03 Sniper Rifle Pathfinder',
        'class_name' => 'GeminiA03Pathfinder',
        'classification' => 'WeaponPersonal',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    // Build from base item
    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($baseItem);

    expect($result['set_name'])->toBe('Gemini A03 Sniper')
        ->and($result['base_item']['variant_name'])->toBe('Rifle')
        ->and($result['variant_items'][0]['variant_name'])->toBeIn(['Eclipse', 'Pathfinder'])
        ->and($result['variant_items'][1]['variant_name'])->toBeIn(['Eclipse', 'Pathfinder']);
});

it('handles multi-word color variant names correctly', function () {
    // Create base item
    $baseItem = Item::create(['uuid' => 'lynx-arms-base']);
    $baseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Lynx Arms',
        'class_name' => 'LynxArms',
        'classification' => 'Armor',
        'data' => [],
    ]);

    // Create multi-word color variants
    $variant1 = Item::create(['uuid' => 'lynx-arms-dark-green']);
    ItemData::create([
        'item_id' => $variant1->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Lynx Arms Dark Green',
        'class_name' => 'LynxArmsDarkGreen',
        'classification' => 'Armor',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    $variant2 = Item::create(['uuid' => 'lynx-arms-dark-red']);
    ItemData::create([
        'item_id' => $variant2->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Lynx Arms Dark Red',
        'class_name' => 'LynxArmsDarkRed',
        'classification' => 'Armor',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    // Build from base item
    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($baseItem);

    expect($result['set_name'])->toBe('Lynx')
        ->and($result['base_item']['variant_name'])->toBe('Arms')
        ->and($result['variant_items'][0]['variant_name'])->toBeIn(['Dark Green', 'Dark Red'])
        ->and($result['variant_items'][1]['variant_name'])->toBeIn(['Dark Green', 'Dark Red']);
});

it('handles quoted variant names correctly', function () {
    // Create base item
    $baseItem = Item::create(['uuid' => 'a03-base']);
    $baseData = ItemData::create([
        'item_id' => $baseItem->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'A03 Sniper Rifle',
        'class_name' => 'A03',
        'classification' => 'WeaponPersonal',
        'data' => [],
    ]);

    // Create quoted variants
    $variant1 = Item::create(['uuid' => 'a03-scorched']);
    ItemData::create([
        'item_id' => $variant1->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'A03 "Scorched" Sniper Rifle',
        'class_name' => 'A03Scorched',
        'classification' => 'WeaponPersonal',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    $variant2 = Item::create(['uuid' => 'a03-red-alert']);
    ItemData::create([
        'item_id' => $variant2->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'A03 "Red Alert" Sniper Rifle',
        'class_name' => 'A03RedAlert',
        'classification' => 'WeaponPersonal',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    $variant3 = Item::create(['uuid' => 'a03-lodestone']);
    ItemData::create([
        'item_id' => $variant3->id,
        'game_version_id' => $this->defaultVersion->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'A03 "Lodestone" Sniper Rifle',
        'class_name' => 'A03Lodestone',
        'classification' => 'WeaponPersonal',
        'base_id' => $baseData->id,
        'data' => [],
    ]);

    // Build from base item
    $builder = new RelatedItemsBuilder($this->defaultVersion->code);
    $result = $builder->build($baseItem);

    expect($result['set_name'])->toBe('A03');
    expect($result['base_item']['variant_name'])->toBe('Sniper Rifle');

    // Extract variant names from result
    $variantNames = collect($result['variant_items'])->pluck('variant_name')->all();

    expect($variantNames)->toContain('Scorched');
    expect($variantNames)->toContain('Red Alert');
    expect($variantNames)->toContain('Lodestone');
});
