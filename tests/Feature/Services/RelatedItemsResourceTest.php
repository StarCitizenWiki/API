<?php

declare(strict_types=1);

use App\Http\Resources\Game\Item\RelatedItemsResource;
use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGradeLinks as ComputeItemVariantGradeLinksJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

function resolveRelatedItems(ItemData $itemData): array
{
    $reloaded = ItemData::query()
        ->with([
            'gameVersion',
            'variantGroupItem.variantGroup.items.itemData.item',
            'gradeLinks.gradeItem.item',
            'setItems.item',
        ])
        ->find($itemData->id);

    return (new RelatedItemsResource($reloaded))->resolve();
}

function computeGroupsAndSetItems(int $gameVersionId): void
{
    (new ComputeItemVariantGroupsJob($gameVersionId))->handle();
    (new ComputeItemSetItemsJob($gameVersionId))->handle();
    (new ComputeItemVariantGradeLinksJob($gameVersionId))->handle();
}

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create();
});

describe('variants', function () {
    it('finds variants from pre-computed groups', function (): void {
        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Weapon Base',
                'class_name' => 'test_weapon_01_01_01',
                'classification' => 'WeaponGun',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['weapon_family', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        $variant1 = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Weapon Blue',
                'class_name' => 'test_weapon_01_01_02',
                'classification' => 'WeaponGun',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['weapon_family', 'Set_01', 'Color_02'],
                    ],
                ],
            ]);

        $variant2 = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Weapon Red',
                'class_name' => 'test_weapon_01_01_03',
                'classification' => 'WeaponGun',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['weapon_family', 'Set_01', 'Color_03'],
                    ],
                ],
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['variant_items'])->toHaveCount(2);

        $uuids = collect($result['variant_items'])->pluck('uuid')->all();
        expect($uuids)->toContain($variant1->item->uuid, $variant2->item->uuid);
    });

    it('resolves base item when querying a variant', function (): void {
        $baseUuid = fake()->uuid();
        $baseItem = Item::factory()->create(['uuid' => $baseUuid]);

        $base = ItemData::factory()
            ->for($baseItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Base',
                'class_name' => 'test_module_01_01_01',
                'classification' => 'TestClass',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['test_family', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        $variant = ItemData::factory()
            ->for(Item::factory())
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Variant',
                'class_name' => 'test_module_01_01_02',
                'classification' => 'TestClass',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['test_family', 'Set_01', 'Color_02'],
                    ],
                ],
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($variant);

        expect($result['base_item'])->not->toBeNull()
            ->and($result['base_item']['uuid'])->toBe($baseUuid)
            ->and($result['base_item']['name'])->toBe('Test Base');
    });

    it('isolates version-specific variant data', function (): void {
        $oldVersion = GameVersion::factory()->create([
            'code' => '3.20.0-LIVE',
            'released_at' => now()->subDays(7),
        ]);

        $baseItem = Item::factory()->create();
        $variantItem = Item::factory()->create();

        $oldBase = ItemData::factory()
            ->for($baseItem)
            ->for($oldVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Old Base',
                'class_name' => 'test_module_01_01_01',
                'classification' => 'TestClass',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['old_family', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($variantItem)
            ->for($oldVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Old Variant',
                'class_name' => 'test_module_01_01_02',
                'classification' => 'TestClass',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['old_family', 'Set_01', 'Color_02'],
                    ],
                ],
            ]);

        $newBase = ItemData::factory()
            ->for($baseItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'New Base',
                'class_name' => 'test_module_01_01_01',
                'classification' => 'TestClass',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['new_family', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        $newVariant = ItemData::factory()
            ->for($variantItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'New Variant',
                'class_name' => 'test_module_01_01_02',
                'classification' => 'TestClass',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['new_family', 'Set_01', 'Color_02'],
                    ],
                ],
            ]);

        computeGroupsAndSetItems($oldVersion->id);
        computeGroupsAndSetItems($this->gameVersion->id);

        expect(resolveRelatedItems($oldBase)['base_item']['name'])->toBe('Old Base')
            ->and(resolveRelatedItems($newVariant)['base_item']['name'])->toBe('New Base');
    });
});

