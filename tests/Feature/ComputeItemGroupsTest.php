<?php

declare(strict_types=1);

use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGradeLinks as ComputeItemVariantGradeLinksJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Models\Game\VariantGroup;
use App\Models\Game\VariantGroupItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

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

it('dispatches the grade link job with the other compute jobs', function (): void {
    Queue::fake();

    GameVersion::query()->create([
        'code' => '3.24.1-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $this->artisan('game:compute-item-groups')
        ->assertExitCode(Command::SUCCESS);

    Queue::assertPushedTimes(ComputeItemVariantGradeLinksJob::class, 1);
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

    new ComputeItemVariantGroupsJob($version->id)->handle();

    expect($variantData->fresh()->base_id)->toBe($baseData->id)
        ->and($baseData->fresh()->base_id)->toBeNull();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull();

    $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get();
    expect($items)->toHaveCount(2);
});

it('persists a null set name for groups without a qualifying set tag', function (): void {
    $version = GameVersion::query()->create([
        'code' => '3.25.0-LIVE',
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
            'is_player_relevant' => true,
            'data' => [
                'stdItem' => [
                    'Tags' => $tags,
                ],
            ],
        ]);
    };

    $createItemData('Kap Light Helmet', 'kap_light_helmet_base', ['kap_light', 'Set_01', 'Color_01', 'Helmet']);
    $createItemData('Kap Light Helmet Rogue', 'kap_light_helmet_rogue', ['kap_light', 'Set_01', 'Color_02', 'Helmet']);

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull()
        // The name line owns the armor set name ("Kap Light Helmet" ->
        // "Kap Light"); Set tags are only the fallback for unparseable
        // names.
        ->and($group->set_name)->toBe('Kap Light');
});

it('persists the set name from a Set-subtree entity tag', function (): void {
    $version = GameVersion::query()->create([
        'code' => '3.25.1-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $kastakUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $kastakUuid, 'name' => 'KastakArms', 'parent_uuid' => $setUuid]);
    $lynxUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $lynxUuid, 'name' => 'Lynx', 'parent_uuid' => $kastakUuid]);
    // Manufacturer-side chain, must never qualify even though it is common
    $rrsUuid = fake()->uuid();
    $manufacturerUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $manufacturerUuid, 'name' => 'Manufacturer']);
    EntityTag::query()->create(['uuid' => $rrsUuid, 'name' => 'RRS', 'parent_uuid' => $manufacturerUuid]);

    $createItemData = function (string $name, string $className) use ($version, $rrsUuid, $lynxUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => 'FPS.Armor.Light',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => [
                ['tag' => $rrsUuid, 'name' => 'RRS'],
                ['tag' => $lynxUuid, 'name' => 'Lynx'],
            ]],
        ]);
    };

    $createItemData('Lynx Legs', 'outlaw_legacy_armor_light_legs_01_01_01');
    $createItemData('Lynx Legs Red', 'outlaw_legacy_armor_light_legs_01_04_02');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull()
        ->and($group->set_name)->toBe('Lynx');
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

    new ComputeItemVariantGroupsJob($version->id)->handle();

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
        'name' => 'Frostbite',
        'class_name' => 'COOL_TEST_S01_Frostbite_SCItem',
        'type' => 'Cooler',
        'sub_type' => 'UNDEFINED',
        'classification' => 'Ship.Cooler',
        'is_player_relevant' => true,
        'data' => [],
    ]);

    $variantUuid = fake()->uuid();
    $variantItem = Item::query()->create(['uuid' => $variantUuid]);
    $variantData = ItemData::query()->create([
        'item_id' => $variantItem->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $this->manufacturer->id,
        'name' => 'Frostbite',
        'class_name' => 'COOL_TEST_S02_Frostbite_SCItem',
        'type' => 'Cooler',
        'sub_type' => 'UNDEFINED',
        'classification' => 'Ship.Cooler',
        'size' => 2,
        'is_player_relevant' => true,
        'data' => [],
    ]);

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull();

    $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get()->keyBy('item_data_id');

    // Both would normally get "Base" but for Ship.* items should use the item name
    expect($items[$baseData->id]->variant_name)->toBe('Frostbite')
        ->and($items[$variantData->id]->variant_name)->toBe('Frostbite');
});

describe('unswitchable ship families', function (): void {
    it('keeps turret mounts ungrouped, dropping the groups they had', function (): void {
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

        new ComputeItemVariantGroupsJob($version->id)->handle();

        // Turrets are stock hull structure: the shared Mount_Gimbal prefix
        // must not group them.
        expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0)
            ->and($s3Data->fresh()->base_id)->toBeNull()
            ->and($s4Data->fresh()->base_id)->toBeNull();
    });

    it('keeps countermeasure launchers and fuel tanks ungrouped', function (): void {
        $version = GameVersion::query()->create([
            'code' => '4.1.0-LIVE',
            'channel' => 'live',
            'released_at' => now(),
            'is_default' => true,
        ]);

        $make = function (string $name, string $className, string $classification) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'sub_type' => 'UNDEFINED',
                'classification' => $classification,
                'data' => [],
            ]);
        };

        // Countermeasures and fuel tanks are fixed stock parts: no groups expected.
        $make('Avenger Chaff Launcher', 'AEGS_Avenger_CML_Chaff', 'Ship.WeaponDefensive.CountermeasureLauncher');
        $make('Avenger Flare Launcher', 'AEGS_Avenger_CML_Flare', 'Ship.WeaponDefensive.CountermeasureLauncher');
        $make('Vanguard Harbinger Fuel Tank', 'HTNK_AEGS_Vanguard_Harbinger', 'Ship.FuelTank.Fuel');
        $make('Vanguard Sentinel Fuel Tank', 'HTNK_AEGS_Vanguard_Sentinel', 'Ship.FuelTank.Fuel');
        $make('Vanguard Harbinger Quantum Tank', 'QTNK_AEGS_Vanguard_Harbinger', 'Ship.QuantumFuelTank.QuantumFuel');
        $make('Vanguard Sentinel Quantum Tank', 'QTNK_AEGS_Vanguard_Sentinel', 'Ship.QuantumFuelTank.QuantumFuel');
        $make('Cargo Grid', 'TMBL_Max_Cargo_Grid', 'Ship.CargoGrid');
        $make('Cargo Grid', 'TMBL_Max_Cargo_Grid_Large', 'Ship.CargoGrid');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0)
            ->and(ItemData::query()->where('game_version_id', $version->id)->whereNotNull('base_id')->count())->toBe(0);
    });

    it('keeps flair ungrouped even when a shared tag would merge it', function (): void {
        $version = GameVersion::query()->create([
            'code' => '4.1.0-LIVE',
            'channel' => 'live',
            'released_at' => now(),
            'is_default' => true,
        ]);

        $make = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'type' => 'Misc',
                'sub_type' => 'UNDEFINED',
                'classification' => 'Ship.Flair.Bobbleheads',
                'data' => ['stdItem' => ['Tags' => ['avenger_bobblehead']]],
            ]);
        };

        // The tag signature alone would group these; ship families outside
        // the routing must stay ungrouped.
        $make('Avenger Bobblehead', 'Flair_Avenger_Bobblehead');
        $make('Avenger Bobblehead Red', 'Flair_Avenger_Bobblehead_Red');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0)
            ->and(ItemData::query()->where('game_version_id', $version->id)->whereNotNull('base_id')->count())->toBe(0);
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

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull();

    $items = VariantGroupItem::query()->where('variant_group_id', $group->id)->get();
    expect($items)->toHaveCount(2);

    $whiteGroupItem = $items->firstWhere('item_data_id', $whiteData->id);
    $blackGroupItem = $items->firstWhere('item_data_id', $blackData->id);

    expect($whiteGroupItem)->not->toBeNull()
        ->and($blackGroupItem)->not->toBeNull()

        // White has lower color index so it should be base
        ->and($whiteGroupItem->is_base)->toBeTrue()
        ->and($blackGroupItem->is_base)->toBeFalse()
        ->and($blackData->fresh()->base_id)->toBe($whiteData->id);
});

it('does not rewrite rows whose base_id is already null during the bulk reset', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.1.1-LIVE', 'is_default' => true]);

    // Non-player-relevant items are only ever touched by the bulk base_id
    // reset, so they isolate the change-guard behaviour.
    $nullBase = ItemData::factory()->create([
        'game_version_id' => $version->id,
        'is_player_relevant' => false,
        'base_id' => null,
    ]);

    // A pre-existing variant group triggers the $hadPreviousGroups reset path.
    VariantGroup::query()->create([
        'game_version_id' => $version->id,
        'set_name' => 'Test Set',
    ]);

    $nullBaseTs = $nullBase->fresh()->updated_at;

    $this->travel(1)->hour();

    (new ComputeItemVariantGroupsJob($version->id))->handle();

    // The row already has base_id = null, so the guarded reset must skip it.
    expect($nullBase->fresh()->base_id)->toBeNull()
        ->and($nullBase->fresh()->updated_at->getTimestamp())->toBe($nullBaseTs->getTimestamp());
});

