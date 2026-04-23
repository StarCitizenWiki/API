<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Concerns;

use App\Models\Game\StarmapLocationData;
use Illuminate\Support\Collection;

trait ExpandsUexPrices
{
    private static ?Collection $locationDataCache = null;

    private function expandPrices(array $prices): array
    {
        if ($prices === []) {
            return [];
        }

        $locationDataLookup = $this->resolveLocationDataLookup($prices);

        return collect($prices)
            ->map(function (array $price) use ($locationDataLookup): array {
                $locationDataId = $price['starmap_location_data_id'] ?? null;
                $locationData = $locationDataId !== null ? $locationDataLookup->get($locationDataId) : null;
                $locationUuid = $price['starmap_location_uuid'] ?? null;

                unset($price['starmap_location_data_id']);

                $price['link'] = $locationUuid !== null
                    ? route('locations.show', ['identifier' => $locationUuid])
                    : null;

                $price['web_url'] = $locationUuid !== null
                    ? route('web.locations.show', ['identifier' => $locationUuid])
                    : null;

                if ($locationData !== null) {
                    $price['starmap_location'] = [
                        'name' => $locationData->name,
                        'slug' => $locationData->location?->slug,
                        'type_name' => $locationData->type_name,
                        'parent_name' => $locationData->parent?->name,
                        'star_system_name' => $locationData->parent?->star?->name,
                    ];
                } else {
                    $price['starmap_location'] = null;
                }

                return $price;
            })
            ->sortBy([['starmap_location.star_system_name', 'asc'], ['date_updated', 'desc']])
            ->values()
            ->toArray();
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

        if (static::$locationDataCache === null) {
            static::$locationDataCache = Collection::empty();
        }

        $uncachedIds = array_values(array_diff($locationDataIds, static::$locationDataCache->keys()->toArray()));

        if ($uncachedIds !== []) {
            $fetched = StarmapLocationData::query()
                ->with(['location', 'parent.star'])
                ->whereIn('id', $uncachedIds)
                ->get()
                ->keyBy('id');

            static::$locationDataCache = collect(static::$locationDataCache->all() + $fetched->all());
        }

        return static::$locationDataCache->only($locationDataIds);
    }

    public static function flushLocationDataCache(): void
    {
        static::$locationDataCache = null;
    }
}
