<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;

use function createDefaultGameVersion;

/**
 * Builds the minimal item-show fixture: default version, manufacturer, item, and item data.
 *
 * @param  array<string, mixed>  $itemOverrides  Item factory attributes.
 * @param  array<string, mixed>  $itemDataOverrides  ItemData factory attributes (data, type, sub_type, etc.).
 * @param  array{name?: string, code?: string}  $manufacturerAttrs  Manufacturer factory attributes.
 * @return array{0: Item, 1: ItemData, 2: GameVersion, 3: Manufacturer}
 */
function createItemForShow(
    array $itemOverrides = [],
    array $itemDataOverrides = [],
    array $manufacturerAttrs = [],
): array {
    $version = createDefaultGameVersion();
    $manufacturer = Manufacturer::factory()->create(array_merge([
        'name' => 'Acme Works',
        'code' => 'ACME',
    ], $manufacturerAttrs));

    $item = Item::factory()->create($itemOverrides);

    $itemData = ItemData::factory()
        ->for($item)
        ->for($version, 'gameVersion')
        ->for($manufacturer)
        ->create($itemDataOverrides);

    return [$item, $itemData, $version, $manufacturer];
}
