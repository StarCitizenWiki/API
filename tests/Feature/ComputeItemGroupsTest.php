<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\VariantGroup;
use App\Models\Game\VariantGroupItem;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->manufacturer = Manufacturer::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('fails when the requested game version does not exist', function (): void {
    Queue::fake();

    $this->artisan('game:compute-item-groups', ['--game-version' => 'missing'])
        ->assertExitCode(Command::FAILURE)
        ->expectsOutput('Game version "missing" does not exist.');

    Queue::assertNothingPushed();
});

it('dispatches a compute job for the default game version', function (): void {
    Queue::fake();

    GameVersion::query()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $this->artisan('game:compute-item-groups')
        ->assertExitCode(Command::SUCCESS)
        ->expectsOutput('Dispatched compute jobs for version 3.24.0-LIVE.');

    Queue::assertPushedTimes(ComputeItemVariantGroupsJob::class, 1);
    Queue::assertPushedTimes(ComputeItemSetItemsJob::class, 1);
});

it('computes variant groups from stditem tags when class names do not match', function (): void {
    $version = GameVersion::query()->create([
        'code' => '3.24.2-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $tagBaseUuid = fake()->uuid();
    $baseItem = Item::query()->create(['uuid' => $tagBaseUuid]);
    $baseData = ItemData::query()->create([
        'item_id' => $baseItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Kap Light Helmet',
        'class_name' => 'kap_light_helmet_base',
        'classification' => 'FPS.Armor.Helmet',
        'data' => [
            'stdItem' => [
                'Tags' => ['kap_light', 'Set_01', 'Color_01', 'Helmet'],
            ],
        ],
    ]);

    $variantUuid = fake()->uuid();
    $variantItem = Item::query()->create(['uuid' => $variantUuid]);
    $variantData = ItemData::query()->create([
        'item_id' => $variantItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Kap Light Helmet Rogue',
        'class_name' => 'kap_light_helmet_rogue',
        'classification' => 'FPS.Armor.Helmet',
        'data' => [
            'stdItem' => [
                'Tags' => ['kap_light', 'Set_01', 'Color_02', 'Helmet'],
            ],
        ],
    ]);

    (new ComputeItemVariantGroupsJob($version->id))->handle();

    expect($variantData->fresh()->base_id)->toBe($baseData->id)
        ->and($baseData->fresh()->base_id)->toBeNull();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull();

    $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get();
    expect($items)->toHaveCount(2);
});

it('prefers a class-name superset when source tags split one variant family', function (): void {
    $version = GameVersion::query()->create([
        'code' => '4.8.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $createItemData = function (string $name, string $className, array $tags) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => 'FPS.Armor.Helmet',
            'data' => [
                'stdItem' => [
                    'Tags' => $tags,
                ],
            ],
        ]);
    };

    $baseData = $createItemData('Tailwind Flight Helmet', 'vgl_flightsuit_helmet_01_01_01', ['VGL', 'flightsuit', 'Set_01', 'Texture_01', 'Color_01', 'Helmet']);
    $dominionData = $createItemData('Tailwind Flight Helmet Dominion Camo', 'vgl_flightsuit_helmet_01_02_01', ['VGL', 'flightsuit', 'Set_01', 'Texture_01', 'Color_01', 'Helmet']);
    $bigBiteData = $createItemData('Tailwind Flight Helmet Big Bite', 'vgl_flightsuit_helmet_01_03_01', ['vgl_flightsuit_helmet', 'Set_01', 'Texture_03', 'Color_01', 'Helmet']);
    $blackboltData = $createItemData('Tailwind Flight Helmet Blackbolt', 'vgl_flightsuit_helmet_01_04_01', ['vgl_flightsuit_helmet', 'Set_01', 'Texture_04', 'Color_01', 'Helmet']);

    (new ComputeItemVariantGroupsJob($version->id))->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull();

    $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get()->keyBy('item_data_id');

    expect($items)->toHaveCount(4)
        ->and($items[$baseData->id]->is_base)->toBeTrue()
        ->and($items[$dominionData->id]->is_base)->toBeFalse()
        ->and($items[$bigBiteData->id]->is_base)->toBeFalse()
        ->and($items[$blackboltData->id]->is_base)->toBeFalse()
        ->and($bigBiteData->fresh()->base_id)->toBe($baseData->id);
});

it('uses item name instead of Base for Ship classification variants', function (): void {
    $version = GameVersion::query()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $baseUuid = fake()->uuid();
    $baseItem = Item::query()->create(['uuid' => $baseUuid]);
    $baseData = ItemData::query()->create([
        'item_id' => $baseItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'VariPuck S4 Gimbal Mount',
        'class_name' => 'Mount_Gimbal_S4',
        'type' => 'Turret',
        'sub_type' => 'UNDEFINED',
        'classification' => 'Ship.Turret.GunTurret',
        'data' => [
            'stdItem' => [
                'Tags' => ['flightready', 'gimbal'],
            ],
        ],
    ]);

    $variantUuid = fake()->uuid();
    $variantItem = Item::query()->create(['uuid' => $variantUuid]);
    $variantData = ItemData::query()->create([
        'item_id' => $variantItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'VariPuck S4 Gimbal Mount',
        'class_name' => 'Mount_Gimbal_S4_Crus_Intrepid',
        'type' => 'Turret',
        'sub_type' => 'UNDEFINED',
        'classification' => 'Ship.Turret.GunTurret',
        'data' => [
            'stdItem' => [
                'Tags' => ['flightready', 'gimbal'],
            ],
        ],
    ]);

    (new ComputeItemVariantGroupsJob($version->id))->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull();

    $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get()->keyBy('item_data_id');

    // Both would normally get "Base" but for Ship.* items should use the item name
    expect($items[$baseData->id]->variant_name)->toBe('VariPuck S4 Gimbal Mount');
    expect($items[$variantData->id]->variant_name)->toBe('VariPuck S4 Gimbal Mount');
});

describe('ship component grouping', function (): void {
    it('groups ship components across sizes by shared class name prefix', function (): void {
        $version = GameVersion::query()->create([
            'code' => '4.1.0-LIVE',
            'channel' => 'live',
            'released_at' => now(),
            'is_default' => true,
        ]);

        $s3Uuid = fake()->uuid();
        $s3Item = Item::query()->create(['uuid' => $s3Uuid]);
        $s3Data = ItemData::query()->create([
            'item_id' => $s3Item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => 'VariPuck S3 Gimbal Mount',
            'class_name' => 'Mount_Gimbal_S3',
            'type' => 'Turret',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Turret.GunTurret',
            'data' => ['stdItem' => ['Tags' => ['flightready', 'gimbal']]],
        ]);

        $s4Uuid = fake()->uuid();
        $s4Item = Item::query()->create(['uuid' => $s4Uuid]);
        $s4Data = ItemData::query()->create([
            'item_id' => $s4Item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => 'VariPuck S4 Gimbal Mount',
            'class_name' => 'Mount_Gimbal_S4',
            'type' => 'Turret',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Turret.GunTurret',
            'data' => ['stdItem' => ['Tags' => ['flightready', 'gimbal']]],
        ]);

        (new ComputeItemVariantGroupsJob($version->id))->handle();

        $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
        expect($group)->not->toBeNull();

        $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get()->keyBy('item_data_id');
        expect($items)->toHaveCount(2)
            ->and($items[$s3Data->id])->not->toBeNull()
            ->and($items[$s4Data->id])->not->toBeNull();
    });

    it('excludes ship-specific class name suffixes from groups', function (): void {
        $version = GameVersion::query()->create([
            'code' => '4.1.0-LIVE',
            'channel' => 'live',
            'released_at' => now(),
            'is_default' => true,
        ]);

        $s4Uuid = fake()->uuid();
        $s4Item = Item::query()->create(['uuid' => $s4Uuid]);
        $s4Data = ItemData::query()->create([
            'item_id' => $s4Item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => 'VariPuck S4 Gimbal Mount',
            'class_name' => 'Mount_Gimbal_S4',
            'type' => 'Turret',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Turret.GunTurret',
            'data' => ['stdItem' => ['Tags' => ['flightready', 'gimbal']]],
        ]);

        $shipSpecificUuid = fake()->uuid();
        $shipSpecificItem = Item::query()->create(['uuid' => $shipSpecificUuid]);
        $shipSpecificData = ItemData::query()->create([
            'item_id' => $shipSpecificItem->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => 'VariPuck S4 Gimbal Mount',
            'class_name' => 'Mount_Gimbal_S4_Orig_m80',
            'type' => 'Turret',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Turret.GunTurret',
            'data' => ['stdItem' => ['Tags' => ['flightready', 'gimbal']]],
        ]);

        $s5Uuid = fake()->uuid();
        $s5Item = Item::query()->create(['uuid' => $s5Uuid]);
        $s5Data = ItemData::query()->create([
            'item_id' => $s5Item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => 'VariPuck S5 Gimbal Mount',
            'class_name' => 'Mount_Gimbal_S5',
            'type' => 'Turret',
            'sub_type' => 'UNDEFINED',
            'classification' => 'Ship.Turret.GunTurret',
            'data' => ['stdItem' => ['Tags' => ['flightready', 'gimbal']]],
        ]);

        (new ComputeItemVariantGroupsJob($version->id))->handle();

        $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
        expect($group)->not->toBeNull();

        $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get()->keyBy('item_data_id');

        // Generic S4 and S5 should be in the group
        expect($items[$s4Data->id])->not->toBeNull()
            ->and($items[$s5Data->id])->not->toBeNull();

        // Ship-specific item should NOT be in the group
        expect($items[$shipSpecificData->id] ?? null)->toBeNull();
    });
});

it('groups clothing color variants by extended class name prefix', function (): void {
    $version = GameVersion::query()->create([
        'code' => '4.1.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $whiteUuid = fake()->uuid();
    $whiteItem = Item::query()->create(['uuid' => $whiteUuid]);
    $whiteData = ItemData::query()->create([
        'item_id' => $whiteItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Orison Shipyards T-Shirt White',
        'class_name' => 'eld_shirt_04_crus07_01',
        'classification' => 'Char.Clothing.T-Shirt',
        'is_player_relevant' => true,
        'data' => [
            'stdItem' => [
                'Tags' => ['eld_shirt', 'Set_07', 'Color_01', 'T-Shirt'],
            ],
        ],
    ]);

    $blackUuid = fake()->uuid();
    $blackItem = Item::query()->create(['uuid' => $blackUuid]);
    $blackData = ItemData::query()->create([
        'item_id' => $blackItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Orison Shipyards T-Shirt Black',
        'class_name' => 'eld_shirt_04_crus07_12',
        'classification' => 'Char.Clothing.T-Shirt',
        'is_player_relevant' => true,
        'data' => [
            'stdItem' => [
                'Tags' => ['eld_shirt', 'Set_07', 'Color_12', 'T-Shirt'],
            ],
        ],
    ]);

    (new ComputeItemVariantGroupsJob($version->id))->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull();

    $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get();
    expect($items)->toHaveCount(2);

    $whiteGroupItem = $items->firstWhere('item_data_id', $whiteData->id);
    $blackGroupItem = $items->firstWhere('item_data_id', $blackData->id);

    expect($whiteGroupItem)->not->toBeNull()
        ->and($blackGroupItem)->not->toBeNull();

    // White has lower color index so it should be base
    expect($whiteGroupItem->is_base)->toBeTrue()
        ->and($blackGroupItem->is_base)->toBeFalse()
        ->and($blackData->fresh()->base_id)->toBe($whiteData->id);
});

it('separates different clothing designs within same manufacturer prefix', function (): void {
    $version = GameVersion::query()->create([
        'code' => '4.2.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $tekawUuid = fake()->uuid();
    $tekawItem = Item::query()->create(['uuid' => $tekawUuid]);
    $tekawData = ItemData::query()->create([
        'item_id' => $tekawItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Tekaw Pants',
        'class_name' => 'alb_pants_01_01_01',
        'classification' => 'Char.Clothing.Legs',
        'is_player_relevant' => true,
        'data' => [
            'stdItem' => [
                'Tags' => ['alb_pants', 'Set_01', 'Color_01'],
            ],
        ],
    ]);

    $tekawAshUuid = fake()->uuid();
    $tekawAshItem = Item::query()->create(['uuid' => $tekawAshUuid]);
    $tekawAshData = ItemData::query()->create([
        'item_id' => $tekawAshItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Tekaw Pants Ash',
        'class_name' => 'alb_pants_01_01_11',
        'classification' => 'Char.Clothing.Legs',
        'is_player_relevant' => true,
        'data' => [
            'stdItem' => [
                'Tags' => ['alb_pants', 'Set_01', 'Color_11'],
            ],
        ],
    ]);

    $strodeUuid = fake()->uuid();
    $strodeItem = Item::query()->create(['uuid' => $strodeUuid]);
    $strodeData = ItemData::query()->create([
        'item_id' => $strodeItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Strode Pants',
        'class_name' => 'alb_pants_01_02_01',
        'classification' => 'Char.Clothing.Legs',
        'is_player_relevant' => true,
        'data' => [
            'stdItem' => [
                'Tags' => ['alb_pants', 'Set_02', 'Color_01'],
            ],
        ],
    ]);

    (new ComputeItemVariantGroupsJob($version->id))->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();

    // Tekaw Pants should be grouped together
    $tekawGroup = $groups->first();
    $tekawItems = VariantGroupItem::query()->where('variant_group_id', $tekawGroup->id)->get();
    expect($tekawItems)->toHaveCount(2);
    expect($tekawItems->pluck('item_data_id')->toArray())->toContain($tekawData->id, $tekawAshData->id);

    // Strode Pants should NOT be in the same group (different design)
    $strodeGroupItem = VariantGroupItem::query()
        ->where('variant_group_id', $tekawGroup->id)
        ->where('item_data_id', $strodeData->id)
        ->first();
    expect($strodeGroupItem)->toBeNull();
});
