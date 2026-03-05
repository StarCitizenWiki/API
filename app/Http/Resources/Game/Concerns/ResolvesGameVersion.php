<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Concerns;

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ResolvesGameVersion
{
    /**
     * Get the resolved GameVersion for this request.
     *
     * The version is resolved once per request by ResolveGameVersion middleware
     * and stored in request attributes for efficient access.
     */
    protected function gameVersion(): GameVersion
    {
        $gameVersion = request()->attributes->get('game_version');

        if ($gameVersion instanceof GameVersion) {
            return $gameVersion;
        }

        $resolved = GameVersion::resolveRequestedOrDefault($this->gameVersionCode());
        request()->attributes->set('game_version', $resolved);

        return $resolved;
    }

    /**
     * Get the game version code from the request (may be null for default).
     */
    protected function gameVersionCode(): ?string
    {
        $code = request()->attributes->get('game_version_code');

        if ($code !== null) {
            return $code;
        }

        $code = request()->query('version');
        request()->attributes->set('game_version_code', $code);

        return $code;
    }

    /**
     * Load an Item with data for the current game version.
     *
     * This method ensures the item is loaded with version-specific ItemData
     * matching the request's resolved game version.
     *
     * @param  string  $uuid  The item UUID to load
     * @return Item|null The item with loaded data, or null if not found
     */
    protected function loadItemForVersion(string $uuid): ?Item
    {
        $eagerLoaded = request()->attributes->get('eager_loaded_port_items');

        if ($eagerLoaded !== null && $eagerLoaded->has($uuid)) {
            return $eagerLoaded->get($uuid);
        }

        return Item::query()
            ->where('uuid', $uuid)
            ->withDataForVersion($this->gameVersionCode())
            ->first();
    }

    /**
     * Load ItemData for the current game version by item UUID.
     *
     * @param  string  $uuid  The item UUID to load
     * @return ItemData|null The item data with required relations, or null if not found
     */
    protected function loadItemDataForVersion(string $uuid): ?ItemData
    {
        $eagerLoaded = request()->attributes->get('eager_loaded_port_items');

        if ($eagerLoaded !== null && $eagerLoaded->has($uuid)) {
            return $eagerLoaded->get($uuid);
        }

        return ItemData::query()
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->whereHas('item', fn (Builder $query) => $query->where('uuid', $uuid))
            ->with(['item', 'manufacturer', 'gameVersion'])
            ->first();
    }

    /**
     * Eager load ItemData for multiple item UUIDs to prevent N+1 queries.
     *
     * @param  array<int, string>  $uuids  The item UUIDs to load
     * @return Collection<string, ItemData> Collection keyed by item UUID
     */
    protected function eagerLoadPortItemData(array $uuids): Collection
    {
        if ($uuids === []) {
            return collect();
        }

        return ItemData::query()
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->whereHas('item', fn (Builder $query) => $query->whereIn('uuid', $uuids))
            ->with(['item', 'manufacturer', 'gameVersion'])
            ->get()
            ->keyBy(fn (ItemData $itemData) => $itemData->item->uuid);
    }

    /**
     * Load a Vehicle with data for the current game version.
     *
     * @param  string  $uuid  The vehicle UUID to load
     * @return Vehicle|null The vehicle with loaded data, or null if not found
     */
    protected function loadVehicleForVersion(string $uuid): ?Vehicle
    {
        return Vehicle::query()
            ->where('uuid', $uuid)
            ->with([
                'data' => fn (Builder $query) => $query->where('game_version_id', $this->gameVersion()->id),
            ])
            ->first();
    }

    /**
     * Apply version filtering to an Item query builder.
     *
     * Use this when you need to customize the query before execution.
     */
    protected function scopeItemForVersion(Builder $query): Builder
    {
        return $query->withDataForVersion($this->gameVersionCode());
    }
}
