<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Services\TagItemResolverService;

function createResolvableTagItem(GameVersion $gameVersion, string $name): ItemData
{
    return ItemData::factory()->create([
        'game_version_id' => $gameVersion->id,
        'name' => $name,
        'class_name' => fake()->lexify('cargo_????????'),
        'type' => 'Cargo',
        'sub_type' => 'Commodity',
        'is_player_relevant' => true,
    ]);
}

describe('resolveItemDataIds', function (): void {
    it('matches an item tagged with a descendant of a requested parent tag', function (): void {
        $gameVersion = GameVersion::factory()->create();
        $resolver = app(TagItemResolverService::class);
        $parentTag = EntityTag::factory()->create(['name' => 'Cargo Parent']);
        $matchingChildTag = EntityTag::factory()->create([
            'name' => 'Medical Cargo',
            'parent_uuid' => $parentTag->uuid,
        ]);
        EntityTag::factory()->create([
            'name' => 'Industrial Cargo',
            'parent_uuid' => $parentTag->uuid,
        ]);

        $matchingItem = createResolvableTagItem($gameVersion, 'Medical Cargo Box');
        $matchingItem->entityTags()->attach($matchingChildTag->id);

        $ids = $resolver->resolveItemDataIds([
            ['positive' => [$parentTag->uuid], 'negative' => []],
        ], $gameVersion->id);

        sort($ids);

        expect($ids)->toBe([$matchingItem->id]);
    });

    it('requires one matching tag from each positive tag set', function (): void {
        $gameVersion = GameVersion::factory()->create();
        $resolver = app(TagItemResolverService::class);
        $cargoParentTag = EntityTag::factory()->create(['name' => 'Cargo Parent']);
        $cargoChildTag = EntityTag::factory()->create([
            'name' => 'Medical Cargo',
            'parent_uuid' => $cargoParentTag->uuid,
        ]);
        $sizeParentTag = EntityTag::factory()->create(['name' => 'Size Parent']);
        $sizeChildTag = EntityTag::factory()->create([
            'name' => 'Small Cargo',
            'parent_uuid' => $sizeParentTag->uuid,
        ]);

        $partiallyMatchingItem = createResolvableTagItem($gameVersion, 'Medical Cargo Only');
        $partiallyMatchingItem->entityTags()->attach($cargoChildTag->id);

        $matchingItem = createResolvableTagItem($gameVersion, 'Small Medical Cargo');
        $matchingItem->entityTags()->attach([$cargoChildTag->id, $sizeChildTag->id]);

        $ids = $resolver->resolveItemDataIds([
            ['positive' => [$cargoParentTag->uuid, $sizeParentTag->uuid], 'negative' => []],
        ], $gameVersion->id);

        sort($ids);

        expect($ids)->toBe([$matchingItem->id]);
    });

    it('returns no items when any required positive tag is missing', function (): void {
        $gameVersion = GameVersion::factory()->create();
        $resolver = app(TagItemResolverService::class);
        $existingTag = EntityTag::factory()->create(['name' => 'Existing Cargo']);
        $matchingExistingTagOnly = createResolvableTagItem($gameVersion, 'Existing Cargo Box');
        $matchingExistingTagOnly->entityTags()->attach($existingTag->id);

        $ids = $resolver->resolveItemDataIds([
            ['positive' => [$existingTag->uuid, fake()->uuid()], 'negative' => []],
        ], $gameVersion->id);

        expect($ids)->toBe([]);
    });

    it('ORs separate tag groups together', function (): void {
        $gameVersion = GameVersion::factory()->create();
        $resolver = app(TagItemResolverService::class);
        $firstTag = EntityTag::factory()->create(['name' => 'First Cargo']);
        $secondTag = EntityTag::factory()->create(['name' => 'Second Cargo']);

        $firstItem = createResolvableTagItem($gameVersion, 'First Cargo Box');
        $firstItem->entityTags()->attach($firstTag->id);

        $secondItem = createResolvableTagItem($gameVersion, 'Second Cargo Box');
        $secondItem->entityTags()->attach($secondTag->id);

        $ids = $resolver->resolveItemDataIds([
            ['positive' => [$firstTag->uuid], 'negative' => []],
            ['positive' => [$secondTag->uuid], 'negative' => []],
        ], $gameVersion->id);

        sort($ids);

        expect($ids)->toBe([$firstItem->id, $secondItem->id]);
    });

    it('excludes items tagged with a descendant of a negative tag', function (): void {
        $gameVersion = GameVersion::factory()->create();
        $resolver = app(TagItemResolverService::class);
        $positiveTag = EntityTag::factory()->create(['name' => 'Cargo Parent']);
        $positiveChildTag = EntityTag::factory()->create([
            'name' => 'Medical Cargo',
            'parent_uuid' => $positiveTag->uuid,
        ]);
        $negativeTag = EntityTag::factory()->create(['name' => 'Illegal Parent']);
        $negativeChildTag = EntityTag::factory()->create([
            'name' => 'Contraband',
            'parent_uuid' => $negativeTag->uuid,
        ]);

        $matchingItem = createResolvableTagItem($gameVersion, 'Medical Cargo Box');
        $matchingItem->entityTags()->attach($positiveChildTag->id);

        $excludedItem = createResolvableTagItem($gameVersion, 'Contraband Medical Cargo');
        $excludedItem->entityTags()->attach([$positiveChildTag->id, $negativeChildTag->id]);

        $ids = $resolver->resolveItemDataIds([
            ['positive' => [$positiveTag->uuid], 'negative' => [$negativeTag->uuid]],
        ], $gameVersion->id);

        sort($ids);

        expect($ids)->toBe([$matchingItem->id]);
    });
});