it('clears rows that carry a stale base_id', function (): void {
    $version = GameVersion::factory()->create(['code' => '4.1.2-LIVE', 'is_default' => true]);

    $nullBase = ItemData::factory()->create([
        'game_version_id' => $version->id,
        'is_player_relevant' => false,
        'base_id' => null,
    ]);
    $setBase = ItemData::factory()->create([
        'game_version_id' => $version->id,
        'is_player_relevant' => false,
        'base_id' => $nullBase->id,
    ]);

    VariantGroup::query()->create([
        'game_version_id' => $version->id,
        'set_name' => 'Test Set',
    ]);

    new ComputeItemVariantGroupsJob($version->id)->handle();

    // The row had a base_id and is not part of any computed group, so it must be cleared
    expect($setBase->fresh()->base_id)->toBeNull();
});

it('groups transposed class-name spellings by name stem', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.0-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $kastakUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $kastakUuid, 'name' => 'KastakArms', 'parent_uuid' => $setUuid]);
    $inquisitorUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $inquisitorUuid, 'name' => 'Inquisitor', 'parent_uuid' => $kastakUuid]);
    $partUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $partUuid, 'name' => 'Part']);
    $armsUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $armsUuid, 'name' => 'Arms', 'parent_uuid' => $partUuid]);

    $createItemData = function (string $name, string $className) use ($version, $inquisitorUuid, $armsUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Arms',
            'classification' => 'FPS.Armor.Medium',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => [
                ['tag' => $inquisitorUuid, 'name' => 'Inquisitor'],
                ['tag' => $armsUuid, 'name' => 'Arms'],
            ]],
        ]);
    };

    $createItemData('Inquisitor Arms Base', 'outlaw_legacy_armor_medium_arms_01_01_01');
    $createItemData('Inquisitor Arms Icefall', 'outlaw_legacy_medium_armor_arms_01_02_01');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(1)
        ->and($groups->first()->set_name)->toBe('Inquisitor')
        ->and($groups->first()->items()->count())->toBe(2);
});

it('labels armor pools by tag-anchored majority and tolerates one untagged member', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.2-adp-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $adpUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $adpUuid, 'name' => 'ADP-mk4', 'parent_uuid' => $setUuid]);
    $partUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $partUuid, 'name' => 'Part']);
    $helmetPartUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $helmetPartUuid, 'name' => 'Helmet', 'parent_uuid' => $partUuid]);

    $createItemData = function (string $name, string $className, array $leafEntries) use ($version, $helmetPartUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Helmet',
            'classification' => 'FPS.Armor.Helmet',
            'is_player_relevant' => true,
            'data' => [
                'stdItem' => ['Tags' => ['Marine_Heavy', 'Set_01', 'Color_01', 'SM_Marine', 'Helmet', 'HelmetCarryable']],
                'entity_tag_map' => [...$leafEntries, ['tag' => $helmetPartUuid, 'name' => 'Helmet']],
            ],
        ]);
    };

    $adp = fn (): array => [['tag' => $adpUuid, 'name' => 'ADP-mk4']];

    $createItemData('ADP-mk4 Helmet Woodland', 'cds_armor_heavy_helmet_01_01_01', $adp());
    $createItemData('ADP-mk4 Helmet Red Alert', 'cds_armor_heavy_helmet_01_iae2022_01', $adp());
    $createItemData('ADP-mk4 Helmet Righteous', 'cds_armor_heavy_helmet_02_03_xenothreat', $adp());
    // CIG data bug: classed Helmet, named Core; line parsing rejects it
    // and the cross-lane check keeps it out of the fallback pool too.
    $phantom = $createItemData('ADP-mk4 Core Justified', 'cds_armor_heavy_helmet_02_04_xenothreat', $adp());
    // One CIG gap: bare Set row where siblings carry the named tag.
    $createItemData('ADP-mk4 Helmet Woodland', 'cds_armor_heavy_helmet_03_01_01', [['tag' => $setUuid, 'name' => 'Set']]);
    // The tag covers DCP too, but consumers read the line: DCP splits off.
    $dcp = $createItemData('DCP Armor Helmet Hunter Camo', 'cds_combat_heavy_helmet_03_01_01', $adp());

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(1)
        ->and($groups->first()->set_name)->toBe('ADP-mk4')
        ->and($groups->first()->items()->count())->toBe(4)
        ->and($groups->first()->items()->orderBy('variant_name')->pluck('variant_name')->all())
        ->toBe(['Base', 'Red Alert', 'Righteous', 'Woodland'])
        ->and(VariantGroupItem::query()->where('item_data_id', $phantom->id)->exists())->toBeFalse()
        ->and($phantom->fresh()->base_id)->toBeNull()
        ->and(VariantGroupItem::query()->where('item_data_id', $dcp->id)->exists())->toBeFalse()
        ->and($dcp->fresh()->base_id)->toBeNull();
});

it('separates Citadel from Citadel-SE lines and merges SE-tagged colorways across stems', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.1-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $kastakUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $kastakUuid, 'name' => 'KastakArms', 'parent_uuid' => $setUuid]);
    $citadelUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $citadelUuid, 'name' => 'Citadel', 'parent_uuid' => $kastakUuid]);
    $citadelSeUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $citadelSeUuid, 'name' => 'Citadel-SE', 'parent_uuid' => $kastakUuid]);
    $partUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $partUuid, 'name' => 'Part']);
    $armsUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $armsUuid, 'name' => 'Arms', 'parent_uuid' => $partUuid]);

    $createItemData = function (string $name, string $className, string $leafUuid) use ($version, $armsUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Arms',
            'classification' => 'FPS.Armor.Heavy',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => [
                ['tag' => $leafUuid, 'name' => EntityTag::query()->where('uuid', $leafUuid)->value('name')],
                ['tag' => $armsUuid, 'name' => 'Arms'],
            ]],
        ]);
    };

    $createItemData('Citadel Arms Base', 'outlaw_legacy_armor_heavy_arms_01_01_01', $citadelUuid);
    $createItemData('Citadel Arms Dark Red', 'outlaw_legacy_armor_heavy_arms_01_02_01', $citadelUuid);
    $createItemData('Citadel Arms Icefall', 'outlaw_legacy_armor_heavy_arms_01_03_01', $citadelSeUuid);
    $createItemData('Citadel-SE Arms White', 'outlaw_legacy_heavy_armor_arms_02_01_01', $citadelSeUuid);

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->orderBy('set_name')->get();
    expect($groups)->toHaveCount(2)
        ->and($groups[0]->set_name)->toBe('Citadel')
        ->and($groups[0]->items()->count())->toBe(2)
        ->and($groups[1]->set_name)->toBe('Citadel-SE')
        ->and(ItemData::query()->whereIn('id', $groups[1]->items()->pluck('item_data_id'))->pluck('name')->sort()->values()->all())->toBe(['Citadel Arms Icefall', 'Citadel-SE Arms White']);
});

it('orphans members whose Set leaf is disjoint from the line', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.2-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $inquisitorUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $inquisitorUuid, 'name' => 'Inquisitor', 'parent_uuid' => $setUuid]);
    $horizonUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $horizonUuid, 'name' => 'Horizon', 'parent_uuid' => $setUuid]);

    $createItemData = function (string $name, ?string $leafUuid) use ($version, $setUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);
        $tagMap = [['tag' => $setUuid, 'name' => 'Set']];

        if ($leafUuid !== null) {
            $tagMap[] = ['tag' => $leafUuid, 'name' => EntityTag::query()->where('uuid', $leafUuid)->value('name')];
        }

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => 'outlaw_legacy_armor_medium_core_01_0'.random_int(1, 9).'_01',
            'type' => 'Char_Armor_Torso',
            'classification' => 'FPS.Armor.Medium',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => $tagMap],
        ]);
    };

    $createItemData('Inquisitor Core Base', $inquisitorUuid);
    $createItemData('Inquisitor Core Red', $inquisitorUuid);
    $rager = $createItemData('Inquisitor Core Rager', $horizonUuid);
    $createItemData('Inquisitor Core Untagged', null);

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(1)
        ->and($groups->first()->items()->count())->toBe(3)
        ->and(ItemData::query()->whereIn('id', $groups->first()->items()->pluck('item_data_id'))->pluck('name'))->not->toContain('Inquisitor Core Rager')
        ->and($rager->fresh()->base_id)->toBeNull();
});

it('keeps NBSP-separated armor names groupable', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.3-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Helmet',
            'classification' => 'FPS.Armor.Heavy',
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    $createItemData("Oracle\u{00A0}Helmet", 'grin_utility_heavy_helmet_01_01_01');
    $createItemData('Oracle Helmet Red', 'grin_utility_heavy_helmet_01_02_01');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(1)
        ->and($groups->first()->items()->count())->toBe(2);
});

it('does not group by stem prefix', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.4-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Arms',
            'classification' => 'FPS.Armor.Heavy',
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    $createItemData('Citadel Arms Base', 'outlaw_legacy_armor_heavy_arms_01_01_01');
    $armsman = $createItemData('Citadel Armsman', 'outlaw_legacy_armor_heavy_arms_01_02_01');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0)
        ->and($armsman->fresh()->base_id)->toBeNull();
});

it('keeps same-stem items of different manufacturers apart', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.5-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);
    $otherManufacturer = Manufacturer::query()->create(['uuid' => fake()->uuid(), 'name' => 'Other Manufacturer', 'code' => 'OTHR']);

    $createItemData = function (string $name, int $manufacturerId) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $manufacturerId,
            'name' => $name,
            'class_name' => 'specialist_heavy_helmet_01_0'.random_int(1, 9).'_01',
            'type' => 'Char_Armor_Helmet',
            'classification' => 'FPS.Armor.Heavy',
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    $createItemData('Specialist Helmet Base', $this->manufacturer->id);
    $createItemData('Specialist Helmet Red', $otherManufacturer->id);

    new ComputeItemVariantGroupsJob($version->id)->handle();

    expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0);
});

