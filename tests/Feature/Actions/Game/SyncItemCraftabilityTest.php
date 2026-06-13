<?php

declare(strict_types=1);

use App\Actions\Game\SyncItemCraftability;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create();
});

it('flags items as craftable when a crafting blueprint references them', function (): void {
    $craftableItem = Item::factory()->create();
    $craftableItemData = ItemData::factory()->create([
        'item_id' => $craftableItem->id,
        'game_version_id' => $this->version->id,
        'is_craftable' => false,
    ]);
    BlueprintData::factory()->create([
        'game_version_id' => $this->version->id,
        'output_item_uuid' => $craftableItem->uuid,
    ]);

    $plainItemData = ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'is_craftable' => false,
    ]);

    app(SyncItemCraftability::class)->execute($this->version->id);

    expect($craftableItemData->fresh()->is_craftable)->toBeTrue()
        ->and($plainItemData->fresh()->is_craftable)->toBeFalse();
});

it('clears stale craftable flags for items without a blueprint', function (): void {
    $staleItem = Item::factory()->create();
    $staleItemData = ItemData::factory()->create([
        'item_id' => $staleItem->id,
        'game_version_id' => $this->version->id,
        'is_craftable' => true,
    ]);

    app(SyncItemCraftability::class)->execute($this->version->id);

    expect($staleItemData->fresh()->is_craftable)->toBeFalse();
});

it('does not rewrite rows that are already correct', function (): void {
    // A craftable item, already correctly flagged.
    $correctCraftableItem = Item::factory()->create();
    ItemData::factory()->create([
        'item_id' => $correctCraftableItem->id,
        'game_version_id' => $this->version->id,
        'is_craftable' => true,
    ]);
    BlueprintData::factory()->create([
        'game_version_id' => $this->version->id,
        'output_item_uuid' => $correctCraftableItem->uuid,
    ]);

    // A non-craftable item, already correctly flagged.
    ItemData::factory()->create([
        'game_version_id' => $this->version->id,
        'is_craftable' => false,
    ]);

    DB::enableQueryLog();
    app(SyncItemCraftability::class)->execute($this->version->id);
    $updates = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $query) => stripos($query, 'update') === 0)
        ->values();

    expect($updates)->toBeEmpty();
});