describe('set items', function () {
    it('finds set items from class name patterns', function (): void {
        $helmet = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Helmet',
                'class_name' => 'char_armor_test_helmet_01',
                'classification' => 'Char_Armor',
            ]);

        $core = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Core',
                'class_name' => 'char_armor_test_core_01',
                'classification' => 'Char_Armor',
            ]);

        $arms = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Arms',
                'class_name' => 'char_armor_test_arms_01',
                'classification' => 'Char_Armor',
            ]);

        $legs = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Legs',
                'class_name' => 'char_armor_test_legs_01',
                'classification' => 'Char_Armor',
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($helmet);

        expect($result['set_items'])->toHaveCount(3);
        $names = collect($result['set_items'])->pluck('name')->all();
        expect($names)->toContain('Test Armor Core', 'Test Armor Arms', 'Test Armor Legs')
            ->and($names)->not->toContain('Test Armor Helmet');
    });

    it('filters set items by game version', function (): void {
        $oldVersion = GameVersion::factory()->create([
            'code' => '3.20.0-LIVE',
            'released_at' => now()->subDays(7),
        ]);

        $helmetItem = Item::factory()->create();

        $oldHelmet = ItemData::factory()
            ->for($helmetItem)
            ->for($oldVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Old Helmet',
                'class_name' => 'char_armor_versioned_helmet_01',
                'classification' => 'Char_Armor',
            ]);

        $newHelmet = ItemData::factory()
            ->for($helmetItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'New Helmet',
                'class_name' => 'char_armor_versioned_helmet_01',
                'classification' => 'Char_Armor',
            ]);

        ItemData::factory()
            ->for(Item::factory())
            ->for($oldVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Old Core',
                'class_name' => 'char_armor_versioned_core_01',
                'classification' => 'Char_Armor',
            ]);

        ItemData::factory()
            ->for(Item::factory())
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'New Core',
                'class_name' => 'char_armor_versioned_core_01',
                'classification' => 'Char_Armor',
            ]);

        computeGroupsAndSetItems($oldVersion->id);
        computeGroupsAndSetItems($this->gameVersion->id);

        $oldResult = resolveRelatedItems($oldHelmet);
        expect($oldResult['set_items'])->toHaveCount(1)
            ->and($oldResult['set_items'][0]['name'])->toBe('Old Core');

        $newResult = resolveRelatedItems($newHelmet);
        expect($newResult['set_items'])->toHaveCount(1)
            ->and($newResult['set_items'][0]['name'])->toBe('New Core');
    });
});

