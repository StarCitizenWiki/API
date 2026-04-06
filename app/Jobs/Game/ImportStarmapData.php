<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\EntityTag;
use App\Models\Game\StarmapAmenity;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Support\Filters\FilterCache;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class ImportStarmapData implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path = 'starmap.json',
    ) {}

    /**
     * @throws JsonException
     */
    public function handle(): void
    {
        $contents = Storage::disk('scunpacked')->get($this->path);
        $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        $entries = is_array($payload) ? $payload : [];

        if ($entries === []) {
            return;
        }

        DB::transaction(function () use ($entries): void {
            $firstPass = [];
            $slugMap = [];

            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $uuid = $entry['UUID'] ?? null;

                if ($uuid === null) {
                    continue;
                }

                $location = StarmapLocation::query()->firstOrCreate(
                    ['uuid' => $uuid],
                    ['uuid' => $uuid]
                );

                $name = $this->extractName($entry);
                $slug = $this->generateUniqueSlug(Str::slug($name), $slugMap);
                $slugMap[$uuid] = $slug;

                $locationData = StarmapLocationData::query()->updateOrCreate(
                    [
                        'starmap_location_id' => $location->id,
                        'game_version_id' => $this->gameVersionId,
                    ],
                    array_merge(
                        $this->mapLocationData($entry),
                        ['slug' => $slug]
                    )
                );

                $locationData->amenities()->sync($this->syncAmenities($entry));

                $firstPass[$uuid] = [
                    'location' => $location,
                    'location_data' => $locationData,
                    'entry' => $entry,
                ];
            }

            $this->resolveHierarchy($firstPass);
        });

        FilterCache::bust(FilterCache::NAMESPACE_STARMAP_LOCATIONS);
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function mapLocationData(array $entry): array
    {
        return [
            'parent_data_id' => null,
            'star_data_id' => null,
            'location_hierarchy_entity_tag_id' => EntityTag::query()->where('uuid', Arr::get($entry, 'LocationHierarchyTag.UUID'))->first()?->id,
            'name' => $this->extractName($entry),
            'description' => $this->normalizeNullableString($entry['Description'] ?? null),
            'type_name' => $this->extractTypeName($entry),
            'system' => null,
            'size' => is_numeric($entry['Size'] ?? null) ? (float) $entry['Size'] : null,
            'is_scannable' => (bool) ($entry['IsScannable'] ?? false),
            'block_travel' => (bool) ($entry['BlockTravel'] ?? false),
            'data' => $entry,
        ];
    }

    private function extractName(array $entry): string
    {
        $name = $this->normalizeNullableString($entry['Name'] ?? null);

        return $name ?? (string) ($entry['UUID'] ?? 'Unknown Location');
    }

    private function extractTypeName(array $entry): string
    {
        $typeName = $this->normalizeNullableString(Arr::get($entry, 'Type.Name'));

        return $typeName ?? 'Unknown';
    }

    /**
     * @param  array<string, array{location: StarmapLocation, location_data: StarmapLocationData, entry: array<string, mixed>}>  $firstPass
     */
    private function resolveHierarchy(array $firstPass): void
    {
        $solarSystemLookup = $this->buildSolarSystemLookup($firstPass);
        $resolvedHierarchy = [];

        foreach (array_keys($firstPass) as $uuid) {
            $hierarchy = $this->resolveHierarchyData($uuid, $firstPass, $resolvedHierarchy, $solarSystemLookup);

            $firstPass[$uuid]['location_data']->update([
                'parent_data_id' => $hierarchy['parent_data_id'],
                'star_data_id' => $hierarchy['star_data_id'],
                'system' => $hierarchy['system'],
            ]);
        }
    }

    /**
     * @param  array<string, array{location: StarmapLocation, location_data: StarmapLocationData, entry: array<string, mixed>}>  $firstPass
     * @param  array<string, array{parent_data_id: int|null, star_data_id: int|null, system: string|null}>  $resolvedHierarchy
     * @param  array<string, string>  $solarSystemLookup
     * @return array{parent_data_id: int|null, star_data_id: int|null, system: string|null}
     */
    private function resolveHierarchyData(
        string $uuid,
        array $firstPass,
        array &$resolvedHierarchy,
        array $solarSystemLookup,
    ): array {
        if (array_key_exists($uuid, $resolvedHierarchy)) {
            return $resolvedHierarchy[$uuid];
        }

        $current = $firstPass[$uuid]['entry'] ?? null;

        if ($current === null) {
            return $resolvedHierarchy[$uuid] = [
                'parent_data_id' => null,
                'star_data_id' => null,
                'system' => null,
            ];
        }

        $currentType = $this->extractTypeName($current);
        $parentUuid = $this->normalizeNullableString($current['ParentUUID'] ?? null);
        $currentName = $this->extractName($current);

        if ($currentType === 'SolarSystem') {
            return $resolvedHierarchy[$uuid] = [
                'parent_data_id' => null,
                'star_data_id' => null,
                'system' => $currentName,
            ];
        }

        if ($parentUuid !== null) {
            if (! isset($firstPass[$parentUuid])) {
                return $resolvedHierarchy[$uuid] = [
                    'parent_data_id' => null,
                    'star_data_id' => null,
                    'system' => null,
                ];
            }

            $parentHierarchy = $this->resolveHierarchyData($parentUuid, $firstPass, $resolvedHierarchy, $solarSystemLookup);

            return $resolvedHierarchy[$uuid] = [
                'parent_data_id' => $firstPass[$parentUuid]['location_data']->id,
                'star_data_id' => $currentType === 'Star'
                    ? $firstPass[$uuid]['location_data']->id
                    : $parentHierarchy['star_data_id'],
                'system' => $parentHierarchy['system'],
            ];
        }

        if ($currentType !== 'Star') {
            return $resolvedHierarchy[$uuid] = [
                'parent_data_id' => null,
                'star_data_id' => null,
                'system' => null,
            ];
        }

        $system = $this->resolveSystemNameForStar($current, $firstPass, $solarSystemLookup);

        return $resolvedHierarchy[$uuid] = [
            'parent_data_id' => null,
            'star_data_id' => $firstPass[$uuid]['location_data']->id,
            'system' => $system,
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<string, array{location: StarmapLocation, location_data: StarmapLocationData, entry: array<string, mixed>}>  $firstPass
     * @param  array<string, string>  $solarSystemLookup
     */
    private function resolveSystemNameForStar(array $entry, array $firstPass, array $solarSystemLookup): ?string
    {
        $solarSystemUuid = $this->resolveSolarSystemUuidForStar($entry, $solarSystemLookup);

        if ($solarSystemUuid !== null) {
            $solarSystem = $firstPass[$solarSystemUuid]['entry'] ?? null;

            if (is_array($solarSystem)) {
                return $this->extractName($solarSystem);
            }
        }

        return $this->extractName($entry);
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<string, string>  $solarSystemLookup
     */
    private function resolveSolarSystemUuidForStar(array $entry, array $solarSystemLookup): ?string
    {
        $normalizedStarName = $this->normalizeSystemLookupKey($this->extractName($entry));

        if ($normalizedStarName === null) {
            return null;
        }

        return $solarSystemLookup[$normalizedStarName] ?? null;
    }

    /**
     * @param  array<string, array{location: StarmapLocation, location_data: StarmapLocationData, entry: array<string, mixed>}>  $firstPass
     * @return array<string, string>
     */
    private function buildSolarSystemLookup(array $firstPass): array
    {
        $lookup = [];

        foreach ($firstPass as $uuid => $imported) {
            if ($this->extractTypeName($imported['entry']) !== 'SolarSystem') {
                continue;
            }

            $normalizedSystemName = $this->normalizeSystemLookupKey($this->extractName($imported['entry']));

            if ($normalizedSystemName !== null) {
                $lookup[$normalizedSystemName] = $uuid;
            }
        }

        return $lookup;
    }

    private function normalizeSystemLookupKey(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $normalized = Str::of($name)
            ->trim()
            ->lower()
            ->replaceMatches('/\s+system$/', '')
            ->squish()
            ->value();

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @return array<int, int>
     */
    private function syncAmenities(array $entry): array
    {
        return collect($entry['Amenities'] ?? [])
            ->filter(fn (mixed $amenity): bool => is_array($amenity))
            ->map(function (array $amenity): ?int {
                $uuid = trim($amenity['UUID'] ?? '');

                if (empty($uuid)) {
                    return null;
                }

                $starmapAmenity = StarmapAmenity::query()->updateOrCreate(
                    ['uuid' => $uuid],
                    [
                        'name' => $this->normalizeNullableString($amenity['Name'] ?? null) ?? $uuid,
                        'display_name' => $this->normalizeNullableString($amenity['DisplayName'] ?? null),
                    ]
                );

                return $starmapAmenity->id;
            })
            ->filter(fn (?int $id): bool => $id !== null)
            ->values()
            ->all();
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Generate a unique slug for starmap location data.
     * Checks the database and local batch map, appending a counter if the slug already exists.
     *
     * @param  string  $baseSlug  The base slug to start with
     * @param  array<string, string>  $slugMap  Local map of already generated slugs in this batch
     */
    private function generateUniqueSlug(string $baseSlug, array $slugMap): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while (isset($slugMap[$slug]) || StarmapLocationData::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