it('rejoins single-member stems into their leaf line via the cross-stem merge', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.6-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $orcUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $orcUuid, 'name' => 'ORC-mkX', 'parent_uuid' => $setUuid]);
    $partUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $partUuid, 'name' => 'Part']);
    $helmetUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $helmetUuid, 'name' => 'Helmet', 'parent_uuid' => $partUuid]);

    $createItemData = function (string $name, string $className) use ($version, $orcUuid, $helmetUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Helmet',
            'classification' => 'FPS.Armor.Medium',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => [
                ['tag' => $orcUuid, 'name' => 'ORC-mkX'],
                ['tag' => $helmetUuid, 'name' => 'Helmet'],
            ]],
        ]);
    };

    $gcdArmy = $createItemData('GCD-Army Helmet', 'cds_combat_medium_helmet_04_01_01');
    $createItemData('ORC-mkX Helmet', 'cds_armor_medium_helmet_01_01_01');
    $createItemData('ORC-mkX Helmet Green', 'cds_armor_medium_helmet_01_02_01');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(1)
        ->and($groups->first()->items()->count())->toBe(3)
        ->and($groups->first()->set_name)->toBe('ORC-mkX');
});

it('disables the cross-stem leaf merge when the Part row is missing', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.7-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $kastakUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $kastakUuid, 'name' => 'KastakArms', 'parent_uuid' => $setUuid]);
    $citadelUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $citadelUuid, 'name' => 'Citadel', 'parent_uuid' => $kastakUuid]);
    $citadelSeUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $citadelSeUuid, 'name' => 'Citadel-SE', 'parent_uuid' => $kastakUuid]);

    $createItemData = function (string $name, string $className, string $leafUuid) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Arms',
            'classification' => 'FPS.Armor.Heavy',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => [
                ['tag' => $leafUuid, 'name' => EntityTag::query()->where('uuid', $leafUuid)->value('name')],
            ]],
        ]);
    };

    $createItemData('Citadel Arms Base', 'outlaw_legacy_armor_heavy_arms_01_01_01', $citadelUuid);
    $createItemData('Citadel Arms Dark Red', 'outlaw_legacy_armor_heavy_arms_01_02_01', $citadelUuid);
    $icefall = $createItemData('Citadel Arms Icefall', 'outlaw_legacy_armor_heavy_arms_01_03_01', $citadelSeUuid);
    $seWhite = $createItemData('Citadel-SE Arms White', 'outlaw_legacy_heavy_armor_arms_02_01_01', $citadelSeUuid);

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(1)
        ->and($groups->first()->set_name)->toBe('Citadel')
        ->and($groups->first()->items()->count())->toBe(2)
        ->and($icefall->fresh()->base_id)->toBeNull()
        ->and($seWhite->fresh()->base_id)->toBeNull();
});

it('does not fuse distinct lines on a manufacturer-doubled Set leaf', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.8-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    // CIG publishes "QuirinusTech" twice: once under Set, once under Manufacturer.
    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $qrtSetLeaf = fake()->uuid();
    EntityTag::query()->create(['uuid' => $qrtSetLeaf, 'name' => 'QuirinusTech', 'parent_uuid' => $setUuid]);
    $mfrRow = fake()->uuid();
    EntityTag::query()->create(['uuid' => $mfrRow, 'name' => 'Manufacturer']);
    $qrtMfrLeaf = fake()->uuid();
    EntityTag::query()->create(['uuid' => $qrtMfrLeaf, 'name' => 'QuirinusTech', 'parent_uuid' => $mfrRow]);
    $partUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $partUuid, 'name' => 'Part']);
    $armsUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $armsUuid, 'name' => 'Arms', 'parent_uuid' => $partUuid]);

    $createItemData = function (string $name, string $className) use ($version, $qrtSetLeaf, $qrtMfrLeaf, $armsUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Arms',
            'classification' => 'FPS.Armor.Medium',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => [
                ['tag' => $qrtSetLeaf, 'name' => 'QuirinusTech'],
                ['tag' => $qrtMfrLeaf, 'name' => 'QuirinusTech'],
                ['tag' => $armsUuid, 'name' => 'Arms'],
            ]],
        ]);
    };

    $createItemData('Artimex Arms Base', 'qrt_specialist_medium_arms_01_01_01');
    $createItemData('Artimex Arms Red', 'qrt_specialist_medium_arms_01_02_01');
    $createItemData('Testudo Arms Base', 'qrt_combat_medium_arms_01_01_01');
    $createItemData('Testudo Arms Blue', 'qrt_combat_medium_arms_01_02_01');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(2)
        ->and($groups->pluck('id')->flatMap(fn ($id) => VariantGroupItem::query()->where('variant_group_id', $id)->pluck('item_data_id'))->count())->toBe(4);
});

