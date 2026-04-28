<?php

declare(strict_types=1);

use App\Http\Resources\Game\Item\RelatedItemsResource;
use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function resolveRelatedItems(ItemData $itemData): array
{
    $reloaded = ItemData::query()
        ->with([
            'gameVersion',
            'variantGroupItem.variantGroup.items.itemData.item',
            'setItems.item',
        ])
        ->find($itemData->id);

    return (new RelatedItemsResource($reloaded))->resolve();
}

function computeGroupsAndSetItems(int $gameVersionId): void
{
    (new ComputeItemVariantGroupsJob($gameVersionId))->handle();
    (new ComputeItemSetItemsJob($gameVersionId))->handle();
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
                'class_name' => 'WeaponBase',
                'classification' => 'WeaponGun',
            ]);

        $variant1 = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Weapon Blue',
                'class_name' => 'WeaponBlue',
                'classification' => 'WeaponGun',
                'base_id' => $base->id,
            ]);

        $variant2 = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Weapon Red',
                'class_name' => 'WeaponRed',
                'classification' => 'WeaponGun',
                'base_id' => $base->id,
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
                'class_name' => 'TestBase',
                'classification' => 'TestClass',
            ]);

        $variant = ItemData::factory()
            ->for(Item::factory())
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Variant',
                'class_name' => 'TestVariant',
                'classification' => 'TestClass',
                'base_id' => $base->id,
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
                'class_name' => 'OldBase',
                'classification' => 'TestClass',
            ]);

        ItemData::factory()
            ->for($variantItem)
            ->for($oldVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Old Variant',
                'class_name' => 'OldVariant',
                'classification' => 'TestClass',
                'base_id' => $oldBase->id,
            ]);

        $newBase = ItemData::factory()
            ->for($baseItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'New Base',
                'class_name' => 'NewBase',
                'classification' => 'TestClass',
            ]);

        $newVariant = ItemData::factory()
            ->for($variantItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'New Variant',
                'class_name' => 'NewVariant',
                'classification' => 'TestClass',
                'base_id' => $newBase->id,
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
    it('computes set names for variant groups', function (): void {
        $base = ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gemini A03 Sniper Rifle',
                'class_name' => 'GeminiA03',
                'classification' => 'WeaponPersonal',
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gemini A03 Sniper Rifle Eclipse',
                'class_name' => 'GeminiA03Eclipse',
                'classification' => 'WeaponPersonal',
                'base_id' => $base->id,
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gemini A03 Sniper Rifle Pathfinder',
                'class_name' => 'GeminiA03Pathfinder',
                'classification' => 'WeaponPersonal',
                'base_id' => $base->id,
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['set_name'])->toBe('Gemini A03 Sniper')
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
                'class_name' => 'LynxArms',
                'classification' => 'Armor',
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Arms Dark Green',
                'class_name' => 'LynxArmsDarkGreen',
                'classification' => 'Armor',
                'base_id' => $base->id,
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Lynx Arms Dark Red',
                'class_name' => 'LynxArmsDarkRed',
                'classification' => 'Armor',
                'base_id' => $base->id,
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['set_name'])->toBe('Lynx')
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
                'class_name' => 'A03',
                'classification' => 'WeaponPersonal',
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'A03 "Scorched" Sniper Rifle',
                'class_name' => 'A03Scorched',
                'classification' => 'WeaponPersonal',
                'base_id' => $base->id,
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'A03 "Red Alert" Sniper Rifle',
                'class_name' => 'A03RedAlert',
                'classification' => 'WeaponPersonal',
                'base_id' => $base->id,
            ]);

        ItemData::factory()
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'A03 "Lodestone" Sniper Rifle',
                'class_name' => 'A03Lodestone',
                'classification' => 'WeaponPersonal',
                'base_id' => $base->id,
            ]);

        computeGroupsAndSetItems($this->gameVersion->id);

        $result = resolveRelatedItems($base);

        expect($result['set_name'])->toBe('A03')
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

    it('derives set name from set items when variant group is absent', function (): void {
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

        expect($result['set_name'])->toBe('Monde HighSec')
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
});
