<?php

declare(strict_types=1);

uses(Tests\TestCase::class);
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

it('can create an entity tag', function (): void {
    $tag = EntityTag::query()->create([
        'uuid' => 'test-uuid-123',
        'name' => 'TestTag',
    ]);

    expect($tag->uuid)->toBe('test-uuid-123');
    expect($tag->name)->toBe('TestTag');
});

it('enforces unique uuid constraint', function (): void {
    EntityTag::query()->create([
        'uuid' => 'duplicate-uuid',
        'name' => 'First Tag',
    ]);

    expect(function () {
        EntityTag::query()->create([
            'uuid' => 'duplicate-uuid',
            'name' => 'Second Tag',
        ]);
    })->toThrow(Exception::class);
});

it('has many-to-many relationship with item data', function (): void {
    $version = GameVersion::query()->create([
        'code' => '3.24.0',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);

    $manufacturer = Manufacturer::query()->create([
        'uuid' => 'uuid-manu',
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);

    $item = Item::query()->create(['uuid' => 'item-uuid']);

    $itemData = ItemData::query()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test Item',
        'class_name' => 'test_class',
        'data' => [],
    ]);

    $tag1 = EntityTag::query()->create([
        'uuid' => 'tag-uuid-1',
        'name' => 'Tag1',
    ]);

    $tag2 = EntityTag::query()->create([
        'uuid' => 'tag-uuid-2',
        'name' => 'Tag2',
    ]);

    $itemData->entityTags()->attach([$tag1->id, $tag2->id]);

    expect($itemData->entityTags)->toHaveCount(2);
    expect($tag1->itemData)->toHaveCount(1);
    expect($tag1->itemData->first()->id)->toBe($itemData->id);
});