describe('weapon variant grammar', function (): void {
    it('keeps NBSP-separated weapon names groupable', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.0-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createWeapon = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'FPS.Weapon.Medium',
                'type' => 'WeaponPersonal',
                'sub_type' => 'Medium',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createWeapon('Arrowhead Sniper Rifle', 'klwe_sniper_energy_01');
        $nbspEdition = $createWeapon("Arrowhead\u{00A0}\"Voyager\" Sniper Rifle", 'klwe_sniper_energy_01_cen01');
        $createWeapon('Arrowhead "Warhawk" Sniper Rifle', 'klwe_sniper_energy_01_green01');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(3)
            ->and($nbspEdition->fresh()->base_id)->not->toBeNull();
    });

    it('treats a trailing zoom paren as the kind', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.1-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createOptic = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'FPS.WeaponAttachment.IronSight',
                'type' => 'WeaponAttachment',
                'sub_type' => 'IronSight',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createOptic('Gamma (1x Holographic)', 'nvtc_optics_holo_x1_s1');
        $createOptic('Gamma "Black Op" (1x Holographic)', 'nvtc_optics_holo_x1_s1_tint01');
        $gammaDuo = $createOptic('Gamma Duo (2x Holographic)', 'nvtc_optics_holo_x2_s1');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(2)
            ->and($gammaDuo->fresh()->base_id)->toBeNull();
    });

    it('strips double- and single-quoted editions', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.2-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createWeapon = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'FPS.Weapon.Medium',
                'type' => 'WeaponPersonal',
                'sub_type' => 'Medium',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createWeapon('Deadrig Shotgun', 'none_shotgun_ballistic_01');
        $singleQuoted = $createWeapon("Deadrig 'Noxious' Shotgun", 'none_shotgun_ballistic_01_green01');
        $createWeapon('Deadrig "Smudge" Shotgun', 'none_shotgun_ballistic_01_tint01');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(3)
            ->and($singleQuoted->fresh()->base_id)->not->toBeNull();
    });

    it('canonicalizes magazine kinds and absorbs the weapon word', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.3-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createItem = function (string $name, string $className, string $classification, string $type, string $subType) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => $classification,
                'type' => $type,
                'sub_type' => $subType,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        // 'SMG' is absorbed into the kind, so both magazines share stem 'C54'.
        $createItem('C54 SMG Magazine (50 cap)', 'gmni_smg_ballistic_01_mag', 'FPS.WeaponAttachment.Magazine', 'WeaponAttachment', 'Magazine');
        $bareMag = $createItem('C54 Magazine (50 cap)', 'gmni_smg_ballistic_01_mag_twin', 'FPS.WeaponAttachment.Magazine', 'WeaponAttachment', 'Magazine');
        $weapon = $createItem('C54 SMG', 'gmni_smg_ballistic_01', 'FPS.Weapon.Medium', 'WeaponPersonal', 'Medium');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(2)
            ->and($bareMag->fresh()->base_id)->not->toBeNull()
            // A magazine never groups with the weapon of the same stem.
            ->and($weapon->fresh()->base_id)->toBeNull();
    });

    it('strips pinned color tails and keeps Black Prism intact', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.4-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createItem = function (string $name, string $className, string $classification, string $type, string $subType) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => $classification,
                'type' => $type,
                'sub_type' => $subType,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createItem('FieldLite Flashlight', 'nvtc_ubarrel_flash_s1', 'FPS.WeaponAttachment.BottomAttachment', 'WeaponAttachment', 'BottomAttachment');
        $colorTail = $createItem('FieldLite Flashlight Red', 'nvtc_ubarrel_flash_s1_02', 'FPS.WeaponAttachment.BottomAttachment', 'WeaponAttachment', 'BottomAttachment');
        $createItem('FieldLite "Scorched" Flashlight', 'nvtc_ubarrel_flash_s1_03', 'FPS.WeaponAttachment.BottomAttachment', 'WeaponAttachment', 'BottomAttachment');

        // 'Black' is a product word here, not a colorway: the head set excludes it.
        $createItem('Black Prism (8x Telescopic)', 'ksar_optics_tsco_x8_s3', 'FPS.WeaponAttachment.IronSight', 'WeaponAttachment', 'IronSight');
        $prism = $createItem('Prism (8x Telescopic)', 'volt_optics_tsco_x8_s3', 'FPS.WeaponAttachment.IronSight', 'WeaponAttachment', 'IronSight');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(3)
            ->and($colorTail->fresh()->base_id)->not->toBeNull()
            ->and($prism->fresh()->base_id)->toBeNull();
    });

    it('keeps Arma size digits load-bearing and joins anchored barrel families', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.5-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createBarrel = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'FPS.WeaponAttachment.BarrelAttachment',
                'type' => 'WeaponAttachment',
                'sub_type' => 'Barrel',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createBarrel('Sion Compensator1', 'arma_barrel_comp_s1');
        $torrent = $createBarrel('Torrent Compensator1', 'arma_barrel_comp_s1_02');
        $createBarrel('Stark Compensator1', 'arma_barrel_comp_s1_03');
        $size2 = $createBarrel('Sion Compensator2', 'arma_barrel_comp_s2');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(3)
            ->and($torrent->fresh()->base_id)->not->toBeNull()
            ->and($size2->fresh()->base_id)->toBeNull();
    });

    it('dissolves family buckets without an anchor in the bucket', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.6-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createItem = function (string $name, string $className, array $tags, string $classification, string $type, string $subType) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => $classification,
                'type' => $type,
                'sub_type' => $subType,
                'is_player_relevant' => true,
                'data' => ['stdItem' => ['Tags' => $tags]],
            ]);
        };

        // Five distinct multi-tool products share only the grin_multitool_01
        // family; their tags would group them, but weapon items never fall
        // back to tag grouping.
        $cutter = $createItem('OxyTorch Cutter Attachment', 'grin_multitool_01_cutter', ['grin_multitool_01'], 'FPS.WeaponAttachment.Utility', 'WeaponAttachment', 'Utility');
        $tractor = $createItem('TruHold Tractor Beam Attachment', 'grin_multitool_01_tractorbeam', ['grin_multitool_01'], 'FPS.WeaponAttachment.Utility', 'WeaponAttachment', 'Utility');
        // The family anchor exists, but in a different type: it cannot rescue the Utility bucket.
        $createItem('Pyro RYT Multi-Tool', 'grin_multitool_01', ['grin_multitool_01'], 'FPS.Weapon.Gadget', 'WeaponPersonal', 'Gadget');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0)
            ->and($cutter->fresh()->base_id)->toBeNull()
            ->and($tractor->fresh()->base_id)->toBeNull();
    });

    it('joins anchored family buckets across bare-word editions', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.7-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createKnife = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'FPS.Weapon.Knife',
                'type' => 'WeaponPersonal',
                'sub_type' => 'Knife',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createKnife('Myuda Knife', 'banu_melee_01');
        $njakte = $createKnife('Njakte Knife', 'banu_melee_01_01');
        $createKnife('Zogo Knife', 'banu_melee_01_03');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(3)
            ->and($njakte->fresh()->base_id)->not->toBeNull();
    });

    it('buckets NULL-kind QuikFlarePro colorways and splits Pink QuikFlare by sub_type', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.8-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createGlowstick = function (string $name, string $className, string $classification, string $subType) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => $classification,
                'type' => 'WeaponPersonal',
                'sub_type' => $subType,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $cyan = $createGlowstick('Cyan QuikFlarePro', 'Carryable_1H_CU_Glowstick_Blue', 'FPS.Weapon.Grenade', 'Grenade');
        $createGlowstick('Green QuikFlarePro', 'Carryable_1H_CU_Glowstick_Green', 'FPS.Weapon.Grenade', 'Grenade');
        $createGlowstick('Orange QuikFlarePro', 'Carryable_1H_CU_Glowstick_Orange', 'FPS.Weapon.Grenade', 'Grenade');
        $red = $createGlowstick('Red QuikFlarePro', 'Carryable_1H_CU_Glowstick_Red', 'FPS.Weapon.Grenade', 'Grenade');
        $createGlowstick('Yellow QuikFlarePro', 'Carryable_1H_CU_Glowstick_Yellow', 'FPS.Weapon.Grenade', 'Grenade');
        $pinkGrenade = $createGlowstick('Pink QuikFlare', 'Carryable_1H_CU_Glowstick_Pink', 'FPS.Weapon.Grenade', 'Grenade');
        $pinkPrison = $createGlowstick('Pink QuikFlare', 'Carryable_1H_CU_Glowstick_Pink_Active_Prison', 'FPS.Weapon.Gadget', 'Gadget');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(5)
            // 'Carryable_1H_CU_Glowstick_Blue' sorts first, so Cyan is the base.
            ->and($red->fresh()->base_id)->toBe($cyan->id)
            ->and($pinkGrenade->fresh()->base_id)->toBeNull()
            ->and($pinkPrison->fresh()->base_id)->toBeNull();
    });

    it('joins the class-typo Deadeye edition by name', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.9-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createWeapon = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'FPS.Weapon.Medium',
                'type' => 'WeaponPersonal',
                'sub_type' => 'Medium',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createWeapon('Atzkav Sniper Rifle', 'lbco_sniper_energy_01');
        $createWeapon('Atzkav "Venom" Sniper Rifle', 'lbco_sniper_energy_01_acid01');
        // CIG class typo: the family segment '_01' is missing, so only the name grammar can reach it.
        $deadeye = $createWeapon('Atzkav "Deadeye" Sniper Rifle', 'lbco_sniper_energy_imp01');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(3)
            ->and($deadeye->fresh()->base_id)->not->toBeNull();
    });

    it('allows one-word stems', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.10-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createItem = function (string $name, string $className, string $classification, string $type, string $subType) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => $classification,
                'type' => $type,
                'sub_type' => $subType,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createItem('Coda Pistol', 'ksar_pistol_ballistic_01', 'FPS.Weapon.Small', 'WeaponPersonal', 'Small');
        $createItem('Coda "Nighthawk" Pistol', 'ksar_pistol_ballistic_01_headhunters01', 'FPS.Weapon.Small', 'WeaponPersonal', 'Small');
        // Single word, no kind word at all: a NULL-kind bucket of one stays ungrouped.
        $shiv = $createItem('Shiv', 'none_melee_01', 'FPS.Weapon.Knife', 'WeaponPersonal', 'Knife');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(2)
            ->and($shiv->fresh()->base_id)->toBeNull();
    });

    it('rejoins kind-dropped editions after the damage-modifier strip', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.11-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createItem = function (string $name, string $className, string $classification, string $type, string $subType) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => $classification,
                'type' => $type,
                'sub_type' => $subType,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createItem('Quartz Energy SMG', 'volt_smg_energy_01', 'FPS.Weapon.Medium', 'WeaponPersonal', 'Medium');
        $gcdArmy = $createItem('Quartz "GCD-Army" SMG', 'volt_smg_energy_01_spc', 'FPS.Weapon.Medium', 'WeaponPersonal', 'Medium');
        $battery = $createItem('Quartz Energy SMG Battery (45 cap)', 'volt_smg_energy_01_mag', 'FPS.WeaponAttachment.Magazine', 'WeaponAttachment', 'Magazine');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(2)
            ->and($gcdArmy->fresh()->base_id)->not->toBeNull()
            // Same stem, different kind and sub_type: the battery stays out.
            ->and($battery->fresh()->base_id)->toBeNull();
    });

    it('merges overlapping name and family buckets into one component', function (): void {
        $version = GameVersion::query()->create(['code' => '4.11.12-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $createOptic = function (string $name, string $className) use ($version): ItemData {
            $item = Item::query()->create(['uuid' => fake()->uuid()]);

            return ItemData::query()->create([
                'item_id' => $item->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'FPS.WeaponAttachment.IronSight',
                'type' => 'WeaponAttachment',
                'sub_type' => 'IronSight',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $createOptic('Gamma (1x Holographic)', 'nvtc_optics_holo_x1_s1');
        // Reaches the group via the family join only (its own stem bucket is a singleton).
        $createOptic('Gamma LL (1x Holographic)', 'nvtc_optics_holo_x1_s1_LAMP');
        $createOptic('Gamma "Black Op" (1x Holographic)', 'nvtc_optics_holo_x1_s1_tint01');
        // Reaches the group via the name bucket only (outside the family bucket).
        $weaponMountTwin = $createOptic('Gamma (1x Holographic)', 'weaponMount_nvtc_optics_holo_x1_s1');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->count())->toBe(4)
            ->and($groups->first()->items()->pluck('item_data_id'))->toContain($weaponMountTwin->id);
    });
});

// Ship-component fixtures; class names, display names, sizes and grades mirror
// Ship line fixtures; class names and display names mirror the game data.
describe('ship lines', function (): void {
    it('merges one line across sizes and keeps grade families apart', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-cool-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className, int $size, int $grade) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.Cooler',
                'size' => $size,
                'grade' => $grade,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $cryo = [
            $make('Cryo-Star SL', 'COOL_JSPN_S00_CryoStarSL_SCItem', 0, 2),
            $make('Cryo-Star', 'COOL_JSPN_S01_CryoStar_SCItem', 1, 2),
            $make('Cryo-Star EX', 'COOL_JSPN_S02_CryoStarEX_SCItem', 2, 2),
            $make('Cryo-Star XL', 'COOL_JSPN_S03_CryoStarXL_SCItem', 3, 2),
        ];
        // Grade families are distinct products: Frost-Star is the grade-C
        // sibling line, never fused into Cryo-Star.
        $frost = [
            $make('Frost-Star SL', 'COOL_JSPN_S00_FrostStarSL_SCItem', 0, 3),
            $make('Frost-Star', 'COOL_JSPN_S01_FrostStar_SCItem', 1, 3),
        ];

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        $groupOf = fn (ItemData $item): ?int => VariantGroupItem::query()->where('item_data_id', $item->id)->first()?->variant_group_id;

        expect($groups)->toHaveCount(2)
            ->and($groups->map(fn ($group) => $group->items()->count())->sort()->values()->all())->toBe([2, 4])
            ->and($groupOf($cryo[0]))->toBe($groupOf($cryo[3]))
            ->and($groupOf($cryo[0]))->not->toBe($groupOf($frost[0]))
            // Output contract: no invented set_name for ship lines.
            ->and($groups->every(fn ($group) => $group->set_name === null))->toBeTrue();
    });

    it('never strips a size marker without display-name corroboration', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-word-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.Cooler',
                'size' => 0,
                'grade' => 2,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        // A one-word name never corroborates: the SL marker stays part of
        // the stem, so the two spellings key different lines.
        $make('HEX', 'COOL_JSPN_S00_CryoStarSL_SCItem');
        $make('Cryo-Star', 'COOL_JSPN_S01_CryoStar_SCItem');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0);
    });

    it('groups gun size ladders by maker and weapon type', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-gun-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className, int $size) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.Weapon.Gun',
                'size' => $size,
                'grade' => 1,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $make('M3A Cannon', 'BEHR_LaserCannon_S1', 1);
        $make('M4A Cannon', 'BEHR_LaserCannon_S2', 2);
        $make('M5A Cannon', 'BEHR_LaserCannon_S3', 3);
        // A different maker's laser cannons are a different line.
        $make('Omnisky III Cannon', 'AMRS_LaserCannon_S1', 1);
        $make('Omnisky VI Cannon', 'AMRS_LaserCannon_S2', 2);

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(2)
            ->and($groups->map(fn ($group) => $group->items()->count())->sort()->values()->all())->toBe([2, 3]);
    });

    it('keeps loadout spellings off the maker ladder', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-vng-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.Weapon.Gun',
                'size' => 2,
                'grade' => 1,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $m3a = $make('M3A Cannon', 'BEHR_LaserCannon_S1');
        $m4a = $make('M4A Cannon', 'BEHR_LaserCannon_S2');
        // The Vanguard loadout spelling carries its own identity token (VNG):
        // a differently-named product, never a size step of the M-line.
        $cvsa = $make('CVSA Cannon', 'BEHR_LaserCannon_VNG_S2');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->pluck('item_data_id')->sort()->values()->all())
            ->toBe(collect([$m3a->id, $m4a->id])->sort()->values()->all())
            ->and($cvsa->fresh()->base_id)->toBeNull();
    });

    it('groups flight blades across stock and tuning editions', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-blade-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.FlightController',
                'size' => null,
                'grade' => null,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $gladius = [
            $make('Gladius Standard Flight Blade', 'Controller_Flight_AEGS_Gladius'),
            $make('Gladius PHB Flight Blade', 'Controller_Flight_AEGS_Gladius_Blade_HND'),
            $make('Gladius TSB Flight Blade', 'Controller_Flight_AEGS_Gladius_Blade_SPD'),
        ];
        $pisces = [
            $make('Pisces Standard Flight Blade', 'Controller_Flight_ANVL_Pisces_Flight_Blade_HND'),
            $make('Pisces TSB Flight Blade', 'Controller_Flight_ANVL_Pisces_Flight_Blade_SPD'),
        ];

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        $groupOf = fn (ItemData $item): ?int => VariantGroupItem::query()->where('item_data_id', $item->id)->first()?->variant_group_id;

        expect($groups)->toHaveCount(2)
            ->and($groups->map(fn ($group) => $group->items()->count())->sort()->values()->all())->toBe([2, 3])
            ->and($groupOf($gladius[0]))->toBe($groupOf($gladius[2]))
            ->and($groupOf($gladius[0]))->not->toBe($groupOf($pisces[0]));
    });

    it('merges ordnance sizes and seeker codes', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-misl-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className, int $size) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.Missile.Torpedo',
                'size' => $size,
                'grade' => 1,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $cs10 = $make('EX-T10-CS "Executor" Torpedo', 'MISL_S10_CS_BEHR_Executor', 10);
        $cs12 = $make('EX-T12-CS "Executor" Torpedo', 'MISL_S12_CS_BEHR_Executor', 12);
        // The seeker code is a warhead variant of the same line, like size.
        $em10 = $make('EX-T10-EM "Executor" Torpedo', 'MISL_S10_EM_BEHR_Executor', 10);

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->items()->pluck('item_data_id')->sort()->values()->all())
            ->toBe(collect([$cs10->id, $cs12->id, $em10->id])->sort()->values()->all());
    });

    it('groups missile racks by mount-count line across sizes', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-rack-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.MissileLauncher.MissileRack',
                'size' => null,
                'grade' => null,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $single = [
            $make('MSD-111 Missile Rack', 'MRCK_S01_BEHR_Single_S01'),
            $make('MSD-212 Missile Rack', 'MRCK_S02_BEHR_Single_S02'),
            $make('MSD-313 Missile Rack', 'MRCK_S03_BEHR_Single_S03'),
        ];
        $dual = [
            $make('MSD-221 Missile Rack', 'MRCK_S02_BEHR_Dual_S01'),
            $make('MSD-322 Missile Rack', 'MRCK_S03_BEHR_Dual_S02'),
        ];

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        $groupOf = fn (ItemData $item): ?int => VariantGroupItem::query()->where('item_data_id', $item->id)->first()?->variant_group_id;

        expect($groups)->toHaveCount(2)
            ->and($groups->map(fn ($group) => $group->items()->count())->sort()->values()->all())->toBe([2, 3])
            ->and($groupOf($single[0]))->not->toBe($groupOf($dual[0]));
    });

    it('groups mining modules into mark ladders, not one domain pool', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-mining-module-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Mining.Module',
                'size' => null,
                'grade' => null,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $fltr = [
            $make('FLTR Module', 'Mining_Modules_Passive_FLTR_MK1'),
            $make('FLTR-L Module', 'Mining_Modules_Passive_FLTR_MK2'),
            $make('FLTR-XL Module', 'Mining_Modules_Passive_FLTR_MK3'),
        ];
        $focus = [
            $make('Focus Module', 'Mining_Modules_Passive_Focus_MK1'),
            $make('Focus III Module', 'Mining_Modules_Passive_Focus_MK3'),
        ];
        // Distinct active products never share a line.
        $surge = $make('Surge Module', 'Mining_Modules_Active_Surge');
        $brandt = $make('Brandt Module', 'Mining_Modules_Active_Brandt');
        $archetype = $make('Mining Modules', 'Mining_Modules');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groupOf = fn (ItemData $item): ?int => VariantGroupItem::query()->where('item_data_id', $item->id)->first()?->variant_group_id;
        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();

        expect($groups)->toHaveCount(2)
            ->and($groupOf($fltr[0]))->toBe($groupOf($fltr[1]))->toBe($groupOf($fltr[2]))
            ->and($groupOf($focus[0]))->toBe($groupOf($focus[1]))
            ->and($groupOf($fltr[0]))->not->toBe($groupOf($focus[0]))
            ->and($groupOf($surge))->toBeNull()
            ->and($groupOf($brandt))->toBeNull()
            ->and($groupOf($archetype))->toBeNull();
    });

    it('merges torpedo seeker codes into one line', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-seeker-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.Missile.Torpedo',
                'size' => null,
                'grade' => null,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $members = [
            $make('Calamity XII-CS Torpedo', 'MISL_S12_CS_TALN_Calamity'),
            $make('Calamity XII-EM Torpedo', 'MISL_S12_EM_TALN_Calamity'),
            $make('Calamity XII-IR Torpedo', 'MISL_S12_IR_TALN_Calamity'),
            $make('EX-T10-CS "Executor" Torpedo', 'MISL_S10_CS_BEHR_Executor'),
            $make('EX-T12-EM "Executor" Torpedo', 'MISL_S12_EM_BEHR_Executor'),
        ];

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        $groupOf = fn (ItemData $item): ?int => VariantGroupItem::query()->where('item_data_id', $item->id)->first()?->variant_group_id;

        expect($groups)->toHaveCount(2)
            ->and($groupOf($members[0]))->toBe($groupOf($members[1]))->toBe($groupOf($members[2]))
            ->and($groupOf($members[3]))->toBe($groupOf($members[4]))
            ->and($groupOf($members[0]))->not->toBe($groupOf($members[3]));
    });

    it('keeps per-mount rack spellings ungrouped', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-mount-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.MissileLauncher.MissileRack',
                'size' => null,
                'grade' => null,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        // Factory loadout spellings of one rack family: each mount instance
        // keys its own line and none reaches a second member.
        $make('RSI Zeus Missile Launcher', 'MRCK_S04_RSI_Zeus_Left');
        $make('RSI Zeus Missile Launcher', 'MRCK_S04_RSI_Zeus_Right');
        $make('RSI Polaris Torpedo Rack', 'MRCK_S10_RSI_Polaris_Torpedo');
        $make('RSI Polaris Torpedo Rack', 'MRCK_S10_RSI_Polaris_Torpedo_lb');
        $make('ST-205 Missile Rack', 'MRCK_S10_RSI_Polaris_Torpedo_Left');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        expect(VariantGroup::query()->where('game_version_id', $version->id)->count())->toBe(0);
    });

    it('keeps hull-specific spellings and tweak flags off the line', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.2-line-out-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className, string $classification, bool $bespoke = false) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => $classification,
                'size' => null,
                'grade' => null,
                'is_player_relevant' => true,
                'is_bespoke' => $bespoke,
                'data' => [],
            ]);
        };

        $make('AD4B Ballistic Gatling', 'APAR_BallisticGatling_S4', 'Ship.Weapon.Gun');
        $ad6b = $make('AD6B Ballistic Gatling', 'APAR_BallisticGatling_S6', 'Ship.Weapon.Gun');
        // Hull-specific class spellings key their own stem; the bespoke
        // flag itself marks ship-fitted-by-default, which nearly every
        // swappable component carries.
        $bespoke = $make('Revenant Gatling', 'ANVL_BallisticGatling_Bespoke', 'Ship.Weapon.Gun', true);
        // Functional-tweak flags are variant-specific spellings.
        $clean1 = $make('AllStop', 'SHLD_GODI_S01_AllStop_SCItem', 'Ship.Shield');
        $clean2 = $make('AllStop', 'SHLD_GODI_S02_AllStop_SCItem', 'Ship.Shield');
        $tweak = $make('AllStop', 'SHLD_GODI_S01_AllStop_SCItem_ResistGasclouds', 'Ship.Shield');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(2)
            ->and($groups->map(fn ($group) => $group->items()->count())->sort()->values()->all())->toBe([2, 2])
            ->and($ad6b->fresh()->base_id)->not()->toBeNull()
            ->and($bespoke->fresh()->base_id)->toBeNull()
            ->and($tweak->fresh()->base_id)->toBeNull()
            ->and(VariantGroupItem::query()->where('item_data_id', $clean1->id)->exists())->toBeTrue();
    });
});

