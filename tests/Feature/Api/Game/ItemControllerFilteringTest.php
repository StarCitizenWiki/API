<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
