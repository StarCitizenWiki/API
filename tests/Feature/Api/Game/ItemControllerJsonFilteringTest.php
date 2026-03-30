<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::factory()->create([
        'name' => 'JSON Filters',
        'code' => 'JSON',
    ]);
});

it('sorts items by json mass ascending and places null values last', function (): void {
    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Zulu Item',
            'type' => 'Widget',
            'class_name' => 'zulu_item',
            'classification' => 'Test.Widget',
            'data' => [
                'stdItem' => [
                    'Mass' => 50.0,
                ],
            ],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Item',
            'type' => 'Widget',
            'class_name' => 'alpha_item',
            'classification' => 'Test.Widget',
            'data' => [
                'stdItem' => [
                    'Mass' => 150.0,
                ],
            ],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Hotel Item',
            'type' => 'Widget',
            'class_name' => 'hotel_item',
            'classification' => 'Test.Widget',
            'data' => [
                'stdItem' => [],
            ],
        ]);

    $response = $this->getJson('/api/items?sort=Mass');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Zulu Item', 'Alpha Item', 'Hotel Item']);
})->group('db-pgsql');

it('sorts items by json mass descending and places null values last', function (): void {
    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Zulu Item',
            'type' => 'Widget',
            'class_name' => 'zulu_item',
            'classification' => 'Test.Widget',
            'data' => [
                'stdItem' => [
                    'Mass' => 50.0,
                ],
            ],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Alpha Item',
            'type' => 'Widget',
            'class_name' => 'alpha_item',
            'classification' => 'Test.Widget',
            'data' => [
                'stdItem' => [
                    'Mass' => 150.0,
                ],
            ],
        ]);

    ItemData::factory()
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Hotel Item',
            'type' => 'Widget',
            'class_name' => 'hotel_item',
            'classification' => 'Test.Widget',
            'data' => [
                'stdItem' => [],
            ],
        ]);

    $response = $this->getJson('/api/items?sort=-Mass');

    $response->assertSuccessful();

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Alpha Item', 'Zulu Item', 'Hotel Item']);
})->group('db-pgsql');
