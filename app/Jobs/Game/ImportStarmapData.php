<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\EntityTag;
use App\Models\Game\StarmapAmenity;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use JsonException;

class ImportStarmapData implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const UNINITIALIZED = '<= UNINITIALIZED =>';

    public function __construct(
        private readonly int $gameVersionId,
        private readonly string $path = 'starmap.json',
    ) {}

    /**
     * @throws JsonException
     */
    public function handle(): void
    {
        $entries = $this->readPayload();

        if ($entries === []) {
            return;
        }

        DB::transaction(function () use ($entries): void {
            $firstPass = [];

            foreach ($entries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $uuid = $this->extractUuid($entry);

                if ($uuid === null) {
                    continue;
                }

                $location = StarmapLocation::query()->firstOrCreate(
                    ['uuid' => $uuid],
                    ['uuid' => $uuid]
                );

                $locationData = StarmapLocationData::query()->updateOrCreate(
                    [
                        'starmap_location_id' => $location->id,
                        'game_version_id' => $this->gameVersionId,
                    ],
                    $this->mapLocationData($entry)
                );

                $locationData->amenities()->sync($this->syncAmenities($entry));

                $firstPass[$uuid] = [
                    'location' => $location,
                    'location_data' => $locationData,
                    'entry' => $entry,
                ];
            }

            $this->resolveParents($firstPass);
            $this->resolveSystems($firstPass);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     *
     * @throws JsonException
     */
    private function readPayload(): array
    {
        $contents = Storage::disk('scunpacked')->get($this->path);
        $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        return is_array($payload) ? $payload : [];
    }

    private function extractUuid(array $entry): ?string
    {
        $uuid = $entry['uuid'] ?? null;

        if (! is_string($uuid)) {
            return null;
        }

        $uuid = trim($uuid);

        return $uuid === '' ? null : $uuid;
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    private function mapLocationData(array $entry): array
    {
        return [
            'parent_data_id' => null,
            'location_hierarchy_entity_tag_id' => $this->resolveLocationHierarchyEntityTagId($entry),
            'name' => $this->extractName($entry),
            'description' => $this->normalizeNullableString($entry['description'] ?? null),
            'type_name' => $this->extractTypeName($entry),
            'type_classification' => $this->normalizeNullableString(Arr::get($entry, 'type.classification')),
            'respawn_location_type' => $this->normalizeNullableString($entry['respawnLocationType'] ?? null) ?? 'None',
            'size' => $this->nullableFloat($entry['size'] ?? null),
            'minimum_display_size' => $this->nullableFloat($entry['minimumDisplaySize'] ?? null),
            'is_scannable' => (bool) ($entry['isScannable'] ?? false),
            'hide_in_starmap' => (bool) ($entry['hideInStarmap'] ?? false),
            'hide_in_world' => (bool) ($entry['hideInWorld'] ?? false),
            'block_travel' => (bool) ($entry['blockTravel'] ?? false),
            'jurisdiction_name' => $this->normalizeNullableString(Arr::get($entry, 'jurisdiction.name')),
            'jurisdiction_is_prison' => $this->nullableBoolean(Arr::get($entry, 'jurisdiction.isPrison')),
            'affiliation_name' => $this->normalizeNullableString(Arr::get($entry, 'affiliation.displayName')),
            'quantum_travel' => $this->nullableArray($entry['quantumTravel'] ?? null),
            'asteroid_ring' => $this->nullableArray($entry['asteroidRing'] ?? null),
            'data' => $entry,
        ];
    }

    private function extractName(array $entry): string
    {
        $name = $this->normalizeNullableString($entry['name'] ?? null);

        return $name ?? (string) ($entry['uuid'] ?? 'Unknown Location');
    }

    private function extractTypeName(array $entry): string
    {
        $typeName = $this->normalizeNullableString(Arr::get($entry, 'type.name'));

        return $typeName ?? 'Unknown';
    }

    private function resolveLocationHierarchyEntityTagId(array $entry): ?int
    {
        $uuid = Arr::get($entry, 'locationHierarchyTag.uuid');

        if (! is_string($uuid) || trim($uuid) === '') {
            return null;
        }

        $entityTag = EntityTag::query()->updateOrCreate(
            ['uuid' => trim($uuid)],
            ['name' => $this->normalizeNullableString(Arr::get($entry, 'locationHierarchyTag.name')) ?? trim($uuid)]
        );

        return $entityTag->id;
    }

    /**
     * @param  array<string, array{location: StarmapLocation, location_data: StarmapLocationData, entry: array<string, mixed>}>  $firstPass
     */
    private function resolveParents(array $firstPass): void
    {
        foreach ($firstPass as $uuid => $imported) {
            $parentUuid = $imported['entry']['parentUuid'] ?? null;

            if (! is_string($parentUuid) || trim($parentUuid) === '') {
                $imported['location_data']->update(['parent_data_id' => null]);

                continue;
            }

            $parent = $firstPass[$parentUuid]['location_data'] ?? null;

            $imported['location_data']->update([
                'parent_data_id' => $parent?->id,
            ]);
        }
    }

    /**
     * @param  array<string, array{location: StarmapLocation, location_data: StarmapLocationData, entry: array<string, mixed>}>  $firstPass
     */
    private function resolveSystems(array $firstPass): void
    {
        $resolvedSystems = [];

        foreach (array_keys($firstPass) as $uuid) {
            $systemUuid = $this->resolveSystemUuid($uuid, $firstPass, $resolvedSystems);

            $firstPass[$uuid]['location']->update([
                'system_uuid' => $systemUuid,
            ]);
        }
    }

    /**
     * @param  array<string, array{location: StarmapLocation, location_data: StarmapLocationData, entry: array<string, mixed>}>  $firstPass
     * @param  array<string, string|null>  $resolvedSystems
     */
    private function resolveSystemUuid(string $uuid, array $firstPass, array &$resolvedSystems): ?string
    {
        if (array_key_exists($uuid, $resolvedSystems)) {
            return $resolvedSystems[$uuid];
        }

        $current = $firstPass[$uuid]['entry'] ?? null;

        if ($current === null) {
            return $resolvedSystems[$uuid] = null;
        }

        if ($this->extractTypeName($current) === 'SolarSystem') {
            return $resolvedSystems[$uuid] = $uuid;
        }

        $parentUuid = $current['parentUuid'] ?? null;

        if (! is_string($parentUuid) || trim($parentUuid) === '') {
            return $resolvedSystems[$uuid] = null;
        }

        return $resolvedSystems[$uuid] = $this->resolveSystemUuid($parentUuid, $firstPass, $resolvedSystems);
    }

    /**
     * @return array<int, int>
     */
    private function syncAmenities(array $entry): array
    {
        return collect($entry['amenities'] ?? [])
            ->filter(fn (mixed $amenity): bool => is_array($amenity))
            ->map(function (array $amenity): ?int {
                $uuid = $amenity['uuid'] ?? null;

                if (! is_string($uuid) || trim($uuid) === '') {
                    return null;
                }

                $starmapAmenity = StarmapAmenity::query()->updateOrCreate(
                    ['uuid' => trim($uuid)],
                    [
                        'name' => $this->normalizeNullableString($amenity['name'] ?? null) ?? trim($uuid),
                        'display_name' => $this->normalizeNullableString($amenity['displayName'] ?? null),
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

        if ($value === '' || $value === self::UNINITIALIZED) {
            return null;
        }

        return $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function nullableBoolean(mixed $value): ?bool
    {
        if (! is_bool($value)) {
            return null;
        }

        return $value;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function nullableArray(mixed $value): ?array
    {
        return is_array($value) ? $value : null;
    }
}
