<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
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
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('returns entity tags and entity tag map when tags are attached', function (): void {
    $tag1 = EntityTag::factory()->create(['name' => 'Tag One']);
    $tag2 = EntityTag::factory()->create(['name' => 'Tag Two']);

    $item = Item::factory()->create();

    $itemData = ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item',
            'type' => 'TestType',
            'class_name' => 'test_item',
            'classification' => 'Test.Category',
            'data' => ['stdItem' => []],
        ]);

    $itemData->entityTags()->attach([$tag1->id, $tag2->id]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful();

    $expectedEntityTagMap = [
        [
            'uuid' => $tag1->uuid,
            'name' => 'Tag One',
        ],
        [
            'uuid' => $tag2->uuid,
            'name' => 'Tag Two',
        ],
    ];

    $expectedEntityTags = [
        $tag1->uuid,
        $tag2->uuid,
    ];

    expect(collect($response->json('data.entity_tag_map'))->sortBy('uuid')->values()->all())->toBe(
        collect($expectedEntityTagMap)->sortBy('uuid')->values()->all()
    )->and(collect($response->json('data.entity_tags'))->sort()->values()->all())->toBe(
        collect($expectedEntityTags)->sort()->values()->all()
    );
});

it('returns empty array when no entity tags are attached', function (): void {
    $item = Item::factory()->create();

    ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item Without Tags',
            'type' => 'TestType',
            'class_name' => 'test_item_no_tags',
            'classification' => 'Test.Category',
            'data' => ['stdItem' => []],
        ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.entity_tag_map', [])
        ->assertJsonPath('data.entity_tags', []);
});
