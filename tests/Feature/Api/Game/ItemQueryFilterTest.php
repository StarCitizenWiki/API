<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters items by query matching name', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Filter Co',
        'code' => 'FILTER',
    ]);

    $match = Item::factory()->create();
    ItemData::factory()
        ->for($match)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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
    $version = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Filter Co',
        'code' => 'FILTER',
    ]);

    $match = Item::factory()->create();
    ItemData::factory()
        ->for($match)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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
    $version = GameVersion::factory()->create([
        'code' => '3.24.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Filter Co',
        'code' => 'FILTER',
    ]);

    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
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
