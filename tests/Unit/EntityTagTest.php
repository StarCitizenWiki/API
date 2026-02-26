<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Illuminate\Database\QueryException;

it('can create an entity tag', function (): void {
    $uuid = fake()->uuid();
    $tag = EntityTag::query()->create([
        'uuid' => $uuid,
        'name' => 'TestTag',
    ]);

    expect($tag->exists)->toBeTrue()
        ->and($tag->uuid)->toBe($uuid)
        ->and($tag->name)->toBe('TestTag');

    $this->assertDatabaseHas('game_entity_tags', [
        'id' => $tag->id,
        'uuid' => $uuid,
        'name' => 'TestTag',
    ]);
});

it('enforces unique uuid constraint', function (): void {
    $uuid = fake()->uuid();
    EntityTag::query()->create([
        'uuid' => $uuid,
        'name' => 'First Tag',
    ]);

    expect(function () use ($uuid): void {
        EntityTag::query()->create([
            'uuid' => $uuid,
            'name' => 'Second Tag',
        ]);
    })->toThrow(QueryException::class);

    expect(EntityTag::query()->where('uuid', $uuid)->count())->toBe(1);
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

    expect($itemData->entityTags)->toHaveCount(2)
        ->and($itemData->entityTags->pluck('id')->all())->toEqualCanonicalizing([$tag1->id, $tag2->id])
        ->and($tag1->itemData)->toHaveCount(1)
        ->and($tag1->itemData->pluck('id')->all())->toBe([$itemData->id])
        ->and($tag2->itemData)->toHaveCount(1)
        ->and($tag2->itemData->pluck('id')->all())->toBe([$itemData->id]);
});
