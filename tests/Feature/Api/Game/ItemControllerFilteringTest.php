<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme',
        'code' => 'ACME',
    ]);
});

it('filters items by type and manufacturer', function (): void {
    $otherManufacturer = Manufacturer::factory()->create([
        'name' => 'Other',
        'code' => 'OTHER',
    ]);

    $match = Item::factory()->create();
    ItemData::factory()
        ->for($match)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Widget One',
            'type' => 'Widget',
            'class_name' => 'WidgetOne',
            'classification' => 'Test',
            'data' => [],
        ]);

    $typeOnly = Item::factory()->create();
    ItemData::factory()
        ->for($typeOnly)
        ->for($this->gameVersion, 'gameVersion')
        ->for($otherManufacturer)
        ->create([
            'name' => 'Widget Two',
            'type' => 'Widget',
            'class_name' => 'WidgetTwo',
            'classification' => 'Test',
            'data' => [],
        ]);

    $manufacturerOnly = Item::factory()->create();
    ItemData::factory()
        ->for($manufacturerOnly)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Gadget One',
            'type' => 'Gadget',
            'class_name' => 'GadgetOne',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?filter[type]=Widget&filter[manufacturer]=Acme');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);

    $response = $this->getJson('/api/items?filter[type]=Widget&filter[manufacturer]=ACME');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);
});

it('filters items by variants flag', function (): void {
    $baseItem = Item::factory()->create();
    $baseData = ItemData::factory()
        ->for($baseItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Base Widget',
            'type' => 'Widget',
            'class_name' => 'WidgetBase',
            'classification' => 'Test',
            'data' => [],
        ]);

    $variantItem = Item::factory()->create();
    ItemData::factory()
        ->for($variantItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Variant Widget',
            'type' => 'Widget',
            'class_name' => 'WidgetVariant',
            'classification' => 'Test',
            'base_id' => $baseData->id,
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?filter[variants]=false');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $baseItem->uuid);
});

it('filters items by category', function (): void {
    $foodItem = Item::factory()->create();
    ItemData::factory()
        ->for($foodItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Fruit Snack',
            'type' => 'Food',
            'class_name' => 'FoodSnack',
            'classification' => 'Test',
            'data' => [],
        ]);

    $weaponItem = Item::factory()->create();
    ItemData::factory()
        ->for($weaponItem)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Laser Pistol',
            'type' => 'WeaponPersonal',
            'class_name' => 'WeaponPistol',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?filter[category]=food');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $foodItem->uuid);
});

it('ignores unknown filters', function (): void {
    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Unknown Widget',
            'type' => 'Widget',
            'class_name' => 'UnknownWidget',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?filter[unknown]=value');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $item->uuid);
});

it('filters items by size', function (): void {
    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Size One Alpha',
            'type' => 'Widget',
            'class_name' => 'size_one_alpha',
            'classification' => 'Test.Size',
            'size' => 1,
            'grade' => 2,
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Size Two Beta',
            'type' => 'Widget',
            'class_name' => 'size_two_beta',
            'classification' => 'Test.Size',
            'size' => 2,
            'grade' => 2,
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Size One Gamma',
            'type' => 'Widget',
            'class_name' => 'size_one_gamma',
            'classification' => 'Test.Size',
            'size' => 1,
            'grade' => 3,
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items?filter[size]=1');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Size One Alpha', 'Size One Gamma']);
});

it('filters items by grade', function (): void {
    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Grade Two Alpha',
            'type' => 'Widget',
            'class_name' => 'grade_two_alpha',
            'classification' => 'Test.Grade',
            'size' => 1,
            'grade' => 2,
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Grade Four Beta',
            'type' => 'Widget',
            'class_name' => 'grade_four_beta',
            'classification' => 'Test.Grade',
            'size' => 1,
            'grade' => 4,
            'data' => ['stdItem' => []],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Grade Two Gamma',
            'type' => 'Widget',
            'class_name' => 'grade_two_gamma',
            'classification' => 'Test.Grade',
            'size' => 2,
            'grade' => 2,
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson('/api/items?filter[grade]=2');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Grade Two Alpha', 'Grade Two Gamma']);
});

it('filters items by name and class_name', function (): void {
    $match = Item::factory()->create();
    ItemData::factory()
        ->for($match)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Widget',
            'type' => 'Widget',
            'class_name' => 'alpha_widget_class',
            'classification' => 'Test',
            'data' => [],
        ]);

    $other = Item::factory()->create();
    ItemData::factory()
        ->for($other)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Beta Widget',
            'type' => 'Widget',
            'class_name' => 'beta_widget_class',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->getJson('/api/items?filter[name]=Alpha');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $match->uuid);

    $response = $this->getJson('/api/items?filter[class_name]=beta');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.uuid', $other->uuid);
});

