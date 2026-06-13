<?php

declare(strict_types=1);

namespace App\Actions\Game;

use App\Models\Game\ItemData;

final class SyncItemCraftability
{
    /**
     * Recompute {@see ItemData::$is_craftable} for all items of the given game version, writing only rows whose value actually changes.
     */
    public function execute(int $gameVersionId): void
    {
        // Flip to true: items that have a blueprint but are currently flagged as not craftable.
        ItemData::where('game_version_id', $gameVersionId)
            ->whereHas('craftingBlueprints')
            ->where('is_craftable', false)
            ->chunkById(5000, fn ($items) => ItemData::whereIn('id', $items->pluck('id'))
                ->update(['is_craftable' => true]));

        // Flip to false: items without a blueprint but currently flagged as craftable.
        ItemData::where('game_version_id', $gameVersionId)
            ->whereDoesntHave('craftingBlueprints')
            ->where('is_craftable', true)
            ->chunkById(5000, fn ($items) => ItemData::whereIn('id', $items->pluck('id'))
                ->update(['is_craftable' => false]));
    }
}
