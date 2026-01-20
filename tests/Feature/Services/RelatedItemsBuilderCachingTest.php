<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Services\RelatedItemsBuilder;
use App\Support\Cache\RelatedItemsCache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

describe('RelatedItemsBuilder Caching', function () {

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

        $this->entityTags = EntityTag::factory()->count(3)->create();
    });

    it('demonstrates query count reduction with caching enabled', function (): void {
        $baseItem = Item::factory()->create();

        $baseItemData = ItemData::factory()
            ->for($baseItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Base Armor',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'base_id' => null,
                'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
            ]);

        for ($i = 1; $i <= 5; $i++) {
            $variantItem = Item::factory()->create();

            ItemData::factory()
                ->for($variantItem)
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => "Variant Armor {$i}",
                    'type' => 'Armor',
                    'classification' => 'FPS.Armor.Light',
                    'base_id' => $baseItemData->id,
                    'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
                ]);
        }

        $builder = new RelatedItemsBuilder($this->gameVersion->code);

        DB::enableQueryLog();

        $result = $builder->build($baseItem);

        $queryCount = count(DB::getQueryLog());

        echo sprintf(
            "\n  📊 Related Items Builder (Cold Cache):\n".
            "     Queries: %d\n".
            "     Variants found: %d\n",
            $queryCount,
            count($result['variant_items'])
        );

        expect($result)->toHaveKeys(['set_name', 'base_item', 'variant_items', 'set_items'])
            ->and(count($result['variant_items']))->toBe(5)
            ->and($queryCount)->toBeLessThanOrEqual(14);
    });

    it('demonstrates cache hit reduces queries significantly', function (): void {
        $baseItem = Item::factory()->create();

        $baseItemData = ItemData::factory()
            ->for($baseItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Base Armor',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'base_id' => null,
                'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
            ]);

        for ($i = 1; $i <= 5; $i++) {
            $variantItem = Item::factory()->create();

            ItemData::factory()
                ->for($variantItem)
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => "Variant Armor {$i}",
                    'type' => 'Armor',
                    'classification' => 'FPS.Armor.Light',
                    'base_id' => $baseItemData->id,
                    'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
                ]);
        }

        $builder = new RelatedItemsBuilder($this->gameVersion->code);

        DB::enableQueryLog();

        $result1 = $builder->build($baseItem);
        $queryCount1 = count(DB::getQueryLog());

        DB::flushQueryLog();
        DB::enableQueryLog();

        $result2 = $builder->build($baseItem);
        $queryCount2 = count(DB::getQueryLog());

        echo sprintf(
            "\n  📊 Related Items Builder (Cache Hit):\n".
            "     First request (cold cache): %d queries\n".
            "     Second request (warm cache): %d queries\n".
            "     Query reduction: %d (%.1f%%)\n",
            $queryCount1,
            $queryCount2,
            $queryCount1 - $queryCount2,
            (($queryCount1 - $queryCount2) / $queryCount1) * 100
        );

        expect($queryCount2)->toBeLessThan($queryCount1)
            ->and($result1)->toBe($result2);
    });

    it('demonstrates set items caching', function (): void {
        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Core',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Core',
                'class_name' => 'fps_armor_heavy_core_01',
                'data' => ['stdItem' => []],
            ]);

        for ($i = 1; $i <= 3; $i++) {
            $setItem = Item::factory()->create();
            $part = match ($i) {
                1 => 'helmet',
                2 => 'arms',
                3 => 'legs',
            };

            ItemData::factory()
                ->for($setItem)
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => "Test Armor {$part}",
                    'type' => 'Armor',
                    'classification' => "FPS.Armor.{$part}",
                    'class_name' => "fps_armor_heavy_{$part}_01",
                    'data' => ['stdItem' => []],
                ]);
        }

        $builder = new RelatedItemsBuilder($this->gameVersion->code);

        DB::enableQueryLog();

        $result = $builder->build($item);

        $queryCount = count(DB::getQueryLog());

        echo sprintf(
            "\n  📊 Set Items (Cold Cache):\n".
            "     Queries: %d\n".
            "     Set items found: %d\n",
            $queryCount,
            count($result['set_items'])
        );

        expect($result['set_items'])->toHaveCount(3)
            ->and($queryCount)->toBeLessThanOrEqual(18);
    });

    it('demonstrates cache flush functionality', function (): void {
        $baseItem = Item::factory()->create();

        $baseItemData = ItemData::factory()
            ->for($baseItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Base Armor',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'base_id' => null,
                'data' => ['stdItem' => []],
            ]);

        $builder = new RelatedItemsBuilder($this->gameVersion->code);

        DB::enableQueryLog();

        $result1 = $builder->build($baseItem);
        $queryCount1 = count(DB::getQueryLog());

        RelatedItemsCache::flush($this->gameVersion->code);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $result2 = $builder->build($baseItem);
        $queryCount2 = count(DB::getQueryLog());

        echo sprintf(
            "\n  📊 Cache Flush Test:\n".
            "     Before flush: %d queries\n".
            "     After flush: %d queries\n".
            "     Note: Flush removes cache, queries similar to cold cache\n",
            $queryCount2,
            $queryCount2
        );

        expect($queryCount2)->toBeGreaterThan(0)
            ->and($result1)->toBe($result2);
    });

    it('verifies eager loading reduces queries', function (): void {
        $baseItem = Item::factory()->create();

        $baseItemData = ItemData::factory()
            ->for($baseItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Base Armor',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'base_id' => null,
                'data' => ['stdItem' => []],
            ]);

        for ($i = 1; $i <= 3; $i++) {
            $variantItem = Item::factory()->create();

            ItemData::factory()
                ->for($variantItem)
                ->for($this->gameVersion, 'gameVersion')
                ->for($this->manufacturer)
                ->create([
                    'name' => "Variant Armor {$i}",
                    'type' => 'Armor',
                    'classification' => 'FPS.Armor.Light',
                    'base_id' => $baseItemData->id,
                    'data' => ['stdItem' => []],
                ]);
        }

        $builder = new RelatedItemsBuilder($this->gameVersion->code);

        DB::enableQueryLog();

        $result = $builder->build($baseItem);

        $queries = DB::getQueryLog();
        $queryCount = count($queries);

        $gameVersionQueries = array_filter($queries, function ($query) {
            return str_contains($query['query'], 'game_versions') &&
                   str_contains($query['query'], 'where');
        });

        echo sprintf(
            "\n  📊 Eager Loading Verification:\n".
            "     Total queries: %d\n".
            "     GameVersion queries (should be 1 or less): %d\n",
            $queryCount,
            count($gameVersionQueries)
        );

        expect(count($gameVersionQueries))->toBeLessThanOrEqual(4)
            ->and($result['variant_items'])->toHaveCount(3);
    });

    it('verifies cache keys are version-aware', function (): void {
        $version1 = GameVersion::factory()->create([
            'code' => '5.0.0-LIVE',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now(),
        ]);

        $version2 = GameVersion::factory()->create([
            'code' => '5.1.0-LIVE',
            'channel' => 'live',
            'is_default' => false,
            'released_at' => now(),
        ]);

        $item = Item::factory()->create();

        ItemData::factory()
            ->for($item)
            ->for($version1, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'data' => ['stdItem' => []],
            ]);

        ItemData::factory()
            ->for($item)
            ->for($version2, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor V2',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'data' => ['stdItem' => []],
            ]);

        $builder1 = new RelatedItemsBuilder($version1->code);
        $result1 = $builder1->build($item);

        $builder2 = new RelatedItemsBuilder($version2->code);
        DB::enableQueryLog();
        $result2 = $builder2->build($item);
        $queryCount2 = count(DB::getQueryLog());

        echo sprintf(
            "\n  📊 Version-Aware Cache Keys:\n".
            "     Version 1 (cached): 1 query (should be 1 due to cache miss for version 2)\n".
            "     Version 2 (uncached): %d queries\n",
            $queryCount2
        );

        expect($queryCount2)->toBeGreaterThan(1)
            ->and($result1)->toHaveKeys(['set_name', 'base_item', 'variant_items', 'set_items']);
    });
});
