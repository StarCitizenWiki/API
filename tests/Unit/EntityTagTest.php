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
    $uuid = fake()->uuid();
    $tag = EntityTag::query()->create([
        'uuid' => $uuid,
        'name' => 'TestTag',
    ]);

    expect($tag->uuid)->toBe($uuid);
    expect($tag->name)->toBe('TestTag');
});

it('enforces unique uuid constraint', function (): void {
    $uuid = fake()->uuid();
    EntityTag::query()->create([
        'uuid' => $uuid,
        'name' => 'First Tag',
    ]);

    expect(function () use ($uuid) {
        EntityTag::query()->create([
            'uuid' => $uuid,
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

    $manufacturerUuid = fake()->uuid();

    $manufacturer = Manufacturer::query()->create([
        'uuid' => $manufacturerUuid,
        'name' => 'Test Manufacturer',
        'code' => 'TST',
    ]);

    $itemUuid = fake()->uuid();
    $item = Item::query()->create(['uuid' => $itemUuid]);

    $itemData = ItemData::query()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'manufacturer_id' => $manufacturer->id,
        'name' => 'Test Item',
        'class_name' => 'test_class',
        'data' => [],
    ]);

    $tag1 = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Tag1',
    ]);

    $tag2 = EntityTag::query()->create([
        'uuid' => fake()->uuid(),
        'name' => 'Tag2',
    ]);

    $itemData->entityTags()->attach([$tag1->id, $tag2->id]);

    expect($itemData->entityTags)->toHaveCount(2);
    expect($tag1->itemData)->toHaveCount(1);
    expect($tag1->itemData->first()->id)->toBe($itemData->id);
});
