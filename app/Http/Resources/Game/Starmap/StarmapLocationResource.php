<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Starmap;

use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_starmap_location_linked_summary',
    title: 'Game Starmap Location Linked Summary',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type_name', type: 'string'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_type',
    title: 'Game Starmap Location Type',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'classification', type: 'string', nullable: true),
        new OA\Property(property: 'spawn_nav_points', type: 'boolean', nullable: true),
        new OA\Property(property: 'valid_quantum_travel_destination', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_jurisdiction',
    title: 'Game Starmap Location Jurisdiction',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'base_fine', type: 'integer', nullable: true),
        new OA\Property(property: 'max_stolen_goods_possession_scu', type: 'integer', nullable: true),
        new OA\Property(property: 'is_prison', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_affiliation',
    title: 'Game Starmap Location Affiliation',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_amenity',
    title: 'Game Starmap Location Amenity',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'display_name', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_tag',
    title: 'Game Starmap Location Tag',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_radar_contact_type',
    title: 'Game Starmap Location Radar Contact Type',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'display_name', type: 'string', nullable: true),
        new OA\Property(property: 'tag_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'tag_name', type: 'string', nullable: true),
        new OA\Property(property: 'is_object_of_interest', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_child_summary',
    title: 'Game Starmap Location Child Summary',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'type_name', type: 'string'),
        new OA\Property(property: 'type_classification', type: 'string', nullable: true),
        new OA\Property(property: 'respawn_location_type', type: 'string', nullable: true),
        new OA\Property(
            property: 'amenities',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_starmap_location_amenity')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location',
    title: 'Game Starmap Location',
    description: 'Versioned starmap location data imported from game starmap data.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'size', type: 'number', nullable: true),
        new OA\Property(property: 'respawn_location_type', type: 'string', nullable: true),
        new OA\Property(property: 'child_count', type: 'integer'),
        new OA\Property(property: 'is_scannable', type: 'boolean'),
        new OA\Property(property: 'hide_in_starmap', type: 'boolean'),
        new OA\Property(property: 'hide_in_world', type: 'boolean'),
        new OA\Property(property: 'block_travel', type: 'boolean'),
        new OA\Property(property: 'quantum_travel', type: 'object', nullable: true),
        new OA\Property(property: 'asteroid_ring', type: 'object', nullable: true),
        new OA\Property(property: 'link', type: 'string'),
        new OA\Property(property: 'web_url', type: 'string'),
        new OA\Property(property: 'version', type: 'string', nullable: true),
        new OA\Property(property: 'system', type: 'string', nullable: true),
        new OA\Property(property: 'parent', ref: '#/components/schemas/game_starmap_location_linked_summary', nullable: true),
        new OA\Property(property: 'star', ref: '#/components/schemas/game_starmap_location_linked_summary', nullable: true),
        new OA\Property(
            property: 'children',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_starmap_location_child_summary'),
            nullable: true
        ),
        new OA\Property(property: 'type', ref: '#/components/schemas/game_starmap_location_type'),
        new OA\Property(property: 'jurisdiction', ref: '#/components/schemas/game_starmap_location_jurisdiction', nullable: true),
        new OA\Property(property: 'affiliation', ref: '#/components/schemas/game_starmap_location_affiliation', nullable: true),
        new OA\Property(
            property: 'amenities',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_starmap_location_amenity')
        ),
        new OA\Property(property: 'tag', ref: '#/components/schemas/game_starmap_location_tag', nullable: true),
        new OA\Property(property: 'radar_contact_type', ref: '#/components/schemas/game_starmap_location_radar_contact_type', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
    type: 'object'
)]
class StarmapLocationResource extends AbstractBaseResource
{
    public static function validIncludes(): array
    {
        return [
            'children',
        ];
    }

    public function toArray(Request $request): array
    {
        $locationData = $this->resource;
        $payload = $this->payload($locationData->data);

        return [
            'uuid' => $locationData->location?->uuid,
            'name' => $locationData->name,
            'description' => $locationData->description,
            'size' => $locationData->size,
            'respawn_location_type' => is_string(Arr::get($payload, 'respawnLocationType'))
                ? trim((string) Arr::get($payload, 'respawnLocationType')) ?: null
                : null,
            'child_count' => (int) ($locationData->child_count ?? 0),
            'is_scannable' => (bool) $locationData->is_scannable,
            'hide_in_starmap' => (bool) Arr::get($payload, 'hideInStarmap', false),
            'hide_in_world' => (bool) Arr::get($payload, 'hideInWorld', false),
            'block_travel' => (bool) $locationData->block_travel,
            'quantum_travel' => is_array(Arr::get($payload, 'quantumTravel')) && Arr::get($payload, 'quantumTravel') !== []
                ? Arr::get($payload, 'quantumTravel')
                : null,
            'asteroid_ring' => is_array(Arr::get($payload, 'asteroidRing')) && Arr::get($payload, 'asteroidRing') !== []
                ? Arr::get($payload, 'asteroidRing')
                : null,
            'system' => $locationData->system,
            'star' => $this->buildStarSummary($locationData),
            'parent' => $this->buildParentSummary($locationData),
            'type' => $this->buildType($locationData, $payload),
            'jurisdiction' => $this->buildJurisdiction($locationData, $payload),
            'affiliation' => $this->buildAffiliation($locationData, $payload),
            'amenities' => $this->buildAmenities($locationData),
            'tag' => $this->buildTag($locationData),
            'radar_contact_type' => $this->buildRadarContactType($payload),
            'link' => $this->buildApiUrl($locationData, $request),
            'web_url' => $this->buildWebUrl($locationData, $request),
            'updated_at' => $locationData->updated_at?->toIso8601String(),
            'version' => $locationData->gameVersion?->code,
            'children' => $this->whenLoaded('children', fn (): array => $locationData->children
                ->filter(static fn (StarmapLocationData $child): bool => $child->location !== null)
                ->map(fn (StarmapLocationData $child): array => $this->buildChildSummary($child))
                ->values()
                ->all()),
        ];
    }

    /**
     * @return array{uuid: string, name: string, web_url: string, type_name: string, type_classification: string|null, respawn_location_type: string|null, amenities: array<int, array{uuid: string, name: string, display_name: string|null}>, amenity_labels: array<int, string>}
     */
    private function buildChildSummary(StarmapLocationData $locationData): array
    {
        $payload = $this->payload($locationData->data);
        $amenities = $this->buildAmenities($locationData);

        return [
            'uuid' => $locationData->location->uuid,
            'name' => $locationData->name,
            'web_url' => $this->buildWebUrl($locationData, request()),
            'type_name' => $locationData->type_name,
            'type_classification' => is_string(Arr::get($payload, 'type.classification'))
                ? trim((string) Arr::get($payload, 'type.classification')) ?: null
                : null,
            'respawn_location_type' => is_string(Arr::get($payload, 'respawnLocationType'))
                ? trim((string) Arr::get($payload, 'respawnLocationType')) ?: null
                : null,
            'amenities' => $amenities,
            'amenity_labels' => $this->buildAmenityLabels($amenities),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(mixed $payload): array
    {
        return match (true) {
            is_array($payload) => $payload,
            $payload instanceof Collection => $payload->all(),
            default => [],
        };
    }

    private function buildApiUrl(StarmapLocationData $locationData, Request $request): string
    {
        return $this->urlWithVersion(
            route('locations.show', ['identifier' => $locationData->location?->uuid]),
            $request
        );
    }

    private function buildWebUrl(StarmapLocationData $locationData, Request $request): string
    {
        return $this->urlWithVersion(
            route('web.locations.show', ['identifier' => $locationData->location?->uuid]),
            $request
        );
    }

    /**
     * @return array{uuid: string, name: string, type_name: string}|null
     */
    private function buildStarSummary(StarmapLocationData $locationData): ?array
    {
        if (! $locationData->relationLoaded('star')) {
            return null;
        }

        return $this->buildLinkedLocationSummary($locationData->star);
    }

    /**
     * @return array{uuid: string, name: string, type_name: string}|null
     */
    private function buildParentSummary(StarmapLocationData $locationData): ?array
    {
        if (! $locationData->relationLoaded('parent')) {
            return null;
        }

        return $this->buildLinkedLocationSummary($locationData->parent);
    }

    /**
     * @return array{uuid: string, name: string, type_name: string}|null
     */
    private function buildLinkedLocationSummary(?StarmapLocationData $locationData): ?array
    {
        if ($locationData === null || $locationData->location === null) {
            return null;
        }

        return [
            'uuid' => $locationData->location->uuid,
            'name' => $locationData->name,
            'type_name' => $locationData->type_name,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string, classification: string|null, spawn_nav_points: bool|null, valid_quantum_travel_destination: bool|null}
     */
    private function buildType(StarmapLocationData $locationData, array $payload): array
    {
        return [
            'uuid' => Arr::get($payload, 'type.uuid'),
            'name' => $locationData->type_name,
            'classification' => is_string(Arr::get($payload, 'type.classification'))
                ? trim((string) Arr::get($payload, 'type.classification')) ?: null
                : null,
            'spawn_nav_points' => Arr::get($payload, 'type.spawnNavPoints'),
            'valid_quantum_travel_destination' => Arr::get($payload, 'type.validQuantumTravelDestination'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string|null, base_fine: int|null, max_stolen_goods_possession_scu: int|null, is_prison: bool|null}|null
     */
    private function buildJurisdiction(StarmapLocationData $locationData, array $payload): ?array
    {
        $jurisdiction = Arr::get($payload, 'jurisdiction');

        if (! is_array($jurisdiction)) {
            return null;
        }

        $hasMeaningfulValue = collect($jurisdiction)
            ->flatten()
            ->contains(static fn (mixed $value): bool => $value !== null && $value !== '');

        if (! $hasMeaningfulValue) {
            return null;
        }

        return [
            'uuid' => Arr::get($payload, 'jurisdiction.uuid'),
            'name' => is_string(Arr::get($payload, 'jurisdiction.name'))
                ? trim((string) Arr::get($payload, 'jurisdiction.name')) ?: null
                : null,
            'base_fine' => Arr::get($payload, 'jurisdiction.baseFine'),
            'max_stolen_goods_possession_scu' => Arr::get($payload, 'jurisdiction.maxStolenGoodsPossessionScu'),
            'is_prison' => Arr::get($payload, 'jurisdiction.isPrison'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string|null}|null
     */
    private function buildAffiliation(StarmapLocationData $locationData, array $payload): ?array
    {
        $affiliation = Arr::get($payload, 'affiliation');

        if (! is_array($affiliation)) {
            return null;
        }

        $hasMeaningfulValue = collect($affiliation)
            ->flatten()
            ->contains(static fn (mixed $value): bool => $value !== null && $value !== '');

        if (! $hasMeaningfulValue) {
            return null;
        }

        return [
            'uuid' => Arr::get($payload, 'affiliation.uuid'),
            'name' => is_string(Arr::get($payload, 'affiliation.displayName'))
                ? trim((string) Arr::get($payload, 'affiliation.displayName')) ?: null
                : (is_string(Arr::get($payload, 'affiliation.name'))
                    ? trim((string) Arr::get($payload, 'affiliation.name')) ?: null
                    : null),
        ];
    }

    /**
     * @return array<int, array{uuid: string, name: string, display_name: string|null}>
     */
    private function buildAmenities(StarmapLocationData $locationData): array
    {
        if (! $locationData->relationLoaded('amenities')) {
            return [];
        }

        return $locationData->amenities
            ->map(static fn ($amenity): array => [
                'uuid' => $amenity->uuid,
                'name' => $amenity->name,
                'display_name' => $amenity->display_name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{uuid: string, name: string, display_name: string|null}>  $amenities
     * @return array<int, string>
     */
    private function buildAmenityLabels(array $amenities): array
    {
        return collect($amenities)
            ->map(static fn (array $amenity): ?string => is_string($amenity['display_name'] ?? null) && trim((string) $amenity['display_name']) !== ''
                ? trim((string) $amenity['display_name'])
                : (is_string($amenity['name'] ?? null) && trim((string) $amenity['name']) !== ''
                    ? trim((string) $amenity['name'])
                    : null))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{uuid: string, name: string}|null
     */
    private function buildTag(StarmapLocationData $locationData): ?array
    {
        if (! $locationData->relationLoaded('locationHierarchyEntityTag') || $locationData->locationHierarchyEntityTag === null) {
            return null;
        }

        return [
            'uuid' => $locationData->locationHierarchyEntityTag->uuid,
            'name' => $locationData->locationHierarchyEntityTag->name,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string|null, display_name: string|null, tag_uuid: string|null, tag_name: string|null, is_object_of_interest: bool|null}|null
     */
    private function buildRadarContactType(array $payload): ?array
    {
        if (! Arr::has($payload, 'radarContactType')) {
            return null;
        }

        return [
            'uuid' => Arr::get($payload, 'radarContactType.uuid'),
            'name' => Arr::get($payload, 'radarContactType.name'),
            'display_name' => Arr::get($payload, 'radarContactType.displayName'),
            'tag_uuid' => Arr::get($payload, 'radarContactType.tagUuid'),
            'tag_name' => Arr::get($payload, 'radarContactType.tagName'),
            'is_object_of_interest' => Arr::get($payload, 'radarContactType.isObjectOfInterest'),
        ];
    }
}