describe('query filter', function (): void {
    it('filters items by query matching name', function (): void {
        $match = Item::factory()->create();
        ItemData::factory()
            ->for($match)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Alpha Widget',
                'type' => 'Widget',
                'class_name' => 'alpha_widget_class',
                'classification' => 'Test',
                'data' => [],
            ]);

        $other = Item::factory()->create();
        ItemData::factory()
            ->for($other)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Beta Widget',
                'type' => 'Widget',
                'class_name' => 'beta_widget_class',
                'classification' => 'Test',
                'data' => [],
            ]);

        $response = $this->getJson('/api/items?filter[query]=Alpha');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $match->uuid);
    });

    it('filters items by query matching class_name', function (): void {
        $match = Item::factory()->create();
        ItemData::factory()
            ->for($match)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Gamma Widget',
                'type' => 'Widget',
                'class_name' => 'gamma_class',
                'classification' => 'Test',
                'data' => [],
            ]);

        $other = Item::factory()->create();
        ItemData::factory()
            ->for($other)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Delta Widget',
                'type' => 'Widget',
                'class_name' => 'delta_class',
                'classification' => 'Test',
                'data' => [],
            ]);

        $response = $this->getJson('/api/items?filter[query]=gamma_class');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $match->uuid);
    });

    it('returns empty when query matches nothing', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Existing Item',
                'type' => 'Widget',
                'class_name' => 'existing_class',
                'classification' => 'Test',
                'data' => [],
            ]);

        $response = $this->getJson('/api/items?filter[query]=zzznonexistent');

        $response->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });
});

describe('tags filter', function (): void {
    it('filters items by a single tag', function (): void {
        $match = Item::factory()->create();
        ItemData::factory()
            ->for($match)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Tagged Item',
                'type' => 'Widget',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['Dock_Command_Module', 'Ship_Dock_Refuel'],
                    ],
                ],
            ]);

        $other = Item::factory()->create();
        ItemData::factory()
            ->for($other)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Other Item',
                'type' => 'Widget',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'Tags' => ['Something_Else'],
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        $response = $this->getJson('/api/items?filter[tags]=Dock_Command_Module');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $match->uuid);
    });

    it('filters items by multiple tags with AND logic', function (): void {
        $match = Item::factory()->create();
        ItemData::factory()
            ->for($match)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Both Tags Item',
                'type' => 'Widget',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['Dock_Command_Module', 'Ship_Dock_Refuel'],
                    ],
                ],
            ]);

        $partial = Item::factory()->create();
        ItemData::factory()
            ->for($partial)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Only One Tag Item',
                'type' => 'Widget',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['Dock_Command_Module'],
                    ],
                ],
            ]);

        $response = $this->getJson('/api/items?filter[tags]=Dock_Command_Module,Ship_Dock_Refuel');

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $match->uuid);
    });

    it('returns empty when no items match tag', function (): void {
        $item = Item::factory()->create();
        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'No Tag Item',
                'type' => 'Widget',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['Unrelated_Tag'],
                    ],
                ],
            ]);

        $response = $this->getJson('/api/items?filter[tags]=Nonexistent_Tag');

        $response->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });
});

describe('rarity filter', function (): void {
    it('filters items by rarity', function (): void {
        $rareItem = Item::factory()->create();
        ItemData::factory()
            ->for($rareItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Rare Item',
                'type' => 'Weapon',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'Rarity' => 'Rare',
                    ],
                ],
                'rarity' => 'Rare',
            ]);

        $commonItem = Item::factory()->create();
        ItemData::factory()
            ->for($commonItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Common Item',
                'type' => 'Weapon',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'Rarity' => 'Common',
                    ],
                ],
                'rarity' => 'Common',
            ]);

        $response = $this->getJson('/api/items?filter[rarity]=Rare');
        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $rareItem->uuid);
    });
});