it('splits sibling armor lines sharing one set tag', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.6-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $antiumUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $antiumUuid, 'name' => 'Antium', 'parent_uuid' => $setUuid]);

    $createItemData = function (string $name, string $className) use ($version, $antiumUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Arms',
            'classification' => 'FPS.Armor.Arms',
            'is_player_relevant' => true,
            'data' => ['entity_tag_map' => [['tag' => $antiumUuid, 'name' => 'Antium']]],
        ]);
    };

    $createItemData('Antium Arms Sand', 'qrt_combat_heavy_arms_01_01_01');
    $createItemData('Antium Arms Storm', 'qrt_combat_heavy_arms_01_01_02');
    $bokto = $createItemData('Bokto Arms', 'qrt_combat_heavy_arms_02_01_01');
    $createItemData('Ana Arms Endro', 'qrt_combat_heavy_arms_03_01_01');
    $createItemData('Ana Arms Wao', 'qrt_combat_heavy_arms_03_02_01');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->orderBy('set_name')->get();
    expect($groups)->toHaveCount(2)
        ->and($groups[0]->set_name)->toBe('Ana')
        ->and($groups[0]->items()->count())->toBe(2)
        ->and($groups[1]->set_name)->toBe('Antium')
        ->and($groups[1]->items()->count())->toBe(2)
        ->and($bokto->fresh()->base_id)->toBeNull();
});

