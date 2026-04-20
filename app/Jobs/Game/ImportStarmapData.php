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

        $entityTagMap = EntityTag::query()->pluck('id', 'uuid')->toArray();

        $validEntries = [];
        foreach ($entries as $entry) {
            if (is_array($entry) && ($entry['UUID'] ?? null) !== null) {
                $validEntries[(string) $entry['UUID']] = $entry;
            }
        }

        if ($validEntries === []) {
            return;
        }

        $uuids = array_keys($validEntries);

        StarmapLocation::upsert(
            array_map(fn (string $uuid): array => ['uuid' => $uuid], $uuids),
            ['uuid'],
            ['uuid'],
        );

        $locationIdMap = StarmapLocation::query()
            ->whereIn('uuid', $uuids)
            ->pluck('id', 'uuid')
            ->toArray();

        $usedSlugs = StarmapLocationData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->pluck('slug')
            ->toArray();

        $locationDataRows = [];
        $amenityEntries = [];
        $entryAmenityUuids = [];

        foreach ($validEntries as $uuid => $entry) {
            $name = $this->extractName($entry);
            $slug = $this->generateUniqueSlugInMemory(Str::slug($name), $usedSlugs);
            $usedSlugs[] = $slug;

            $hierarchyTagUuid = Arr::get($entry, 'LocationHierarchyTag.UUID');

            $locationDataRows[$uuid] = [
                'starmap_location_id' => $locationIdMap[$uuid],
                'game_version_id' => $this->gameVersionId,
                'parent_data_id' => null,
                'star_data_id' => null,
                'location_hierarchy_entity_tag_id' => $hierarchyTagUuid !== null ? ($entityTagMap[$hierarchyTagUuid] ?? null) : null,
                'name' => $name,
                'description' => $this->normalizeNullableString($entry['Description'] ?? null),
                'type_name' => $this->extractTypeName($entry),
                'system' => null,
                'size' => is_numeric($entry['Size'] ?? null) ? (float) $entry['Size'] : null,
                'is_scannable' => (bool) ($entry['IsScannable'] ?? false),
                'block_travel' => (bool) ($entry['BlockTravel'] ?? false),
                'data' => json_encode($entry, JSON_THROW_ON_ERROR),
                'slug' => $slug,
            ];

            foreach ($entry['Amenities'] ?? [] as $amenity) {
                if (! is_array($amenity)) {
                    continue;
                }

                $aUuid = trim($amenity['UUID'] ?? '');

                if ($aUuid === '') {
                    continue;
                }

                $amenityEntries[$aUuid] = [
                    'name' => $this->normalizeNullableString($amenity['Name'] ?? null) ?? $aUuid,
                    'display_name' => $this->normalizeNullableString($amenity['DisplayName'] ?? null),
                ];
                $entryAmenityUuids[$uuid][] = $aUuid;
            }
        }

        $upsertColumns = [
            'starmap_location_id', 'game_version_id', 'parent_data_id', 'star_data_id',
            'location_hierarchy_entity_tag_id', 'name', 'description', 'type_name',
            'system', 'size', 'is_scannable', 'block_travel', 'data', 'slug',
        ];

        DB::transaction(function () use ($locationDataRows, $validEntries, $uuids, $locationIdMap, $amenityEntries, $entryAmenityUuids, $upsertColumns): void {
            StarmapLocationData::upsert(
                array_values($locationDataRows),
                ['starmap_location_id', 'game_version_id'],
                array_values(array_diff($upsertColumns, ['starmap_location_id', 'game_version_id'])),
            );

            $locationDataIdMap = StarmapLocationData::query()
                ->where('game_version_id', $this->gameVersionId)
                ->whereIn('starmap_location_id', array_values($locationIdMap))
                ->pluck('id', 'starmap_location_id')
                ->toArray();

            $solarSystemLookup = $this->buildSolarSystemLookup($validEntries);
            $resolvedHierarchy = [];

            foreach ($uuids as $uuid) {
                $this->resolveHierarchyData(
                    $uuid,
                    $validEntries,
                    $resolvedHierarchy,
                    $solarSystemLookup,
                    $locationIdMap,
                    $locationDataIdMap,
                );
            }

            $hierarchyRows = [];
            foreach ($uuids as $uuid) {
                $h = $resolvedHierarchy[$uuid];
                $hierarchyRows[] = [
                    'starmap_location_id' => $locationIdMap[$uuid],
                    'game_version_id' => $this->gameVersionId,
                    'parent_data_id' => $h['parent_data_id'],
                    'star_data_id' => $h['star_data_id'],
                    'system' => $h['system'],
                ];
            }

            StarmapLocationData::upsert(
                $hierarchyRows,
                ['starmap_location_id', 'game_version_id'],
                ['parent_data_id', 'star_data_id', 'system'],
            );

            $this->syncAmenitiesBulk($amenityEntries, $entryAmenityUuids, $locationIdMap, $locationDataIdMap);
        });

        FilterCache::bust(FilterCache::NAMESPACE_STARMAP_LOCATIONS);
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
     * @param  array<string, array<string, mixed>>  $entries
     * @param  array<string, array{parent_data_id: int|null, star_data_id: int|null, system: string|null}>  $resolvedHierarchy
     * @param  array<string, string>  $solarSystemLookup
     * @param  array<string, int>  $locationIdMap
     * @param  array<int, int>  $locationDataIdMap
     * @return array{parent_data_id: int|null, star_data_id: int|null, system: string|null}
     */
    private function resolveHierarchyData(
        string $uuid,
        array &$entries,
        array &$resolvedHierarchy,
        array &$solarSystemLookup,
        array $locationIdMap,
        array $locationDataIdMap,
    ): array {
        if (array_key_exists($uuid, $resolvedHierarchy)) {
            return $resolvedHierarchy[$uuid];
        }

        $current = $entries[$uuid] ?? null;

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

        $currentLocationId = $locationIdMap[$uuid] ?? null;
        $currentDataId = $currentLocationId !== null ? ($locationDataIdMap[$currentLocationId] ?? null) : null;

        if ($parentUuid !== null) {
            if (! isset($entries[$parentUuid])) {
                return $resolvedHierarchy[$uuid] = [
                    'parent_data_id' => null,
                    'star_data_id' => null,
                    'system' => null,
                ];
            }

            $parentHierarchy = $this->resolveHierarchyData(
                $parentUuid, $entries, $resolvedHierarchy, $solarSystemLookup, $locationIdMap, $locationDataIdMap,
            );

            $parentLocationId = $locationIdMap[$parentUuid] ?? null;

            return $resolvedHierarchy[$uuid] = [
                'parent_data_id' => $parentLocationId !== null ? ($locationDataIdMap[$parentLocationId] ?? null) : null,
                'star_data_id' => $currentType === 'Star'
                    ? $currentDataId
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

        $system = $this->resolveSystemNameForStar($current, $entries, $solarSystemLookup);

        return $resolvedHierarchy[$uuid] = [
            'parent_data_id' => null,
            'star_data_id' => $currentDataId,
            'system' => $system,
        ];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @param  array<string, array<string, mixed>>  $entries
     * @param  array<string, string>  $solarSystemLookup
     */
    private function resolveSystemNameForStar(array $entry, array &$entries, array &$solarSystemLookup): ?string
    {
        $solarSystemUuid = $this->resolveSolarSystemUuidForStar($entry, $solarSystemLookup);

        if ($solarSystemUuid !== null) {
            $solarSystem = $entries[$solarSystemUuid] ?? null;

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
    private function resolveSolarSystemUuidForStar(array $entry, array &$solarSystemLookup): ?string
    {
        $normalizedStarName = $this->normalizeSystemLookupKey($this->extractName($entry));

        if ($normalizedStarName === null) {
            return null;
        }

        return $solarSystemLookup[$normalizedStarName] ?? null;
    }

    /**
     * @param  array<string, array<string, mixed>>  $entries
     * @return array<string, string>
     */
    private function buildSolarSystemLookup(array &$entries): array
    {
        $lookup = [];

        foreach ($entries as $uuid => $entry) {
            if ($this->extractTypeName($entry) !== 'SolarSystem') {
                continue;
            }

            $normalizedSystemName = $this->normalizeSystemLookupKey($this->extractName($entry));

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
     * @param  array<string, array{name: string, display_name: string|null}>  $amenityEntries
     * @param  array<string, list<string>>  $entryAmenityUuids
     * @param  array<string, int>  $locationIdMap
     * @param  array<int, int>  $locationDataIdMap
     */
    private function syncAmenitiesBulk(
        array $amenityEntries,
        array $entryAmenityUuids,
        array $locationIdMap,
        array $locationDataIdMap,
    ): void {
        if ($amenityEntries === []) {
            return;
        }

        $amenityRows = [];
        foreach ($amenityEntries as $aUuid => $aData) {
            $amenityRows[] = [
                'uuid' => $aUuid,
                'name' => $aData['name'],
                'display_name' => $aData['display_name'],
            ];
        }

        StarmapAmenity::upsert(
            $amenityRows,
            ['uuid'],
            ['name', 'display_name'],
        );

        $amenityIdMap = StarmapAmenity::query()
            ->whereIn('uuid', array_keys($amenityEntries))
            ->pluck('id', 'uuid')
            ->toArray();

        $locationDataIds = array_values($locationDataIdMap);

        if ($locationDataIds !== []) {
            DB::table('game_starmap_location_data_amenity')
                ->whereIn('location_data_id', $locationDataIds)
                ->delete();
        }

        $pivotRows = [];
        foreach ($entryAmenityUuids as $locationUuid => $aUuids) {
            $locationDataId = $locationDataIdMap[$locationIdMap[$locationUuid]] ?? null;

            if ($locationDataId === null) {
                continue;
            }

            foreach ($aUuids as $aUuid) {
                $amenityId = $amenityIdMap[$aUuid] ?? null;

                if ($amenityId === null) {
                    continue;
                }

                $pivotRows[] = [
                    'location_data_id' => $locationDataId,
                    'amenity_id' => $amenityId,
                ];
            }
        }

        if ($pivotRows !== []) {
            foreach (array_chunk($pivotRows, 500) as $chunk) {
                DB::table('game_starmap_location_data_amenity')->insert($chunk);
            }
        }
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
     * @param  list<string|null>  $usedSlugs
     */
    private function generateUniqueSlugInMemory(string $baseSlug, array &$usedSlugs): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while (in_array($slug, $usedSlugs, true)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
