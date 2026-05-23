<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

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

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');

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

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data');

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Alpha Item', 'Zulu Item', 'Hotel Item']);
})->group('db-pgsql');

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
            'data' => [
                'stdItem' => [],
            ],
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
            'data' => [
                'stdItem' => [],
            ],
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
            'data' => [
                'stdItem' => [],
            ],
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
            'data' => [
                'stdItem' => [],
            ],
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
            'data' => [
                'stdItem' => [],
            ],
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
            'data' => [
                'stdItem' => [],
            ],
        ]);

    $response = $this->getJson('/api/items?filter[grade]=2');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');

    expect(collect($response->json('data'))->pluck('name')->all())
        ->toBe(['Grade Two Alpha', 'Grade Two Gamma']);
});
