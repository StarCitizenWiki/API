<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->gameVersion = GameVersion::create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->manufacturer = Manufacturer::create([
        'uuid' => 'manufacturer-test',
        'name' => 'Test Manufacturer',
        'code' => 'TEST',
    ]);
});

it('returns entity tag map with correct structure when tags are attached', function () {
    $tag1 = EntityTag::create([
        'uuid' => 'tag-uuid-1',
        'name' => 'Tag One',
    ]);

    $tag2 = EntityTag::create([
        'uuid' => 'tag-uuid-2',
        'name' => 'Tag Two',
    ]);

    $item = Item::create(['uuid' => 'test-item-uuid']);

    $itemData = ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item',
        'type' => 'TestType',
        'class_name' => 'test_item',
        'classification' => 'Test.Category',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $itemData->entityTags()->attach([$tag1->id, $tag2->id]);

    $response = $this->getJson("/api/items/{$item->uuid}?include=entityTags");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'entity_tag_map' => [
                    '*' => [
                        'uuid',
                        'name',
                    ],
                ],
            ],
        ])
        ->assertJsonCount(2, 'data.entity_tag_map')
        ->assertJsonCount(2, 'data.entity_tags');

    $entityTagMap = $response->json('data.entity_tag_map');
    expect($entityTagMap)->toBeArray();
    expect($entityTagMap)->toHaveCount(2);

    $uuids = collect($entityTagMap)->pluck('uuid')->toArray();
    $names = collect($entityTagMap)->pluck('name')->toArray();

    expect($uuids)->toContain('tag-uuid-1', 'tag-uuid-2');
    expect($names)->toContain('Tag One', 'Tag Two');

    $entityTags = $response->json('data.entity_tags');
    expect($entityTags)->toBeArray();
    expect($entityTags)->toContain('Tag One', 'Tag Two');
});

it('returns empty array when no entity tags are attached', function () {
    $item = Item::create(['uuid' => 'test-item-no-tags']);

    ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item Without Tags',
        'type' => 'TestType',
        'class_name' => 'test_item_no_tags',
        'classification' => 'Test.Category',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.entity_tag_map', [])
        ->assertJsonPath('data.entity_tags', []);
});

it('returns correct uuid and name values for entity tags', function () {
    $tag = EntityTag::create([
        'uuid' => 'specific-tag-uuid-123',
        'name' => 'Specific Tag Name',
    ]);

    $item = Item::create(['uuid' => 'test-item-specific']);

    $itemData = ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item',
        'type' => 'TestType',
        'class_name' => 'test_item',
        'classification' => 'Test.Category',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $itemData->entityTags()->attach($tag->id);

    $response = $this->getJson("/api/items/{$item->uuid}?include=entityTags");

    $response->assertSuccessful()
        ->assertJsonPath('data.entity_tag_map.0.uuid', 'specific-tag-uuid-123')
        ->assertJsonPath('data.entity_tag_map.0.name', 'Specific Tag Name');
});

it('returns entity tag map for items with multiple tags in correct format', function () {
    $tags = [];
    for ($i = 1; $i <= 5; $i++) {
        $tags[] = EntityTag::create([
            'uuid' => "tag-uuid-{$i}",
            'name' => "Tag {$i}",
        ]);
    }

    $item = Item::create(['uuid' => 'test-item-multiple']);

    $itemData = ItemData::create([
        'item_id' => $item->id,
        'game_version_id' => $this->gameVersion->id,
        'name' => 'Test Item Multiple Tags',
        'type' => 'TestType',
        'class_name' => 'test_item_multiple',
        'classification' => 'Test.Category',
        'manufacturer_id' => $this->manufacturer->id,
        'data' => ['stdItem' => []],
    ]);

    $itemData->entityTags()->attach(collect($tags)->pluck('id')->toArray());

    $response = $this->getJson("/api/items/{$item->uuid}?include=entityTags");

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data.entity_tag_map');

    $entityTagMap = $response->json('data.entity_tag_map');

    foreach ($entityTagMap as $tagData) {
        expect($tagData)->toHaveKeys(['uuid', 'name']);
        expect($tagData['uuid'])->toBeString();
        expect($tagData['name'])->toBeString();
    }
});