it('labels a duplicate bare armor name verbatim instead of a second Base', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.7-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Arms',
            'classification' => 'FPS.Armor.Arms',
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    $createItemData('TrueDef-Pro Arms', 'vgl_armor_light_arms_01_01_01');
    $createItemData('TrueDef-Pro Arms Red/Silver', 'vgl_armor_light_arms_01_01_02');
    $createItemData('TrueDef-Pro Arms', 'vgl_armor_light_arms_01_01_microtech');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull()
        ->and($group->set_name)->toBe('TrueDef-Pro')
        ->and($group->items()->count())->toBe(3)
        ->and($group->items()->orderBy('variant_name')->pluck('variant_name')->all())
        ->toBe(['Base', 'Red/Silver', 'TrueDef-Pro Arms'])
        ->and($group->items()->where('is_base', true)->count())->toBe(1);
});

it('keeps unparseable armor names on the tag fallback', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.8-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $setUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $setUuid, 'name' => 'Set']);
    $pembrokeUuid = fake()->uuid();
    EntityTag::query()->create(['uuid' => $pembrokeUuid, 'name' => 'RSI', 'parent_uuid' => $setUuid]);

    $createItemData = function (string $name, string $className) use ($version, $pembrokeUuid): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Gameplay',
            'classification' => 'FPS.Armor.Helmet',
            'is_player_relevant' => true,
            'data' => [
                'stdItem' => ['Tags' => ['clda_utility_heavy_helmet', 'Set_01', 'Color_01', 'Helmet']],
                'entity_tag_map' => [['tag' => $pembrokeUuid, 'name' => 'RSI']],
            ],
        ]);
    };

    $createItemData('RSI Ivory Edition', 'clda_utility_heavy_helmet_02_01_01');
    $createItemData('RSI Graphite Edition', 'clda_utility_heavy_helmet_02_01_12');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
    expect($group)->not->toBeNull()
        ->and($group->items()->count())->toBe(2)
        ->and($group->set_name)->toBe('RSI')
        ->and($group->items()->orderBy('variant_name')->pluck('variant_name')->all())
        ->toBe(['Base', 'Graphite Edition']);
});

it('links armor set items by line and keeps the stem swap for line-less names', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.9-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className, string $classification) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => $classification,
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    // One stem, two product lines: Argus helmets, TrueDef-Pro core/arms.
    // The helmet lane has no same-line partner, so the index-matched fill
    // links the stem's TrueDef-Pro body at the same class index.
    $argusHelmet = $createItemData('Argus Helmet', 'vgl_armor_light_helmet_01_01_01', 'FPS.Armor.Helmet');
    $trueDefArms = $createItemData('TrueDef-Pro Arms', 'vgl_armor_light_arms_01_01_01', 'FPS.Armor.Arms');
    $trueDefCore = $createItemData('TrueDef-Pro Core Base', 'vgl_armor_light_core_01_01_01', 'FPS.Armor.Torso');
    // The line rule links the matching backpack like any swap part.
    $trueDefBackpack = $createItemData('TrueDef-Pro Backpack', 'vgl_armor_light_backpack_01_01_01', 'FPS.Armor.Backpack');
    // Line-less names across one stem family keep the stem swap.
    $rsiHelmet = $createItemData('RSI Ivory Edition', 'clda_utility_heavy_helmet_02_01_01', 'FPS.Armor.Helmet');
    $rsiCore = $createItemData('RSI Graphite Edition', 'clda_utility_heavy_core_02_01_01', 'FPS.Armor.Torso');

    new ComputeItemSetItemsJob($version->id)->handle();

    $setItemsOf = function (ItemData $itemData): array {
        return $itemData->setItems()->pluck('name')->sort()->values()->all();
    };

    expect($setItemsOf($argusHelmet->fresh()))->toBe(['TrueDef-Pro Arms', 'TrueDef-Pro Backpack', 'TrueDef-Pro Core Base'])
        ->and($setItemsOf($trueDefArms->fresh()))->toBe(['Argus Helmet', 'TrueDef-Pro Backpack', 'TrueDef-Pro Core Base'])
        ->and($setItemsOf($trueDefCore->fresh()))->toBe(['Argus Helmet', 'TrueDef-Pro Arms', 'TrueDef-Pro Backpack'])
        ->and($setItemsOf($trueDefBackpack->fresh()))->toBe(['Argus Helmet', 'TrueDef-Pro Arms', 'TrueDef-Pro Core Base'])
        ->and($setItemsOf($rsiHelmet->fresh()))->toBe(['RSI Graphite Edition'])
        ->and($setItemsOf($rsiCore->fresh()))->toBe(['RSI Ivory Edition']);
});

it('fills set items across lane-local lines by class index', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.10-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className, string $classification) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => $classification,
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    // One set, two lane-local lines: Balor HCH helmets, ADP body. CIG
    // aligns their colorways by class index, not by line string.
    $balorBlue = $createItemData('Balor HCH Helmet Blue', 'cds_legacy_armor_heavy_helmet_01_01_07', 'FPS.Armor.Helmet');
    $adpCoreBlue = $createItemData('ADP Core Blue', 'cds_legacy_armor_heavy_core_01_01_07', 'FPS.Armor.Torso');
    $adpArmsBlue = $createItemData('ADP Arms Blue', 'cds_legacy_armor_heavy_arms_01_01_07', 'FPS.Armor.Arms');
    $adpLegsBlue = $createItemData('ADP Legs Blue', 'cds_legacy_armor_heavy_legs_01_01_07', 'FPS.Armor.Legs');
    $adpCorePurple = $createItemData('ADP Core Purple', 'cds_legacy_armor_heavy_core_01_01_08', 'FPS.Armor.Torso');

    new ComputeItemSetItemsJob($version->id)->handle();

    $setItemsOf = function (ItemData $itemData): array {
        return $itemData->setItems()->pluck('name')->sort()->values()->all();
    };

    expect($setItemsOf($balorBlue->fresh()))->toBe(['ADP Arms Blue', 'ADP Core Blue', 'ADP Legs Blue'])
        ->and($setItemsOf($adpCoreBlue->fresh()))->toContain('Balor HCH Helmet Blue')
        ->and($setItemsOf($adpCoreBlue->fresh()))->not->toContain('ADP Core Purple');
});

