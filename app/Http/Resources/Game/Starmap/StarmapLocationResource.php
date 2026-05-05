<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Starmap;

use App\Enums\Game\ResourceKind;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Mission\MissionSummaryResource;
use App\Models\Game\StarmapLocationData;
use App\Support\Resources\HasDepositFormatting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'game_starmap_location_linked_summary',
    title: 'Game Starmap Location Linked Summary',
    description: 'A lightweight summary of a linked starmap location, used for parent, star, and related location references.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier of the linked location.', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', description: 'Display name of the linked location.', type: 'string'),
        new OA\Property(property: 'type_name', description: 'Type name of the linked location (e.g. Planet, Moon, Outpost).', type: 'string'),
        new OA\Property(property: 'slug', description: 'URL-friendly slug for the linked location.', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_type',
    title: 'Game Starmap Location Type',
    description: 'Classification and quantum travel properties of a starmap location type.',
    properties: [
        new OA\Property(property: 'uuid', description: 'UUID of the location type.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', description: 'Type name (e.g. Planet, Moon, Outpost, Asteroid).', type: 'string'),
        new OA\Property(property: 'classification', description: 'Sub-classification of the location type (e.g. Outpost, LandingZone).', type: 'string', nullable: true),
        new OA\Property(property: 'spawn_nav_points', description: 'Whether this location type spawns navigation points.', type: 'boolean', nullable: true),
        new OA\Property(property: 'valid_quantum_travel_destination', description: 'Whether this location type is a valid quantum travel destination.', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_jurisdiction',
    title: 'Game Starmap Location Jurisdiction',
    description: 'Legal jurisdiction governing a starmap location, including fines and prison status.',
    properties: [
        new OA\Property(property: 'uuid', description: 'UUID of the jurisdiction.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', description: 'Name of the governing jurisdiction (e.g. UEE).', type: 'string', nullable: true),
        new OA\Property(property: 'base_fine', description: 'Base fine amount for crimes committed in this jurisdiction.', type: 'integer', nullable: true),
        new OA\Property(property: 'max_stolen_goods_possession_scu', description: 'Maximum stolen goods possession allowed in SCU before penalties apply.', type: 'integer', nullable: true),
        new OA\Property(property: 'is_prison', description: 'Whether this location is a prison facility.', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_affiliation',
    title: 'Game Starmap Location Affiliation',
    description: 'Faction or organization affiliation of a starmap location.',
    properties: [
        new OA\Property(property: 'uuid', description: 'UUID of the affiliated faction or organization.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the affiliated faction or organization (e.g. UEE, Private Security).', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_amenity',
    title: 'Game Starmap Location Amenity',
    description: 'An amenity available at a starmap location (e.g. hospital, commodity trading, garage).',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier of the amenity.', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', description: 'Internal name of the amenity.', type: 'string'),
        new OA\Property(property: 'display_name', description: 'Human-readable display name of the amenity, may differ from internal name.', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_tag',
    title: 'Game Starmap Location Tag',
    description: 'A hierarchy entity tag used for grouping and filtering locations (e.g. orbital markers like HUR_L1).',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier of the hierarchy entity tag.', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', description: 'Tag name, typically an orbital or navigational marker code (e.g. HUR_L1, ARC_L2).', type: 'string'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_radar_contact_type',
    title: 'Game Starmap Location Radar Contact Type',
    description: 'Radar contact classification for a starmap location, used for navigation and object-of-interest detection.',
    properties: [
        new OA\Property(property: 'uuid', description: 'UUID of the radar contact type.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', description: 'Internal name of the radar contact type.', type: 'string', nullable: true),
        new OA\Property(property: 'display_name', description: 'Human-readable display name of the radar contact type.', type: 'string', nullable: true),
        new OA\Property(property: 'tag_uuid', description: 'UUID of the associated hierarchy entity tag.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'tag_name', description: 'Name of the associated hierarchy entity tag.', type: 'string', nullable: true),
        new OA\Property(property: 'is_object_of_interest', description: 'Whether this location is marked as an object of interest.', type: 'boolean', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_quantum_travel',
    title: 'Game Starmap Location Quantum Travel',
    description: 'Quantum travel parameters defining how ships interact with this location during quantum travel.',
    properties: [
        new OA\Property(property: 'obstruction_radius', description: 'Radius around the location that obstructs quantum travel.', type: 'number'),
        new OA\Property(property: 'arrival_radius', description: 'Radius at which a ship exits quantum travel near this location.', type: 'number'),
        new OA\Property(property: 'arrival_point_detection_offset', description: 'Positional offset for detecting the quantum travel arrival point.', type: 'number'),
        new OA\Property(property: 'adoption_radius', description: 'Radius within which child locations are adopted into the quantum travel zone.', type: 'number'),
        new OA\Property(property: 'sub_point_radius_multiplier', description: 'Multiplier applied to the sub-point radius for quantum travel calculations.', type: 'number'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_asteroid_ring',
    title: 'Game Starmap Location Asteroid Ring',
    description: 'Asteroid ring configuration for locations that have an asteroid belt, defining density, size, and dimensional parameters.',
    properties: [
        new OA\Property(property: 'density_scale', description: 'Scale factor controlling asteroid density within the ring.', type: 'number'),
        new OA\Property(property: 'size_scale', description: 'Scale factor controlling individual asteroid size.', type: 'number'),
        new OA\Property(property: 'inner_radius', description: 'Inner boundary radius of the asteroid ring.', type: 'number'),
        new OA\Property(property: 'outer_radius', description: 'Outer boundary radius of the asteroid ring.', type: 'number'),
        new OA\Property(property: 'depth', description: 'Vertical depth or thickness of the asteroid ring.', type: 'number'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_child_summary',
    title: 'Game Starmap Location Child Summary',
    description: 'Summary of a child location within a starmap hierarchy, including amenities and resource availability.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier of the child location.', type: 'string', format: 'uuid'),
        new OA\Property(property: 'name', description: 'Display name of the child location.', type: 'string'),
        new OA\Property(property: 'designation', description: 'Official designation code for the child location.', type: 'string', nullable: true),
        new OA\Property(property: 'web_url', description: 'Web frontend URL for the child location.', type: 'string', format: 'uri'),
        new OA\Property(property: 'type_name', description: 'Location type name (e.g. Outpost, Asteroid).', type: 'string'),
        new OA\Property(property: 'type_classification', description: 'Sub-classification of the location type.', type: 'string', nullable: true),
        new OA\Property(property: 'respawn_location_type', description: 'Type of respawn facility available, if any.', type: 'string', nullable: true),
        new OA\Property(
            property: 'amenities',
            description: 'Amenities available at this child location.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_starmap_location_amenity')
        ),
        new OA\Property(
            property: 'amenity_labels',
            description: 'Simplified list of amenity display names or internal names.',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(property: 'has_resources', description: 'Whether this child location has harvestable resource deposits.', type: 'boolean'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'starmap_location_resource',
    title: 'Starmap Location Resource',
    description: 'A mineable or harvestable resource deposit at a starmap location, combining deposit configuration with commodity identity.',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/deposit_base'),
        new OA\Schema(
            properties: [
                new OA\Property(property: 'name', description: 'Commodity name of the resource deposit.', type: 'string'),
                new OA\Property(property: 'uuid', description: 'UUID of the commodity, null for non-commodity deposits.', type: 'string', format: 'uuid', nullable: true),
                new OA\Property(property: 'tier', description: 'Rarity tier of the commodity.', type: 'string', nullable: true),
                new OA\Property(property: 'link', description: 'API URL for the commodity detail endpoint.', type: 'string', format: 'uri', nullable: true),
                new OA\Property(property: 'web_url', description: 'Web frontend URL for the commodity detail page.', type: 'string', format: 'uri', nullable: true),
            ],
            type: 'object'
        ),
    ]
)]
#[OA\Schema(
    schema: 'starmap_location_mining_type_group',
    title: 'Starmap Location Mining Type Group',
    description: 'A group of mineable or harvestable deposits at a location, categorized by extraction method (e.g. mining, hand-mining, salvage).',
    properties: [
        new OA\Property(property: 'group_name', description: 'Original group name from game data identifying the deposit group.', type: 'string'),
        new OA\Property(property: 'mining_type', description: 'Extraction method label (e.g. Mining, Hand Mining, Salvage).', type: 'string'),
        new OA\Property(property: 'mining_type_sort_order', description: 'Sort order for mining type display ordering.', type: 'integer'),
        new OA\Property(property: 'resource_kind', description: 'Kind of resource (Mineable or Harvestable).', type: 'string', nullable: true),
        new OA\Property(property: 'group_probability_min', description: 'Lowest raw probability among deposits in this group (0–1).', type: 'number', nullable: true),
        new OA\Property(property: 'group_probability_max', description: 'Highest raw probability among deposits in this group (0–1).', type: 'number', nullable: true),
        new OA\Property(property: 'group_probability_min_percent', description: 'Lowest probability in this group as a percentage (0–100).', type: 'number', nullable: true),
        new OA\Property(property: 'group_probability_max_percent', description: 'Highest probability in this group as a percentage (0–100).', type: 'number', nullable: true),
        new OA\Property(
            property: 'resources',
            description: 'Individual resource deposits within this group.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/starmap_location_resource')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location_mission_group',
    title: 'Game Starmap Location Mission Group',
    description: 'Missions associated with a starmap location, grouped by purpose.',
    properties: [
        new OA\Property(property: 'purpose', description: 'Mission purpose category (e.g. Mission, Patrol, Investigation).', type: 'string', nullable: true),
        new OA\Property(
            property: 'missions',
            description: 'List of mission summaries in this group.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_summary')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'game_starmap_location',
    title: 'Game Starmap Location',
    description: 'Versioned starmap location data imported from game starmap data.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique identifier for this starmap location.', type: 'string', format: 'uuid'),
        new OA\Property(property: 'slug', description: 'URL-friendly slug for this location.', type: 'string', nullable: true),
        new OA\Property(property: 'name', description: 'Display name of the starmap location.', type: 'string'),
        new OA\Property(property: 'description', description: 'In-universe description of the location.', type: 'string', nullable: true),
        new OA\Property(property: 'size', description: 'Relative size of the location.', type: 'number', nullable: true),
        new OA\Property(property: 'respawn_location_type', description: 'Type of respawn facility available (e.g. Hospital, MedicalRoom).', type: 'string', nullable: true),
        new OA\Property(property: 'child_count', description: 'Number of direct child locations.', type: 'integer'),
        new OA\Property(property: 'has_resources', description: 'Whether this location has harvestable resource deposits.', type: 'boolean', nullable: true),
        new OA\Property(property: 'mission_count', description: 'Number of available missions at this location.', type: 'integer'),
        new OA\Property(property: 'is_scannable', description: 'Whether this location can be detected by ship scanners.', type: 'boolean'),
        new OA\Property(property: 'hide_in_starmap', description: 'Whether this location is hidden from the in-game starmap.', type: 'boolean'),
        new OA\Property(property: 'hide_in_world', description: 'Whether this location is hidden in the game world.', type: 'boolean'),
        new OA\Property(property: 'block_travel', description: 'Whether quantum travel to this location is blocked.', type: 'boolean'),
        new OA\Property(property: 'quantum_travel', ref: '#/components/schemas/game_starmap_location_quantum_travel', description: 'Quantum travel parameters for this location.', nullable: true),
        new OA\Property(property: 'asteroid_ring', ref: '#/components/schemas/game_starmap_location_asteroid_ring', description: 'Asteroid ring parameters, only present on locations with asteroid rings.', nullable: true),
        new OA\Property(property: 'system', description: 'Name of the star system this location belongs to (e.g. Stanton System).', type: 'string', nullable: true),
        new OA\Property(property: 'parent', ref: '#/components/schemas/game_starmap_location_linked_summary', description: 'Parent location in the hierarchy.', nullable: true),
        new OA\Property(property: 'star', ref: '#/components/schemas/game_starmap_location_linked_summary', description: 'Nearest star or celestial body.', nullable: true),
        new OA\Property(
            property: 'children',
            description: 'Direct child locations. Only included when requested via `include=children`.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_starmap_location_child_summary'),
            nullable: true
        ),
        new OA\Property(property: 'type', ref: '#/components/schemas/game_starmap_location_type', description: 'Location type classification and properties.'),
        new OA\Property(property: 'jurisdiction', ref: '#/components/schemas/game_starmap_location_jurisdiction', description: 'Legal jurisdiction governing this location.', nullable: true),
        new OA\Property(property: 'affiliation', ref: '#/components/schemas/game_starmap_location_affiliation', description: 'Faction or organization controlling this location.', nullable: true),
        new OA\Property(
            property: 'amenities',
            description: 'Available amenities at this location.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_starmap_location_amenity')
        ),
        new OA\Property(property: 'tag', ref: '#/components/schemas/game_starmap_location_tag', description: 'Hierarchy entity tag for grouping and filtering.', nullable: true),
        new OA\Property(property: 'designation', description: 'Official designation code for this location.', type: 'string', nullable: true),
        new OA\Property(property: 'radar_contact_type', ref: '#/components/schemas/game_starmap_location_radar_contact_type', description: 'Radar contact classification for navigation.', nullable: true),
        new OA\Property(property: 'link', description: 'API URL for this location\'s detail endpoint.', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', description: 'Web frontend URL for this location.', type: 'string', format: 'uri'),
        new OA\Property(property: 'updated_at', description: 'Timestamp of the last data update.', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'version', description: 'Game data version code.', type: 'string', nullable: true),
        new OA\Property(
            property: 'area_boosts',
            description: 'Areas with boosted deposit spawn rates. Only included when requested via `include=resources`.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/area_boost'),
            nullable: true
        ),
        new OA\Property(
            property: 'resources',
            description: 'Harvestable resource deposits grouped by extraction method. Only included when requested via `include=resources`.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/starmap_location_mining_type_group')
        ),
        new OA\Property(
            property: 'missions',
            description: 'Available missions grouped by purpose. Only included when requested via `include=missions`.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/game_starmap_location_mission_group'),
            nullable: true
        ),
    ],
    type: 'object'
)]
class StarmapLocationResource extends AbstractBaseResource
{
    use HasDepositFormatting;

    public static function validIncludes(): array
    {
        return [
            'children',
            'resources',
            'missions',
        ];
    }

    public function toArray(Request $request): array
    {
        $locationData = $this->resource;
        $payload = $this->payload($locationData->data);

        return [
            'uuid' => $locationData->location?->uuid,
            'slug' => $locationData->location?->slug,
            'name' => $locationData->name,
            'description' => $locationData->description,
            'size' => $locationData->size,
            'respawn_location_type' => is_string(Arr::get($payload, 'RespawnLocationType'))
                ? trim((string) Arr::get($payload, 'RespawnLocationType')) ?: null
                : null,
            'child_count' => (int) ($locationData->child_count ?? 0),
            'has_resources' => array_key_exists('has_resources', $locationData->getAttributes())
                ? (bool) ($locationData->getAttributes()['has_resources'])
                : null,
            'mission_count' => (int) ($locationData->mission_count ?? 0),
            'is_scannable' => (bool) $locationData->is_scannable,
            'hide_in_starmap' => (bool) Arr::get($payload, 'HideInStarmap', false),
            'hide_in_world' => (bool) Arr::get($payload, 'HideInWorld', false),
            'block_travel' => (bool) $locationData->block_travel,
            'quantum_travel' => is_array(Arr::get($payload, 'QuantumTravel')) && Arr::get($payload, 'QuantumTravel') !== []
                ? collect(Arr::get($payload, 'QuantumTravel'))->mapWithKeys(fn (mixed $value, string|int $key) => [str((string) $key)->snake()->value() => $value])->all()
                : null,
            'asteroid_ring' => is_array(Arr::get($payload, 'AsteroidRing')) && Arr::get($payload, 'AsteroidRing') !== []
                ? collect(Arr::get($payload, 'AsteroidRing'))->mapWithKeys(fn (mixed $value, string|int $key) => [str((string) $key)->snake()->value() => $value])->all()
                : null,
            'system' => $locationData->system,
            'star' => $this->buildStarSummary($locationData),
            'parent' => $this->buildParentSummary($locationData),
            'type' => $this->buildType($locationData, $payload),
            'jurisdiction' => $this->buildJurisdiction($locationData, $payload),
            'affiliation' => $this->buildAffiliation($locationData, $payload),
            'amenities' => $this->buildAmenities($locationData),
            'tag' => $this->buildTag($locationData),
            'designation' => $this->buildDesignation($locationData),
            'radar_contact_type' => $this->buildRadarContactType($payload),
            'link' => $this->buildApiUrl($locationData, $request),
            'web_url' => $this->buildWebUrl($locationData, $request),
            'images' => $locationData->location?->images ?? [],
            'updated_at' => $locationData->updated_at?->toIso8601String(),
            'version' => $locationData->gameVersion?->code,
            'children' => $this->whenLoaded('children', fn (): array => $locationData->children
                ->filter(static fn (StarmapLocationData $child): bool => $child->location !== null)
                ->map(fn (StarmapLocationData $child): array => $this->buildChildSummary($child))
                ->values()
                ->all()),
            'area_boosts' => $this->whenLoaded('resourceLocations', fn (): ?array => self::formatAllAreas(
                $locationData->resourceLocations->first()?->provider?->areas
            )),
            'resources' => $this->whenLoaded('resourceLocations', fn (): array => $this->buildResources($locationData, $request)),
            'missions' => $this->whenLoaded('missions', fn (): array => $this->buildMissions($locationData, $request)),
        ];
    }

    /**
     * @return array{uuid: string, name: string, web_url: string, type_name: string, type_classification: string|null, respawn_location_type: string|null, amenities: array<int, array{uuid: string, name: string, display_name: string|null}>, amenity_labels: array<int, string>, has_resources: bool}
     */
    private function buildChildSummary(StarmapLocationData $locationData): array
    {
        $payload = $this->payload($locationData->data);
        $amenities = $this->buildAmenities($locationData);

        return [
            'uuid' => $locationData->location->uuid,
            'name' => $locationData->name,
            'designation' => $this->buildDesignation($locationData),
            'web_url' => $this->buildWebUrl($locationData, request()),
            'type_name' => $locationData->type_name,
            'type_classification' => is_string(Arr::get($payload, 'Type.Classification'))
                ? trim((string) Arr::get($payload, 'Type.Classification')) ?: null
                : null,
            'respawn_location_type' => is_string(Arr::get($payload, 'RespawnLocationType'))
                ? trim((string) Arr::get($payload, 'RespawnLocationType')) ?: null
                : null,
            'amenities' => $amenities,
            'amenity_labels' => $this->buildAmenityLabels($amenities),
            'has_resources' => (bool) ($locationData->getAttributes()['has_resources'] ?? false),
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
        $identifier = $locationData->location?->slug ?? $locationData->location?->uuid;

        return $this->urlWithVersion(
            route('web.locations.show', ['identifier' => $identifier]),
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
     * @return array{uuid: string, name: string, type_name: string, slug: string|null}|null
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
            'slug' => $locationData->location->slug,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string, classification: string|null, spawn_nav_points: bool|null, valid_quantum_travel_destination: bool|null}
     */
    private function buildType(StarmapLocationData $locationData, array $payload): array
    {
        return [
            'uuid' => Arr::get($payload, 'Type.UUID'),
            'name' => $locationData->type_name,
            'classification' => is_string(Arr::get($payload, 'Type.Classification'))
                ? trim((string) Arr::get($payload, 'Type.Classification')) ?: null
                : null,
            'spawn_nav_points' => Arr::get($payload, 'Type.SpawnNavPoints'),
            'valid_quantum_travel_destination' => Arr::get($payload, 'Type.ValidQuantumTravelDestination'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string|null, base_fine: int|null, max_stolen_goods_possession_scu: int|null, is_prison: bool|null}|null
     */
    private function buildJurisdiction(StarmapLocationData $locationData, array $payload): ?array
    {
        $jurisdiction = Arr::get($payload, 'Jurisdiction');

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
            'uuid' => Arr::get($payload, 'Jurisdiction.UUID'),
            'name' => is_string(Arr::get($payload, 'Jurisdiction.Name'))
                ? trim((string) Arr::get($payload, 'Jurisdiction.Name')) ?: null
                : null,
            'base_fine' => Arr::get($payload, 'Jurisdiction.BaseFine'),
            'max_stolen_goods_possession_scu' => Arr::get($payload, 'Jurisdiction.MaxStolenGoodsPossessionScu'),
            'is_prison' => Arr::get($payload, 'Jurisdiction.IsPrison'),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string|null}|null
     */
    private function buildAffiliation(StarmapLocationData $locationData, array $payload): ?array
    {
        $affiliation = Arr::get($payload, 'Affiliation');

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
            'uuid' => Arr::get($payload, 'Affiliation.UUID'),
            'name' => is_string(Arr::get($payload, 'Affiliation.DisplayName'))
                ? trim((string) Arr::get($payload, 'Affiliation.DisplayName')) ?: null
                : (is_string(Arr::get($payload, 'Affiliation.Name'))
                    ? trim((string) Arr::get($payload, 'Affiliation.Name')) ?: null
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

    private function buildDesignation(StarmapLocationData $locationData): ?string
    {
        return $locationData->designation;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{uuid: string|null, name: string|null, display_name: string|null, tag_uuid: string|null, tag_name: string|null, is_object_of_interest: bool|null}|null
     */
    private function buildRadarContactType(array $payload): ?array
    {
        if (! Arr::has($payload, 'RadarContactType')) {
            return null;
        }

        return [
            'uuid' => Arr::get($payload, 'RadarContactType.UUID'),
            'name' => Arr::get($payload, 'RadarContactType.Name'),
            'display_name' => Arr::get($payload, 'RadarContactType.DisplayName'),
            'tag_uuid' => Arr::get($payload, 'RadarContactType.TagUUID'),
            'tag_name' => Arr::get($payload, 'RadarContactType.TagName'),
            'is_object_of_interest' => Arr::get($payload, 'RadarContactType.IsObjectOfInterest'),
        ];
    }

    private function buildResources(StarmapLocationData $locationData, Request $request): array
    {
        $resourceLocations = $locationData->resourceLocations;

        $pairs = $resourceLocations->flatMap(function ($resourceLocation) {
            $resourceData = $resourceLocation->resourceData;
            if ($resourceData === null) {
                return collect();
            }

            if ($resourceData->commodities->isNotEmpty()) {
                return $resourceData->commodities->map(fn ($commodity) => [
                    'commodity' => $commodity,
                    'resourceLocation' => $resourceLocation,
                ]);
            }

            $synthetic = (object) [
                'id' => $resourceData->id,
                'uuid' => null,
                'name' => self::parseDepositLabel($resourceData->key),
                'tier' => null,
            ];

            return collect([[
                'commodity' => $synthetic,
                'resourceLocation' => $resourceLocation,
            ]]);
        });

        return $pairs
            ->groupBy(fn (array $pair): string => self::resolveMiningType($pair['resourceLocation']->group_name)['label'])
            ->map(fn (Collection $groupPairs): array => $this->buildMiningTypeGroup($groupPairs, $request))
            ->sortBy(static fn (array $group): int => $group['mining_type_sort_order'])
            ->values()
            ->all();
    }

    private function buildMiningTypeGroup(Collection $groupPairs, Request $request): array
    {
        $first = $groupPairs->first();
        $miningType = self::resolveMiningType($first['resourceLocation']->group_name);

        $groupProbMin = $groupPairs->min(fn (array $pair): float => (float) $pair['resourceLocation']->group_probability);
        $groupProbMax = $groupPairs->max(fn (array $pair): float => (float) $pair['resourceLocation']->group_probability);

        $isMineable = $first['resourceLocation']->resource_kind === ResourceKind::Mineable;

        $resources = $groupPairs
            ->groupBy(fn (array $pair): string => $isMineable
                ? $pair['resourceLocation']->resourceData->key.'@'.($pair['resourceLocation']->resource_provider_id ?? 'none')
                : (string) $pair['resourceLocation']->resourceData->id)
            ->map(fn (Collection $depositPairs): array => $this->buildDepositResource($depositPairs, $request))
            ->sortBy('name')
            ->values()
            ->all();

        return [
            'group_name' => $first['resourceLocation']->group_name,
            'mining_type' => $miningType['label'],
            'mining_type_sort_order' => $miningType['sort_order'],
            'resource_kind' => $first['resourceLocation']->resource_kind,
            'group_probability_min' => $groupProbMin,
            'group_probability_max' => $groupProbMax,
            'group_probability_min_percent' => self::formatPercent($groupProbMin),
            'group_probability_max_percent' => self::formatPercent($groupProbMax),
            'resources' => $resources,
        ];
    }

    private function buildDepositResource(Collection $depositPairs, Request $request): array
    {
        $representative = $depositPairs->first()['resourceLocation'];
        $resourceData = $representative->resourceData;

        $uniqueResourceLocations = $depositPairs
            ->unique(fn (array $pair): int => $pair['resourceLocation']->id)
            ->map(static fn (array $pair): array => [
                'resourceLocation' => $pair['resourceLocation'],
            ]);

        $primaryCommodity = $resourceData->commodities
            ->sortByDesc(static fn ($commodity): float => (float) $commodity->pivot->max_percentage)
            ->first();

        $depositBase = self::buildDepositBase($uniqueResourceLocations, $resourceData, $primaryCommodity?->id);

        return [
            'key' => $depositBase['key'],
            'name' => $primaryCommodity?->name ?? $depositBase['label'],
            'label' => $depositBase['label'],
            'uuid' => $primaryCommodity?->uuid,
            'tier' => $primaryCommodity?->tier,
            'link' => $primaryCommodity?->uuid !== null ? $this->urlWithVersion(
                route('commodities.show', ['commodity' => $primaryCommodity->uuid]),
                $request,
            ) : null,
            'web_url' => $primaryCommodity?->uuid !== null ? $this->urlWithVersion(
                route('web.commodities.show', ['identifier' => $primaryCommodity->uuid]),
                $request,
            ) : null,
            'signature' => $depositBase['signature'],
            'area_exceptions' => $depositBase['area_exceptions'],
            'clustering' => $depositBase['clustering'],
            'harvestable_setup' => $depositBase['harvestable_setup'],
            'provider_names' => $depositBase['provider_names'],
            'materials' => $depositBase['materials'],
            'quality_min' => $depositBase['quality_min'],
            'quality_max' => $depositBase['quality_max'],
            'relative_probability_min' => $depositBase['relative_probability_min'],
            'relative_probability_max' => $depositBase['relative_probability_max'],
            'relative_probability_min_percent' => $depositBase['relative_probability_min_percent'],
            'relative_probability_max_percent' => $depositBase['relative_probability_max_percent'],
        ];
    }

    private function buildMissions(StarmapLocationData $locationData, Request $request): array
    {
        return $locationData->missions
            ->groupBy(fn ($mission): string => $mission->pivot->purpose ?? 'Unknown')
            ->map(fn (Collection $group, string $purpose): array => [
                'purpose' => $purpose,
                'missions' => MissionSummaryResource::collection($group)->resolve($request),
            ])
            ->sortBy('purpose', SORT_STRING | SORT_FLAG_CASE)
            ->values()
            ->all();
    }
}
