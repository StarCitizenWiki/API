<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('filters items by type on the web route', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);

    $widget = Item::factory()->create();
    ItemData::factory()
        ->for($widget)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Widget One',
            'type' => 'Widget',
            'class_name' => 'widget_one',
            'classification' => 'Test',
            'data' => [],
        ]);

    $gadget = Item::factory()->create();
    ItemData::factory()
        ->for($gadget)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Gadget One',
            'type' => 'Gadget',
            'class_name' => 'gadget_one',
            'classification' => 'Test',
            'data' => [],
        ]);

    $response = $this->get(route('web.items.type', ['type' => 'Widget']));

    $response->assertOk()
        ->assertViewIs('items.index')
        ->assertViewHas('initialTableData', function (array $payload) use ($widget): bool {
            return ($payload['data'][0]['uuid'] ?? null) === $widget->uuid
                && count($payload['data']) === 1;
        });
});