it('culls cross-lane spellings from set item panels', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.12-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className, string $classification) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => $classification,
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    // Helmet and legs SKUs named with the core slot word: linked nowhere.
    $phantomHelmet = $createItemData('ADP-mk4 Core Justified', 'cds_armor_heavy_helmet_02_04_xenothreat', 'FPS.Armor.Helmet');
    $arms = $createItemData('ADP-mk4 Arms Justified', 'cds_armor_heavy_arms_02_04_xenothreat', 'FPS.Armor.Arms');
    $core = $createItemData('ADP-mk4 Core Justified', 'cds_armor_heavy_core_02_04_xenothreat', 'FPS.Armor.Torso');
    $phantomLegs = $createItemData('ADP-mk4 Core Justified', 'cds_armor_heavy_legs_02_04_xenothreat', 'FPS.Armor.Legs');

    new ComputeItemSetItemsJob($version->id)->handle();

    $setItemsOf = function (ItemData $itemData): array {
        return $itemData->setItems()->pluck('name')->sort()->values()->all();
    };

    expect($setItemsOf($phantomHelmet->fresh()))->toBe([])
        ->and($setItemsOf($phantomLegs->fresh()))->toBe([])
        ->and($setItemsOf($arms->fresh()))->toBe(['ADP-mk4 Core Justified']);
});

it('excludes non-relevant shadow SKUs from set items', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.13-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $make = function (string $name, string $className, bool $relevant) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => str_contains($className, '_core_') ? 'FPS.Armor.Torso' : 'FPS.Armor.Arms',
            'is_player_relevant' => $relevant,
            'data' => [],
        ]);
    };

    $arms = $make('Aves Arms', 'ccc_medium_armor_arms_01_01_01', true);
    $make('Aves Core', 'ccc_medium_armor_core_01_01_01', true);
    $make('Aves Core', 'nodraw_ccc_medium_armor_core_01_01_01', false);

    new ComputeItemSetItemsJob($version->id)->handle();

    expect($arms->fresh()->setItems()->pluck('name')->all())->toBe(['Aves Core']);
});

it('resolves edition orphans and sub-line collisions to one partner per lane', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.14-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className, string $classification) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => $classification,
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    // Five name-identical helmets; the microtech undersuit's edition
    // token has no helmet mate, so the numerically indexed base
    // represents the lane. The base undersuit anchors the lane's line.
    $createItemData('Odyssey II Undersuit Alpha', 'rsi_odyssey_undersuit_01_01_01', 'FPS.Armor.Undersuit');
    $undersuit = $createItemData('Odyssey II Undersuit Alpha', 'rsi_odyssey_undersuit_01_01_microtech', 'FPS.Armor.Undersuit');
    foreach (['01', '05', '12', '13', '19'] as $suffix) {
        $createItemData('Odyssey II Helmet Alpha', 'rsi_odyssey_skull_helmet_01_'.$suffix, 'FPS.Armor.Helmet');
    }

    // A backpack between two helmet sub-lines: shared name words decide.
    $backpack = $createItemData('Stirling Exploration Backpack', 'rsi_utility_heavy_backpack_02_01_01', 'FPS.Armor.Backpack');
    $createItemData('Stirling Exploration Helmet', 'clda_utility_heavy_helmet_04_01_01', 'FPS.Armor.Helmet');
    $createItemData('Stirling Hazard Helmet', 'clda_utility_heavy_helmet_06_01_01', 'FPS.Armor.Helmet');

    new ComputeItemSetItemsJob($version->id)->handle();

    $undersuitPanel = $undersuit->fresh()->setItems()->pluck('name')->all();
    expect(count($undersuitPanel))->toBe(1)
        ->and($undersuitPanel[0])->toBe('Odyssey II Helmet Alpha');

    $backpackPanel = $backpack->fresh()->setItems()->pluck('name')->all();
    expect(count($backpackPanel))->toBe(1)
        ->and($backpackPanel[0])->toBe('Stirling Exploration Helmet');
});

it('resolves name-identical shop SKUs to one partner per lane', function (): void {
    $version = GameVersion::query()->create(['code' => '4.10.11-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className, string $classification) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'classification' => $classification,
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    // Shop editions whose names do not carry the edition: three legs
    // SKUs all called "TrueDef-Pro Legs".
    $createItemData('TrueDef-Pro Arms', 'vgl_armor_light_arms_01_01_01', 'FPS.Armor.Arms');
    $armsMicrotech = $createItemData('TrueDef-Pro Arms', 'vgl_armor_light_arms_01_01_microtech', 'FPS.Armor.Arms');
    $createItemData('TrueDef-Pro Core Base', 'vgl_armor_light_core_01_01_01', 'FPS.Armor.Torso');
    $createItemData('TrueDef-Pro Core Base', 'vgl_armor_light_core_01_01_microtech', 'FPS.Armor.Torso');
    $legsBase = $createItemData('TrueDef-Pro Legs', 'vgl_armor_light_legs_01_01_01', 'FPS.Armor.Legs');
    $createItemData('TrueDef-Pro Legs', 'vgl_armor_light_legs_01_01_carrack', 'FPS.Armor.Legs');
    $legsMicrotech = $createItemData('TrueDef-Pro Legs', 'vgl_armor_light_legs_01_01_microtech', 'FPS.Armor.Legs');

    new ComputeItemSetItemsJob($version->id)->handle();

    $setItemsOf = function (ItemData $itemData): array {
        return $itemData->setItems()->pluck('name')->sort()->values()->all();
    };

    $legs = $setItemsOf($legsBase->fresh());
    expect(count($legs))->toBe(2)
        ->and($legs)->toContain('TrueDef-Pro Arms')
        ->and($legs)->toContain('TrueDef-Pro Core Base')
        ->and($legs)->not->toContain('TrueDef-Pro Legs');

    $microtechLegs = $setItemsOf($legsMicrotech->fresh());
    expect(count($microtechLegs))->toBe(2)
        ->and($microtechLegs)->toContain('TrueDef-Pro Arms')
        ->and($microtechLegs)->toContain('TrueDef-Pro Core Base');
});

it('collapses inverted armor grammar onto the shared line', function (): void {
    $version = GameVersion::query()->create(['code' => '4.11.0-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

    $createItemData = function (string $name, string $className) use ($version): ItemData {
        $item = Item::query()->create(['uuid' => fake()->uuid()]);

        return ItemData::query()->create([
            'item_id' => $item->id,
            'game_version_id' => $version->id,
            'manufacturer_id' => $this->manufacturer->id,
            'name' => $name,
            'class_name' => $className,
            'type' => 'Char_Armor_Helmet',
            'classification' => 'FPS.Armor.Helmet',
            'is_player_relevant' => true,
            'data' => [],
        ]);
    };

    // CIG also emits "{Line} {Colorway} Helmet"; the colorway before the
    // slot word must not split the family ("Aztalan Helmet" mixes shapes).
    $createItemData('Aztalan Helmet', 'thp_light_helmet_01_01_01');
    $createItemData('Aztalan Tamarack Helmet', 'thp_light_helmet_01_01_03');
    $createItemData('Aztalan Galena Helmet', 'thp_light_helmet_01_01_12');
    $bokto = $createItemData('Bokto Helmet', 'thp_light_helmet_02_01_01');

    new ComputeItemVariantGroupsJob($version->id)->handle();

    $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
    expect($groups)->toHaveCount(1)
        ->and($groups->first()->set_name)->toBe('Aztalan')
        ->and($groups->first()->items()->orderBy('variant_name')->pluck('variant_name')->all())
        ->toBe(['Base', 'Galena', 'Tamarack'])
        ->and($bokto->fresh()->base_id)->toBeNull();
});

describe('variant grade links', function (): void {
    beforeEach(function (): void {
        $this->version = GameVersion::query()->create([
            'code' => '4.10.2-grade-LIVE',
            'channel' => 'live',
            'released_at' => now(),
            'is_default' => true,
        ]);
    });

    it('links grade peers across stems and for single-size lines', function (): void {
        $make = function (string $name, string $className, int $size, int $grade): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $this->version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'classification' => 'Ship.Cooler',
                'size' => $size,
                'grade' => $grade,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $cryoS0 = $make('Cryo-Star SL', 'COOL_JSPN_S00_CryoStarSL_SCItem', 0, 2);
        $cryoS1 = $make('Cryo-Star', 'COOL_JSPN_S01_CryoStar_SCItem', 1, 2);
        $frostS0 = $make('Frost-Star SL', 'COOL_JSPN_S00_FrostStarSL_SCItem', 0, 3);
        $frostS1 = $make('Frost-Star', 'COOL_JSPN_S01_FrostStar_SCItem', 1, 3);
        // Single-size line: no variant group, but its grade cell still links.
        $winterS0 = $make('Winter-Star SL', 'COOL_JSPN_S00_WinterStarSL_SCItem', 0, 4);

        new ComputeItemVariantGroupsJob($this->version->id)->handle();
        new ComputeItemVariantGradeLinksJob($this->version->id)->handle();

        $peerCount = fn (ItemData $item): int => DB::table('game_item_variant_grade_items')
            ->where('item_data_id', $item->id)
            ->count();

        // Both size cells have one item per grade: 3*2 + 2*1 directed rows.
        expect(DB::table('game_item_variant_grade_items')->count())->toBe(8)
            ->and($peerCount($cryoS0))->toBe(2)
            ->and($peerCount($cryoS1))->toBe(1)
            ->and($peerCount($winterS0))->toBe(2)
            // The single-size line has no group; grade links exist regardless.
            ->and(VariantGroupItem::query()->where('item_data_id', $winterS0->id)->exists())->toBeFalse();
    });

    it('emits no grade links for ship paints', function (): void {
        $makePaint = function (int $grade): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $this->version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => 'Gladius Paint '.$grade,
                'class_name' => 'paint_aegis_gladius_'.$grade,
                'classification' => 'Ship.Paints',
                'size' => 0,
                'grade' => $grade,
                'is_player_relevant' => true,
                'data' => ['stdItem' => ['Tags' => ['Paint_AEGS_Gladius']]],
            ]);
        };

        $makePaint(1);
        $makePaint(2);

        new ComputeItemVariantGradeLinksJob($this->version->id)->handle();

        expect(DB::table('game_item_variant_grade_items')->count())->toBe(0);
    });

    it('skips ambiguous cells whole', function (): void {
        $make = function (int $size, int $grade, string $suffix): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $this->version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => 'Test Power Plant '.$size.' '.$grade.$suffix,
                'class_name' => 'PWR_TEST_S'.$size.'_G'.$grade.$suffix,
                'classification' => 'Ship.PowerPlant.Power',
                'size' => $size,
                'grade' => $grade,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        // Clean cell: one item per grade.
        $make(1, 1, '');
        $make(1, 2, '');

        // Ambiguous cell: a tweak twin duplicates grade 2, so the cell
        // cannot say which spelling represents the grade.
        $twin = $make(0, 2, '');
        $make(0, 2, '_ResistGasclouds');
        $make(0, 1, '');

        new ComputeItemVariantGradeLinksJob($this->version->id)->handle();

        $rows = DB::table('game_item_variant_grade_items')->get();

        expect($rows)->toHaveCount(2)
            ->and($rows->pluck('item_data_id')->unique()->all())->not->toContain($twin->id);
    });

    it('ignores non-player-relevant items in cells', function (): void {
        $make = function (int $grade, bool $relevant): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $this->version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => 'Test Shield '.$grade,
                'class_name' => 'SHLD_TEST_G'.$grade,
                'classification' => 'Ship.Shield',
                'size' => 1,
                'grade' => $grade,
                'is_player_relevant' => $relevant,
                'data' => [],
            ]);
        };

        $make(1, true);
        $make(2, false);

        new ComputeItemVariantGradeLinksJob($this->version->id)->handle();

        expect(DB::table('game_item_variant_grade_items')->count())->toBe(0);
    });

    it('is idempotent when run twice', function (): void {
        $make = function (int $size, int $grade): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $this->version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => 'Test Cooler '.$size.' '.$grade,
                'class_name' => 'COOL_TEST_S'.$size.'_G'.$grade,
                'classification' => 'Ship.Cooler',
                'size' => $size,
                'grade' => $grade,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        foreach ([0, 1] as $size) {
            $make($size, 2);
            $make($size, 3);
        }

        $job = new ComputeItemVariantGradeLinksJob($this->version->id);
        $job->handle();
        $first = DB::table('game_item_variant_grade_items')->count();

        $job->handle();

        expect($first)->toBe(4)
            ->and(DB::table('game_item_variant_grade_items')->count())->toBe($first);
    });
});

