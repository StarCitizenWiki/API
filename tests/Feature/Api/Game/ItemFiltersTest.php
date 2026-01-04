<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns item filter values with counts', function (): void {
    $version = GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $manufacturer = Manufacturer::factory()->create([
        'name' => 'Acme',
        'code' => 'ACME',
    ]);

    $item = Item::factory()->create();
    ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create([
            'name' => 'Pulse Rifle',
            'type' => 'Weapon',
            'sub_type' => 'Laser',
            'classification' => 'FPS.Weapon',
            'size' => 1,
            'grade' => 2,
            'class' => 'A',
            'data' => [],
        ]);

    $unknownManufacturer = Manufacturer::factory()->create([
        'name' => 'Nova',
        'code' => 'NOVA',
    ]);

    $unknown = Item::factory()->create();
    ItemData::factory()
        ->for($unknown)
        ->for($version, 'gameVersion')
        ->for($unknownManufacturer)
        ->create([
            'name' => 'Mystery Item',
            'type' => null,
            'sub_type' => null,
            'classification' => null,
            'size' => null,
            'grade' => null,
            'class' => null,
            'data' => [],
        ]);

    $response = $this->getJson('/api/items/filters');

    $response->assertSuccessful();

    $filters = $response->json('filters');

    expect(collect($filters['type'])->contains(fn (array $row) => $row['value'] === 'Weapon' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['type'])->contains(fn (array $row) => $row['value'] === null && $row['label'] === 'Unknown'))->toBeTrue()
        ->and(collect($filters['manufacturer'])->contains(fn (array $row) => $row['value'] === 'Acme' && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['size'])->contains(fn (array $row) => $row['value'] === 1 && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['grade'])->contains(fn (array $row) => $row['value'] === 2 && $row['count'] === 1))->toBeTrue()
        ->and(collect($filters['class'])->contains(fn (array $row) => $row['value'] === 'A' && $row['count'] === 1))->toBeTrue();
});