describe('naming', function () {
    it('computes variant names for groups without entity tags', function (): void {
        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gemini A03 Sniper Rifle',
                'class_name' => 'gemini_a03_01_01_01',
                'classification' => 'WeaponPersonal',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['gemini_a03', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gemini A03 Sniper Rifle Eclipse',
                'class_name' => 'gemini_a03_01_01_02',
                'classification' => 'WeaponPersonal',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['gemini_a03', 'Set_01', 'Color_02'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gemini A03 Sniper Rifle Pathfinder',
                'class_name' => 'gemini_a03_01_01_03',
                'classification' => 'WeaponPersonal',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['gemini_a03', 'Set_01', 'Color_03'],
                    ],
                ],
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        // No Set-subtree entity tag exists, so set_name must be null
        expect($result['set_name'])->toBeNull()
            ->and($result['base_item']['variant_name'])->toBe('Rifle')
            ->and($result['variant_items'][0]['variant_name'])->toBeIn(['Eclipse', 'Pathfinder'])
            ->and($result['variant_items'][1]['variant_name'])->toBeIn(['Eclipse', 'Pathfinder']);
    });

    it('handles multi-word color variant names', function (): void {
        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Arms',
                'class_name' => 'lynx_arms_01_01_01',
                'classification' => 'Armor',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['lynx_armor', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Arms Dark Green',
                'class_name' => 'lynx_arms_01_01_02',
                'classification' => 'Armor',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['lynx_armor', 'Set_01', 'Color_02'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Arms Dark Red',
                'class_name' => 'lynx_arms_01_01_03',
                'classification' => 'Armor',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['lynx_armor', 'Set_01', 'Color_03'],
                    ],
                ],
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['set_name'])->toBeNull()
            ->and($result['base_item']['variant_name'])->toBe('Arms')
            ->and($result['variant_items'][0]['variant_name'])->toBeIn(['Dark Green', 'Dark Red'])
            ->and($result['variant_items'][1]['variant_name'])->toBeIn(['Dark Green', 'Dark Red']);
    });

    it('handles quoted variant names', function (): void {
        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'A03 Sniper Rifle',
                'class_name' => 'a03_rifle_01_01_01',
                'classification' => 'WeaponPersonal',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['a03_family', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'A03 "Scorched" Sniper Rifle',
                'class_name' => 'a03_rifle_01_01_02',
                'classification' => 'WeaponPersonal',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['a03_family', 'Set_01', 'Color_02'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'A03 "Red Alert" Sniper Rifle',
                'class_name' => 'a03_rifle_01_01_03',
                'classification' => 'WeaponPersonal',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['a03_family', 'Set_01', 'Color_03'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'A03 "Lodestone" Sniper Rifle',
                'class_name' => 'a03_rifle_01_01_04',
                'classification' => 'WeaponPersonal',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['a03_family', 'Set_01', 'Color_04'],
                    ],
                ],
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['set_name'])->toBeNull()
            ->and($result['base_item']['variant_name'])->toBe('Sniper Rifle');

        $variantNames = collect($result['variant_items'])->pluck('variant_name')->all();
        expect($variantNames)->toContain('Scorched', 'Red Alert', 'Lodestone');
    });

    it('groups texture variants with color variants via className prefix', function (): void {
        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Davlos Shirt Charcoal',
                'class_name' => 'mym_shirt_01_01_01',
                'classification' => 'FPS.Clothing.Shirt',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['mym_shirt', 'Set_01', 'Color_01'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Davlos Shirt Night',
                'class_name' => 'mym_shirt_01_01_12',
                'classification' => 'FPS.Clothing.Shirt',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['mym_shirt', 'Set_01', 'Color_12'],
                    ],
                ],
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Davlos Shirt Mustard',
                'class_name' => 'mym_shirt_01_01_03',
                'classification' => 'FPS.Clothing.Shirt',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['mym_shirt', 'Set_01', 'Color_03'],
                    ],
                ],
            ]);

        $sweater = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Forgiveness Sweater',
                'class_name' => 'mym_shirt_01_lum02_02',
                'classification' => 'FPS.Clothing.Shirt',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['mym_shirt', 'Set_01', 'Texture_lum02', 'Color_02'],
                    ],
                ],
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['variant_items'])->toHaveCount(3);

        $variantUuids = collect($result['variant_items'])->pluck('uuid')->all();
        expect($variantUuids)->toContain($sweater->item->uuid);
    });

    it('returns null set name when no variant group exists', function (): void {
        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Helmet HighSec',
                'class_name' => 'kap_combat_heavy_helmet_02_03_01',
                'classification' => 'Char_Armor',
            ]);

        $core = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Core HighSec',
                'class_name' => 'kap_combat_heavy_core_02_03_01',
                'classification' => 'Char_Armor',
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Arms HighSec',
                'class_name' => 'kap_combat_heavy_arms_02_03_01',
                'classification' => 'Char_Armor',
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Legs HighSec',
                'class_name' => 'kap_combat_heavy_legs_02_03_01',
                'classification' => 'Char_Armor',
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($core);

        expect($result['set_name'])->toBeNull()
            ->and($result['set_items'])->toHaveCount(3);
    });

    it('groups class name variants with numeric sub-variant fallback', function (): void {
        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Core',
                'class_name' => 'kap_combat_heavy_core_02_01_01',
                'classification' => 'Char_Armor',
            ]);

        $highsec = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Core HighSec',
                'class_name' => 'kap_combat_heavy_core_02_03_01',
                'classification' => 'Char_Armor',
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Core Hemlock Camo',
                'class_name' => 'kap_combat_heavy_core_02_04_01',
                'classification' => 'Char_Armor',
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($highsec);

        expect($result['variant_items'])->toHaveCount(1);
    });

    it('resolves set name via entity tags when manufacturer name collides', function (): void {
        // Regression: "Lynx" is both an armor set name and a manufacturer name.
        // Entity tags for Lynx items include: Light, Common, FPS, Legs, Human,
        // KastakArms, Lynx, Grey - where only "Lynx" qualifies: its parent
        // chain passes the unique 'Set' row and its name appears in the
        // member names (KastakArms passes the chain but never the name check).
        $setUuid = fake()->uuid();
        EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
        $kastakUuid = fake()->uuid();
        EntityTag::query()->create(['uuid' => $kastakUuid, 'name' => 'KastakArms', 'parent_uuid' => $setUuid]);
        $lynxUuid = fake()->uuid();
        EntityTag::query()->create(['uuid' => $lynxUuid, 'name' => 'Lynx', 'parent_uuid' => $kastakUuid]);

        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Legs',
                'class_name' => 'outlaw_legacy_armor_light_legs_01_01_01',
                'classification' => 'FPS.Armor.Light',
                'is_player_relevant' => true,
                'data' => ['entity_tag_map' => [
                    ['tag' => 'f1c9b063-0000-0000-0000-000000000001', 'name' => 'Light'],
                    ['tag' => '59ca5e36-0000-0000-0000-000000000001', 'name' => 'Common'],
                    ['tag' => 'ba75cc73-0000-0000-0000-000000000001', 'name' => 'FPS'],
                    ['tag' => '4cd7531a-0000-0000-0000-000000000001', 'name' => 'Legs'],
                    ['tag' => 'ba81fd42-0000-0000-0000-000000000001', 'name' => 'Human'],
                    ['tag' => $kastakUuid, 'name' => 'KastakArms'],
                    ['tag' => $lynxUuid, 'name' => 'Lynx'],
                    ['tag' => 'dd7bfcdd-0000-0000-0000-000000000001', 'name' => 'Grey'],
                ]],
            ]);

        $red = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Legs Red',
                'class_name' => 'outlaw_legacy_armor_light_legs_01_04_02',
                'classification' => 'FPS.Armor.Light',
                'is_player_relevant' => true,
                'data' => ['entity_tag_map' => [
                    ['tag' => 'f1c9b063-0000-0000-0000-000000000001', 'name' => 'Light'],
                    ['tag' => '59ca5e36-0000-0000-0000-000000000001', 'name' => 'Common'],
                    ['tag' => 'ba75cc73-0000-0000-0000-000000000001', 'name' => 'FPS'],
                    ['tag' => '4cd7531a-0000-0000-0000-000000000001', 'name' => 'Legs'],
                    ['tag' => 'ba81fd42-0000-0000-0000-000000000001', 'name' => 'Human'],
                    ['tag' => $kastakUuid, 'name' => 'KastakArms'],
                    ['tag' => $lynxUuid, 'name' => 'Lynx'],
                    ['tag' => 'a1d4429d-0000-0000-0000-000000000001', 'name' => 'Red'],
                ]],
            ]);

        $blue = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Legs Blue',
                'class_name' => 'outlaw_legacy_armor_light_legs_01_04_07',
                'classification' => 'FPS.Armor.Light',
                'is_player_relevant' => true,
                'data' => ['entity_tag_map' => [
                    ['tag' => 'f1c9b063-0000-0000-0000-000000000001', 'name' => 'Light'],
                    ['tag' => '59ca5e36-0000-0000-0000-000000000001', 'name' => 'Common'],
                    ['tag' => 'ba75cc73-0000-0000-0000-000000000001', 'name' => 'FPS'],
                    ['tag' => '4cd7531a-0000-0000-0000-000000000001', 'name' => 'Legs'],
                    ['tag' => 'ba81fd42-0000-0000-0000-000000000001', 'name' => 'Human'],
                    ['tag' => $kastakUuid, 'name' => 'KastakArms'],
                    ['tag' => $lynxUuid, 'name' => 'Lynx'],
                    ['tag' => 'dd7bfcdd-0000-0000-0000-000000000001', 'name' => 'Blue'],
                ]],
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['set_name'])->toBe('Lynx')
            ->and($result['variant_items'])->toHaveCount(2);

        // Also verify that querying a variant still shows "Lynx" as set name
        $variantResult = resolveRelatedItems($red);
        expect($variantResult['set_name'])->toBe('Lynx');
    });

    it('persists null set name when entity tags are absent', function (): void {
        // Without entity tags there is no game-data set name, so set_name
        // must be null even though the group itself still forms.
        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Legs',
                'class_name' => 'outlaw_legacy_armor_light_legs_01_01_01',
                'classification' => 'FPS.Armor.Light',
                'is_player_relevant' => true,
            ]);

        $red = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Legs Red',
                'class_name' => 'outlaw_legacy_armor_light_legs_01_04_02',
                'classification' => 'FPS.Armor.Light',
                'is_player_relevant' => true,
            ]);

        $blue = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Legs Blue',
                'class_name' => 'outlaw_legacy_armor_light_legs_01_04_07',
                'classification' => 'FPS.Armor.Light',
                'is_player_relevant' => true,
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['set_name'])->toBeNull();
    });

    it('persists null set name for ungrouped player-relevant set items', function (): void {
        // The "Monde" armor set has items across Helmet/Core/Arms/Legs.
        // With no entity tags there is no game-data set name; set items are
        // still linked, but set_name must stay null.
        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Helmet HighSec',
                'class_name' => 'kap_combat_heavy_helmet_02_03_01',
                'classification' => 'Char_Armor',
                'is_player_relevant' => true,
            ]);

        $core = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Core HighSec',
                'class_name' => 'kap_combat_heavy_core_02_03_01',
                'classification' => 'Char_Armor',
                'is_player_relevant' => true,
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Arms HighSec',
                'class_name' => 'kap_combat_heavy_arms_02_03_01',
                'classification' => 'Char_Armor',
                'is_player_relevant' => true,
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Monde Legs HighSec',
                'class_name' => 'kap_combat_heavy_legs_02_03_01',
                'classification' => 'Char_Armor',
                'is_player_relevant' => true,
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($core);

        expect($result['set_name'])->toBeNull();
    });
});

