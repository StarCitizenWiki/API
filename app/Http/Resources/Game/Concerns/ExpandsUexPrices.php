<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Concerns;

use App\Http\Resources\Game\Uex\UexPriceResource;
use App\Models\Game\StarmapLocationData;
use Illuminate\Support\Collection;

trait ExpandsUexPrices
{
    private static ?Collection $locationDataCache = null;

    private static function cachedGet(array $locationDataIds): void
    {
        if (static::$locationDataCache === null) {
            static::$locationDataCache = Collection::empty();
        }

        $uncachedIds = array_values(
            array_diff($locationDataIds, static::$locationDataCache->keys()->toArray())
        );

        if ($uncachedIds !== []) {
            $fetched = StarmapLocationData::query()
                ->whereIn('id', $uncachedIds)
                ->get()
                ->keyBy('id');

            static::$locationDataCache = collect(
                static::$locationDataCache->all() + $fetched->all()
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function expandPrices(array $prices): array
    {
        if ($prices === []) {
            return [];
        }

        $locationDataLookup = $this->resolveLocationDataLookup($prices);

        $mapped = array_map(static function (array $price) use ($locationDataLookup): array {
            $locationDataId = $price['starmap_location_data_id'] ?? null;
            $locationData = $locationDataId !== null ? $locationDataLookup->get($locationDataId) : null;

            unset($price['starmap_location_data_id']);

            return UexPriceResource::toPriceArray($price, $locationData);
        }, $prices);

        return collect($mapped)
            ->sortBy([['starmap_location.star_system_name', 'asc'], ['date_updated', 'desc']])
            ->values()
            ->all();
    }

    private function resolveLocationDataLookup(array $prices): Collection
    {
        $locationDataIds = collect($prices)
            ->pluck('starmap_location_data_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if ($locationDataIds === []) {
            return Collection::empty();
        }

        self::cachedGet($locationDataIds);

        return static::$locationDataCache->only($locationDataIds);
    }

    /**
     * @param  array<int, int>  $locationDataIds
     */
    public static function preloadLocationData(array $locationDataIds): void
    {
        if ($locationDataIds === []) {
            return;
        }

        self::cachedGet($locationDataIds);
    }

    public static function flushLocationDataCache(): void
    {
        static::$locationDataCache = null;
    }
}