describe('spliced fallbacks', function (): void {
    it('groups paint lines without Paint_ tags by class-name family', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.3-paint-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $makePaint = function (string $name, string $className, array $tags) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'type' => 'Paints',
                'classification' => 'Ship.Paints',
                'is_player_relevant' => true,
                'data' => ['stdItem' => ['Tags' => $tags]],
            ]);
        };

        // CIG tags the 300-series hulls "300i_Paint", never "Paint_300";
        // each colorway only adds its unique "@" tag.
        $makePaint('300 Series Citron Paint', 'Paint_300_Series_Solid_Yellow', ['ORIG_300i_Base', 'webcustom', '300i_Paint', '@300_Series_Solid_Yellow']);
        $makePaint('300 Series Electric Blue Paint', 'Paint_300_Series_Solid_Blue', ['ORIG_300i_Base', 'webcustom', '300i_Paint', '@300_Series_Solid_Blue']);
        $makePaint('300 Series Moonlight Paint', 'Paint_300_Series_Metallic_White', ['ORIG_300i_Base', 'webcustom', '300i_Paint', '@300_Series_Metallic_White']);
        $makePaint('300 Series Sterling Paint', 'Paint_300_Series_Metallic_Silver', ['ORIG_300i_Base', 'webcustom', '300i_Paint', '@300_Series_Metallic_Silver']);
        // "Paint_300i_*" has no numeric class anchor and must stay ungrouped.
        $outsider = $makePaint('300 Series Golden Dawn Paint', 'Paint_300i_White_Red_Stripe', ['ORIG_300i_Base', 'webcustom', '300i_Paint', '@300i_White_Red_Stripe']);

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $groups = VariantGroup::query()->where('game_version_id', $version->id)->get();
        expect($groups)->toHaveCount(1)
            ->and($groups->first()->set_name)->toBe('300 Series')
            ->and($groups->first()->items()->count())->toBe(4)
            ->and(VariantGroupItem::query()->where('item_data_id', $outsider->id)->exists())->toBeFalse()
            ->and($outsider->fresh()->base_id)->toBeNull();
    });

    it('derives a clothing set name from the member name prefix', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.3-clothing-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $makeShoes = function (string $name, string $className) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'type' => 'Char_Clothing_Feet',
                'classification' => 'FPS.Clothing.Shoes',
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        $makeShoes('Prim Shoes', 'fio_shoes_01_01_01');
        $makeShoes('Prim Shoes Aqua', 'fio_shoes_01_01_02');
        $makeShoes('Prim Shoes Black', 'fio_shoes_01_01_03');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $group = VariantGroup::query()->where('game_version_id', $version->id)->first();
        expect($group)->not->toBeNull()
            ->and($group->set_name)->toBe('Prim Shoes')
            ->and($group->items()->count())->toBe(3);
    });

    it('joins slot-word-less armor names into the family their line extends', function (): void {
        $version = GameVersion::query()->create(['code' => '4.10.3-armor-LIVE', 'channel' => 'live', 'released_at' => now(), 'is_default' => true]);

        $make = function (string $name, string $className, string $type, string $classification) use ($version): ItemData {
            return ItemData::query()->create([
                'item_id' => Item::query()->create(['uuid' => fake()->uuid()])->id,
                'game_version_id' => $version->id,
                'manufacturer_id' => $this->manufacturer->id,
                'name' => $name,
                'class_name' => $className,
                'type' => $type,
                'classification' => $classification,
                'is_player_relevant' => true,
                'data' => [],
            ]);
        };

        // Torso lane: two anchored "Carnifex Armor" colorways, one relaxed
        // "Carnifex" line, and the slot-word-less stl edition.
        $core = $make('Carnifex Armor Core', 'gys_jacket_01_01_01', 'Char_Armor_Torso', 'FPS.Armor.Torso');
        $make('Carnifex Armor Core Brassy', 'gys_jacket_01_01_14', 'Char_Armor_Torso', 'FPS.Armor.Torso');
        $make('Carnifex Core Windfall', 'gys_jacket_stl01_01_01', 'Char_Armor_Torso', 'FPS.Armor.Torso');
        $luckyBreak = $make('Carnifex Armor Lucky Break', 'gys_jacket_stl01_02_01', 'Char_Armor_Torso', 'FPS.Armor.Torso');

        // Legs lane: "Carnifex Pants Windfall" extends no anchored line.
        $make('Carnifex Armor Legs', 'gys_pants_01_01_01', 'Char_Armor_Legs', 'FPS.Armor.Legs');
        $make('Carnifex Armor Legs Brassy', 'gys_pants_01_01_14', 'Char_Armor_Legs', 'FPS.Armor.Legs');
        $pantsWindfall = $make('Carnifex Pants Windfall', 'gys_pants_stl01_01_01', 'Char_Armor_Legs', 'FPS.Armor.Legs');

        new ComputeItemVariantGroupsJob($version->id)->handle();

        $torsoGroup = VariantGroup::query()->where('game_version_id', $version->id)
            ->whereHas('items', fn ($query) => $query->where('item_data_id', $core->id))
            ->first();

        expect($torsoGroup)->not->toBeNull()
            ->and($torsoGroup->set_name)->toBe('Carnifex Armor')
            ->and($torsoGroup->items()->count())->toBe(4)
            ->and($torsoGroup->items()->firstWhere('item_data_id', $luckyBreak->id))->not->toBeNull()
            ->and($torsoGroup->items()->firstWhere('item_data_id', $core->id)->is_base)->toBeTrue();

        $legsGroups = VariantGroupItem::query()->whereIn('item_data_id', [$pantsWindfall->id])->exists();
        expect($legsGroups)->toBeFalse()
            ->and($pantsWindfall->fresh()->base_id)->toBeNull();
    });
});