describe('filter[port_tags]', function (): void {
    it('excludes items with no RequiredTags and no matching Tags', function (): void {
        $universal = Item::factory()->create();
        ItemData::factory()
            ->for($universal)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Universal Turret',
                'type' => 'Turret',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                        'Tags' => ['Unrelated_Tag'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[port_tags]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    it('includes items with no RequiredTags but matching Tags', function (): void {
        $paint = Item::factory()->create();
        ItemData::factory()
            ->for($paint)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => '300 Series Paint',
                'type' => 'Paints',
                'classification' => 'Ship.Paints',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                        'Tags' => ['ORIG_300i_Base', '300i_Paint'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[port_tags]=ORIG_300i_Base')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $paint->uuid);
    });

    it('excludes items with no RequiredTags and non-matching Tags', function (): void {
        $wrongPaint = Item::factory()->create();
        ItemData::factory()
            ->for($wrongPaint)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Caterpillar Paint',
                'type' => 'Paints',
                'classification' => 'Ship.Paints',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                        'Tags' => ['DRAK_Caterpillar_Base', 'Caterpillar_Paint'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[port_tags]=ORIG_300i_Base')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    it('includes items whose RequiredTags match the port tags', function (): void {
        $matched = Item::factory()->create();
        ItemData::factory()
            ->for($matched)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Avenger Turret',
                'type' => 'Turret',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['AEGS_Avenger_Base'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[port_tags]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $matched->uuid);
    });

    it('excludes items whose RequiredTags do not match the port tags', function (): void {
        $polaris = Item::factory()->create();
        ItemData::factory()
            ->for($polaris)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Polaris Turret',
                'type' => 'Turret',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['RSI_Polaris'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[port_tags]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    it('includes items when any RequiredTag matches (any-match, not subset)', function (): void {
        // In practice SC items have 0 or 1 RequiredTags. This test documents
        // the current limitation: multi-tag items match if ANY RequiredTag is
        // in the port tags (not an exact subset check).
        $partial = Item::factory()->create();
        ItemData::factory()
            ->for($partial)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Partial Match',
                'type' => 'Turret',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['AEGS_Avenger_Base', 'SomeOtherTag'],
                    ],
                ],
            ]);

        // Port only has AEGS_Avenger_Base - item matches because one of its
        // RequiredTags is present (any-match, not subset).
        $this->getJson('/api/items?filter[port_tags]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $partial->uuid);
    });
});

describe('filter[vehicle]', function (): void {
    it('shows universal items that have no RequiredTags and are not bespoke', function (): void {
        $universal = Item::factory()->create();
        ItemData::factory()
            ->for($universal)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Universal Missile Rack',
                'type' => 'MissileLauncher',
                'classification' => 'Test',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $universal->uuid);
    });

    it('excludes bespoke items with no RequiredTags from the universal branch', function (): void {
        // Bespoke item with no RequiredTags
        $bespoke = Item::factory()->create();
        ItemData::factory()
            ->for($bespoke)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Hull B Missile Rack',
                'type' => 'MissileLauncher',
                'classification' => 'Test',
                'is_bespoke' => true,
                'bespoke_vehicle_tags' => ['MISC_Hull_B'],
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    it('shows bespoke items whose RequiredTags match the vehicle context', function (): void {
        $bespoke = Item::factory()->create();
        ItemData::factory()
            ->for($bespoke)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Avenger Turret',
                'type' => 'Turret',
                'classification' => 'Test',
                'is_bespoke' => true,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['AEGS_Avenger_Base'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $bespoke->uuid);
    });

    it('shows bespoke items whose bespoke_vehicle_tags match the vehicle context', function (): void {
        // Bespoke item with no RequiredTags but matching bespoke_vehicle_tags
        $bespoke = Item::factory()->create();
        ItemData::factory()
            ->for($bespoke)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Hull B Missile Rack',
                'type' => 'MissileLauncher',
                'classification' => 'Test',
                'is_bespoke' => true,
                'bespoke_vehicle_tags' => ['MISC_Hull_B'],
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=MISC_Hull_B')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $bespoke->uuid);
    });

    it('excludes bespoke items whose bespoke_vehicle_tags do not match', function (): void {
        $bespoke = Item::factory()->create();
        ItemData::factory()
            ->for($bespoke)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Hull B Missile Rack',
                'type' => 'MissileLauncher',
                'classification' => 'Test',
                'is_bespoke' => true,
                'bespoke_vehicle_tags' => ['MISC_Hull_B'],
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    it('excludes bespoke items whose RequiredTags do not match the vehicle context', function (): void {
        $furyRack = Item::factory()->create();
        ItemData::factory()
            ->for($furyRack)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Fury Missile Rack',
                'type' => 'MissileLauncher',
                'classification' => 'Test',
                'is_bespoke' => true,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['MISC_Fury_Miru'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(0, 'data');
    });

    it('shows universal and matching bespoke items together', function (): void {
        $universal = Item::factory()->create();
        ItemData::factory()
            ->for($universal)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Universal Rack',
                'type' => 'MissileLauncher',
                'classification' => 'Test',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => [],
                    ],
                ],
            ]);

        $bespoke = Item::factory()->create();
        ItemData::factory()
            ->for($bespoke)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Avenger Specific Part',
                'type' => 'Turret',
                'classification' => 'Test',
                'is_bespoke' => true,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['AEGS_Avenger_Base'],
                    ],
                ],
            ]);

        $wrongBespoke = Item::factory()->create();
        ItemData::factory()
            ->for($wrongBespoke)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Fury Rack',
                'type' => 'MissileLauncher',
                'classification' => 'Test',
                'is_bespoke' => true,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['MISC_Fury_Miru'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(2, 'data');
    });

    it('matches when any vehicle context tag hits any RequiredTag', function (): void {
        // Hurricane has multiple identity tags
        $bespoke = Item::factory()->create();
        ItemData::factory()
            ->for($bespoke)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Cutlass Part',
                'type' => 'Turret',
                'classification' => 'Test',
                'is_bespoke' => true,
                'data' => [
                    'stdItem' => [
                        'RequiredTags' => ['DRAK_Cutlass_Base'],
                    ],
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=ANVL_Hurricane,DRAK_Cutlass_Base')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $bespoke->uuid);
    });

    it('includes items with null RequiredTags as universal', function (): void {
        $noTags = Item::factory()->create();
        ItemData::factory()
            ->for($noTags)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'No RequiredTags Field',
                'type' => 'Cooler',
                'classification' => 'Test',
                'is_bespoke' => false,
                'data' => [
                    'stdItem' => new stdClass, // no RequiredTags key at all
                ],
            ]);

        $this->getJson('/api/items?filter[vehicle]=AEGS_Avenger_Base')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.uuid', $noTags->uuid);
    });
});
