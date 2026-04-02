<?php

declare(strict_types=1);

use App\Models\Game\EntityTag;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use App\Services\RelatedItemsBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

describe('RelatedItemsBuilder Caching', function () {
    beforeEach(function (): void {
        app('cache')->setDefaultDriver('array');
        app('cache')->forgetDriver(['array', 'database']);
        Cache::store('array')->flush();
        Cache::store('database')->flush();

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

    afterEach(function (): void {
        Cache::store('array')->flush();
        Cache::store('database')->flush();
        app('cache')->setDefaultDriver('array');
        app('cache')->forgetDriver(['array', 'database']);
    });

    it('keeps related items cached until the cache is flushed when values are serialized', function (): void {
        app('cache')->setDefaultDriver('database');
        app('cache')->forgetDriver(['array', 'database']);

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

        $variantItem = Item::factory()->create();
        $variantData = ItemData::factory()
            ->for($variantItem)
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Variant Armor Alpha',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'base_id' => $baseItemData->id,
                'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
            ]);

        $builder = new RelatedItemsBuilder($this->gameVersion->code);

        $initial = $builder->build($baseItem);
        expect(data_get($initial, 'variant_items.0.name'))->toBe('Variant Armor Alpha');

        $variantData->update(['name' => 'Variant Armor Beta']);

        $cached = $builder->build($baseItem);
        expect(data_get($cached, 'variant_items.0.name'))->toBe('Variant Armor Alpha');

        Cache::flush();

        $refreshed = $builder->build($baseItem);
        expect(data_get($refreshed, 'variant_items.0.name'))->toBe('Variant Armor Beta');
    });

    it('keeps version-specific cache entries isolated', function (): void {
        $versionOne = GameVersion::factory()->create([
            'code' => '5.0.0-LIVE',
            'channel' => 'live',
            'released_at' => now()->subDay(),
        ]);

        $versionTwo = GameVersion::factory()->create([
            'code' => '5.1.0-LIVE',
            'channel' => 'live',
            'released_at' => now(),
        ]);

        $baseItem = Item::factory()->create();
        $variantItem = Item::factory()->create();

        $versionOneBaseData = ItemData::factory()
            ->for($baseItem)
            ->for($versionOne, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version One Armor',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
            ]);

        ItemData::factory()
            ->for($variantItem)
            ->for($versionOne, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version One Variant',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'base_id' => $versionOneBaseData->id,
                'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
            ]);

        $versionTwoBaseData = ItemData::factory()
            ->for($baseItem)
            ->for($versionTwo, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version Two Armor',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
            ]);

        ItemData::factory()
            ->for($variantItem)
            ->for($versionTwo, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Version Two Variant',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.Light',
                'base_id' => $versionTwoBaseData->id,
                'data' => ['stdItem' => ['Tags' => ['set_test', 'series_test']]],
            ]);

        $builderOne = new RelatedItemsBuilder($versionOne->code);
        $builderTwo = new RelatedItemsBuilder($versionTwo->code);

        expect(data_get($builderOne->build($baseItem), 'variant_items.0.name'))->toBe('Version One Variant')
            ->and(data_get($builderTwo->build($baseItem), 'variant_items.0.name'))->toBe('Version Two Variant');
    });

    it('builds set items from matching class names', function (): void {
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

        ItemData::factory()
            ->for(Item::factory(), 'item')
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Helmet',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.helmet',
                'class_name' => 'fps_armor_heavy_helmet_01',
                'data' => ['stdItem' => []],
            ]);

        ItemData::factory()
            ->for(Item::factory(), 'item')
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Arms',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.arms',
                'class_name' => 'fps_armor_heavy_arms_01',
                'data' => ['stdItem' => []],
            ]);

        ItemData::factory()
            ->for(Item::factory(), 'item')
            ->for($this->gameVersion, 'gameVersion')
            ->for($this->manufacturer)
            ->create([
                'name' => 'Test Armor Legs',
                'type' => 'Armor',
                'classification' => 'FPS.Armor.legs',
                'class_name' => 'fps_armor_heavy_legs_01',
                'data' => ['stdItem' => []],
            ]);

        $builder = new RelatedItemsBuilder($this->gameVersion->code);
        $result = $builder->build($item);

        expect($result)->toHaveKeys(['set_name', 'base_item', 'variant_items', 'set_items'])
            ->and($result['set_items'])->toHaveCount(3)
            ->and(collect($result['set_items'])->pluck('name')->all())->toBe([
                'Test Armor Helmet',
                'Test Armor Arms',
                'Test Armor Legs',
            ]);
    });
});
