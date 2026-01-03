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

it('returns entity tag map with correct structure when tags are attached', function () {
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
    expect($entityTagMap)->toBeArray()
        ->and($entityTagMap)->toHaveCount(2);

    $uuids = collect($entityTagMap)->pluck('uuid')->toArray();
    $names = collect($entityTagMap)->pluck('name')->toArray();

    expect($uuids)->toContain($tag1->uuid, $tag2->uuid)
        ->and($names)->toContain('Tag One', 'Tag Two');

    $entityTags = $response->json('data.entity_tags');
    expect($entityTags)->toBeArray()
        ->and($entityTags)->toContain($tag1->uuid, $tag2->uuid);
});

it('returns empty array when no entity tags are attached', function () {
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

it('returns correct uuid and name values for entity tags', function () {
    $tag = EntityTag::factory()->create(['name' => 'Specific Tag Name']);

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

    $itemData->entityTags()->attach($tag->id);

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.entity_tag_map.0.uuid', $tag->uuid)
        ->assertJsonPath('data.entity_tag_map.0.name', 'Specific Tag Name');
});

it('returns entity tag map for items with multiple tags in correct format', function () {
    $tags = [];
    for ($i = 1; $i <= 5; $i++) {
        $tags[] = EntityTag::factory()->create(['name' => "Tag {$i}"]);
    }

    $item = Item::factory()->create();

    $itemData = ItemData::factory()
        ->for($item)
        ->for($this->gameVersion, 'gameVersion')
        ->for($this->manufacturer)
        ->create([
            'name' => 'Test Item Multiple Tags',
            'type' => 'TestType',
            'class_name' => 'test_item_multiple',
            'classification' => 'Test.Category',
            'data' => ['stdItem' => []],
        ]);

    $itemData->entityTags()->attach(collect($tags)->pluck('id')->toArray());

    $response = $this->getJson("/api/items/{$item->uuid}");

    $response->assertSuccessful()
        ->assertJsonCount(5, 'data.entity_tag_map');

    $entityTagMap = $response->json('data.entity_tag_map');

    foreach ($entityTagMap as $tagData) {
        expect($tagData)->toHaveKeys(['uuid', 'name'])
            ->and($tagData['uuid'])->toBeString()
            ->and($tagData['name'])->toBeString();
    }
});