describe('variant axes', function (): void {
    it('labels by_size, by_grade and by_color for ship lines', function (): void {
        $make = function (string $name, string $className, int $size, int $grade): ItemData {
            return ItemData::factory()
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => $name,
                    'class_name' => $className,
                    'classification' => 'Ship.Cooler',
                    'size' => $size,
                    'grade' => $grade,
                    'is_player_relevant' => true,
                    'data' => [],
                ]);
        };

        // J-Span shape: three grade families, Frost-Star spanning all sizes.
        $frostSl = $make('Frost-Star SL', 'COOL_JSPN_S00_FrostStarSL_SCItem', 0, 3);
        $frost = $make('Frost-Star', 'COOL_JSPN_S01_FrostStar_SCItem', 1, 3);
        $frostEx = $make('Frost-Star EX', 'COOL_JSPN_S02_FrostStarEX_SCItem', 2, 3);
        $frostXl = $make('Frost-Star XL', 'COOL_JSPN_S03_FrostStarXL_SCItem', 3, 3);
        $cryoSl = $make('Cryo-Star SL', 'COOL_JSPN_S00_CryoStarSL_SCItem', 0, 2);
        $winterSl = $make('Winter-Star SL', 'COOL_JSPN_S00_WinterStarSL_SCItem', 0, 4);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($frostSl);

        expect($result['by_size'])->toHaveCount(3)
            ->and(collect($result['by_size'])->pluck('uuid')->all())
            ->toBe([$frost->item->uuid, $frostEx->item->uuid, $frostXl->item->uuid])
            ->and($result['by_grade'])->toHaveCount(2)
            ->and(collect($result['by_grade'])->pluck('uuid')->all())
            ->toBe([$cryoSl->item->uuid, $winterSl->item->uuid])
            ->and($result['by_color'])->toBe([]);

        // The base is a peer like any other member: a non-base member's
        // by_size must include the base item.
        $s1 = resolveRelatedItems($frost);

        expect($s1['by_size'])->toHaveCount(3)
            ->and(collect($s1['by_size'])->pluck('uuid')->all())
            ->toBe([$frostSl->item->uuid, $frostEx->item->uuid, $frostXl->item->uuid]);
    });

    it('labels paint groups as by_color without grade links', function (): void {
        $makePaint = function (string $name, string $className): ItemData {
            return ItemData::factory()
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => $name,
                    'class_name' => $className,
                    'classification' => 'Ship.Paints',
                    'size' => 0,
                    'grade' => 1,
                    'is_player_relevant' => true,
                    'data' => ['stdItem' => ['Tags' => ['Paint_AEGS_Gladius']]],
                ]);
        };

        $base = $makePaint('Gladius Lighthouse Paint', 'paint_aegis_gladius_lighthouse');
        $red = $makePaint('Gladius Red Paint', 'paint_aegis_gladius_red');
        $blue = $makePaint('Gladius Blue Paint', 'paint_aegis_gladius_blue');

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['by_color'])->toHaveCount(2)
            ->and(collect($result['by_color'])->pluck('uuid')->sort()->values()->all())
            ->toBe(collect([$red->item->uuid, $blue->item->uuid])->sort()->values()->all())
            ->and($result['by_size'])->toBe([])
            ->and($result['by_grade'])->toBe([]);
    });

    it('emits by_grade only for ungrouped cell members', function (): void {
        $make = function (string $name, int $grade): ItemData {
            return ItemData::factory()
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => $name,
                    'class_name' => 'COOL_TEST_S00_'.str($name)->snake().'_SCItem',
                    'classification' => 'Ship.Cooler',
                    'size' => 0,
                    'grade' => $grade,
                    'is_player_relevant' => true,
                    'data' => [],
                ]);
        };

        // Single-size lines never form variant groups; their grade cell
        // still links.
        $sl = $make('Argo SL', 2);
        $argoB = $make('Argo B', 3);
        $argoC = $make('Argo C', 4);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($sl);

        expect($result['set_name'])->toBeNull()
            ->and($result['base_item'])->toBeNull()
            ->and($result['variant_items'])->toBe([])
            ->and($result['by_size'])->toBe([])
            ->and($result['by_color'])->toBe([])
            ->and($result['by_grade'])->toHaveCount(2)
            ->and(collect($result['by_grade'])->pluck('uuid')->all())
            ->toBe([$argoB->item->uuid, $argoC->item->uuid]);
    });

    it('labels FPS color lanes as by_color', function (): void {
        $make = function (string $name, string $className): ItemData {
            return ItemData::factory()
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => $name,
                    'class_name' => $className,
                    'classification' => 'FPS.Armor.Light',
                    'size' => 1,
                    'grade' => 1,
                    'is_player_relevant' => true,
                ]);
        };

        // ADP-style colorway lane: same line and slot, differing colorway
        // index only.
        $base = $make('ADP Arms', 'adp_arms_01_01_01');
        $grey = $make('ADP Arms Grey', 'adp_arms_01_01_02');
        $tan = $make('ADP Arms Tan', 'adp_arms_01_01_07');

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['by_color'])->toHaveCount(2)
            ->and(collect($result['by_color'])->pluck('uuid')->sort()->values()->all())
            ->toBe(collect([$grey->item->uuid, $tan->item->uuid])->sort()->values()->all())
            ->and($result['by_size'])->toBe([])
            ->and($result['by_grade'])->toBe([]);
    });

    it('labels pure size ladders as by_size', function (): void {
        $make = function (string $name, string $className, int $size): ItemData {
            return ItemData::factory()
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => $name,
                    'class_name' => $className,
                    'classification' => 'Ship.Weapon.Gun',
                    'size' => $size,
                    'grade' => 1,
                    'is_player_relevant' => true,
                    'data' => [],
                ]);
        };

        $m3a = $make('M3A Cannon', 'BEHR_LaserCannon_S1', 1);
        $m4a = $make('M4A Cannon', 'BEHR_LaserCannon_S2', 2);
        $m5a = $make('M5A Cannon', 'BEHR_LaserCannon_S3', 3);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($m4a);

        expect($result['by_size'])->toHaveCount(2)
            ->and(collect($result['by_size'])->pluck('uuid')->all())
            ->toBe([$m3a->item->uuid, $m5a->item->uuid])
            ->and($result['by_color'])->toBe([])
            ->and($result['by_grade'])->toBe([]);
    });
});
