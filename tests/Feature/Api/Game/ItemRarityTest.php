<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('includes rarity in item response when present in stdItem', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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

it('filters items by rarity', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    $rareItem = Item::factory()->create();
    ItemData::factory()
        ->for($rareItem)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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

it('includes rarity facet in filters endpoint', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create();

    foreach (['Common', 'Rare', 'Rare'] as $rarity) {
        ItemData::factory()
            ->for(Item::factory(), 'item')
            ->for($version, 'gameVersion')
            ->for($manufacturer)
            ->create([
                'name' => fake()->word(),
                'type' => 'Weapon',
                'classification' => 'Test',
                'data' => [
                    'stdItem' => [
                        'Rarity' => $rarity,
                    ],
                ],
                'rarity' => $rarity,
            ]);
    }

    $response = $this->getJson(route('items.filters'));
    $response->assertOk();

    $rarityFilters = collect($response->json('filters.rarity'));

    $common = $rarityFilters->first(fn (array $f) => $f['value'] === 'Common');
    $rare = $rarityFilters->first(fn (array $f) => $f['value'] === 'Rare');

    expect($common)->not->toBeNull()
        ->and($common['count'])->toBe(1)
        ->and($rare)->not->toBeNull()
        ->and($rare['count'])->toBe(2);
});
