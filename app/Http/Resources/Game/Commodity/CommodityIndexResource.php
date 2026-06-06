<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Commodity;

use App\Enums\Game\ResourceKind;
use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'commodity_index_location_entry',
    title: 'Commodity Index Location Entry',
    description: 'A grouped mining entry within a location, summarizing a specific resource group.',
    properties: [
        new OA\Property(property: 'group_name', description: 'Internal group name identifying the mining method or category (e.g. "SpaceShip_Mineables", "Harvestables").', type: 'string'),
        new OA\Property(property: 'resource_kind', description: 'Kind of resource if resolvable from the group (e.g. "Mineable", "Harvestable").', type: 'string', nullable: true),
        new OA\Property(property: 'quality_min', description: 'Minimum quality value across entries in this group.', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', description: 'Maximum quality value across entries in this group.', type: 'integer', nullable: true),
        new OA\Property(property: 'entry_count', description: 'Number of individual resource entries in this group.', type: 'integer'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_index_location',
    title: 'Commodity Index Location',
    description: 'A named location where a commodity can be found, with grouped entries by mining method.',
    properties: [
        new OA\Property(property: 'name', description: 'Canonical location name.', type: 'string'),
        new OA\Property(property: 'display_name', description: 'Formatted display name including designation prefix (e.g. "CRU-L1: Green Circle").', type: 'string'),
        new OA\Property(property: 'system', description: 'Star system name this location belongs to.', type: 'string', nullable: true),
        new OA\Property(property: 'type', description: 'Location type classification (e.g. "Moon", "Planet", "Outpost").', type: 'string', nullable: true),
        new OA\Property(property: 'parent_name', description: 'Name of the parent celestial body or location.', type: 'string', nullable: true),
        new OA\Property(property: 'parent_type', description: 'Type of the parent location.', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', description: 'UUID of the starmap location entity.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', description: 'API link to the full location details.', type: 'string', format: 'uri', nullable: true),
        new OA\Property(
            property: 'entries',
            description: 'Resource entries grouped by mining method.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_index_location_entry')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_link',
    title: 'Commodity Link',
    description: 'Game commodity summary used in list responses.',
    properties: [
        new OA\Property(property: 'uuid', description: 'Unique commodity identifier.', type: 'string', format: 'uuid'),
        new OA\Property(property: 'key', description: 'Internal commodity key (e.g. "Quartz").', type: 'string'),
        new OA\Property(property: 'name', description: 'Display name of the commodity.', type: 'string'),
        new OA\Property(property: 'display_name', description: 'Name with leaf commodity group in parentheses, e.g. "WiDoW (Vice)".', type: 'string'),
        new OA\Property(property: 'slug', description: 'URL-friendly slug for the commodity.', type: 'string'),
        new OA\Property(property: 'description', description: 'In-game lore description.', type: 'string', nullable: true),
        new OA\Property(property: 'tier', description: 'Refinement tier (e.g. "Raw", "Refined").', type: 'string', nullable: true),
        new OA\Property(
            property: 'refined_version',
            description: 'The refined counterpart of this raw commodity, if applicable.',
            properties: [
                new OA\Property(property: 'name', description: 'Name of the refined commodity.', type: 'string'),
                new OA\Property(property: 'uuid', description: 'UUID of the refined commodity.', type: 'string', format: 'uuid'),
                new OA\Property(property: 'web_url', description: 'Frontend URL for the refined commodity page.', type: 'string', format: 'uri'),
                new OA\Property(property: 'link', description: 'API link to the refined commodity details.', type: 'string', format: 'uri'),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'density_g_per_cc', description: 'Density in grams per cubic centimeter.', type: 'number', nullable: true),
        new OA\Property(property: 'instability', description: 'Instability rating affecting mining behavior.', type: 'number', nullable: true),
        new OA\Property(property: 'resistance', description: 'Resistance rating affecting mining difficulty.', type: 'number', nullable: true),
        new OA\Property(
            property: 'box_sizes_scu',
            description: 'Standard cargo box sizes in SCU that this commodity fits into.',
            type: 'array',
            items: new OA\Items(type: 'number')
        ),
        new OA\Property(property: 'validate_default_cargo_box', description: 'Whether the default cargo box validation applies.', type: 'boolean'),
        new OA\Property(property: 'has_default_cargo_containers', description: 'Whether default cargo containers are available for this commodity.', type: 'boolean'),
        new OA\Property(property: 'is_mineable', description: 'Whether this commodity can be obtained through mining or harvesting.', type: 'boolean'),
        new OA\Property(property: 'has_ship_mineables', description: 'Whether ship mining deposits exist for this commodity.', type: 'boolean'),
        new OA\Property(property: 'has_ground_vehicle_mineables', description: 'Whether ground vehicle mining deposits exist for this commodity.', type: 'boolean'),
        new OA\Property(property: 'has_fps_mineables', description: 'Whether FPS mining deposits exist for this commodity.', type: 'boolean'),
        new OA\Property(property: 'has_harvestables', description: 'Whether harvestable deposits exist for this commodity.', type: 'boolean'),
        new OA\Property(property: 'has_salvage', description: 'Whether salvage deposits exist for this commodity.', type: 'boolean'),
        new OA\Property(property: 'signature', description: 'Electromagnetic signature strength, used for scanner detection.', type: 'integer', nullable: true),
        new OA\Property(property: 'kind', description: 'Resource kind classification (e.g. "Mineable", "Harvestable").', type: 'string', nullable: true),
        new OA\Property(
            property: 'commodity_groups',
            description: 'Ordered commodity groups from root to leaf (e.g. ["ProcessedGoods", "Vice"]).',
            type: 'array',
            items: new OA\Items(type: 'string'),
            nullable: true
        ),
        new OA\Property(
            property: 'methods',
            description: 'Available extraction methods (e.g. ["Ship", "Ground Vehicle", "FPS"]).',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(
            property: 'systems',
            description: 'Star systems where this commodity can be found.',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(
            property: 'locations',
            description: 'Named locations where this commodity appears.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_index_location')
        ),
        new OA\Property(property: 'link', description: 'API link to this commodity\'s full details.', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', description: 'Frontend URL for this commodity\'s page.', type: 'string', format: 'uri'),
        new OA\Property(
            property: 'images',
            description: 'Images from external sources for this commodity.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'source', description: 'Image source identifier', type: 'string'),
                    new OA\Property(property: 'thumbnail_url', type: 'string', nullable: true),
                    new OA\Property(property: 'thumbnail_width', type: 'integer', nullable: true),
                    new OA\Property(property: 'thumbnail_height', type: 'integer', nullable: true),
                    new OA\Property(property: 'original_url', type: 'string', nullable: true),
                    new OA\Property(property: 'original_width', type: 'integer', nullable: true),
                    new OA\Property(property: 'original_height', type: 'integer', nullable: true),
                ],
                type: 'object'
            ),
        ),
    ],
    type: 'object'
)]
class CommodityIndexResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $resourceDataCollection = $this->resource->resourceData ?? collect();
        $locations = $this->buildLocations($resourceDataCollection);
        $groupNames = $this->extractGroupNames($resourceDataCollection);
        ['hasShip' => $hasShip, 'hasGround' => $hasGround, 'hasFps' => $hasFps, 'hasHarvestable' => $hasHarvestable, 'hasSalvage' => $hasSalvage] = $this->resolveFlags($groupNames);

        $kind = ($first = $resourceDataCollection->first()) ? ($first->locations->first()?->resource_kind?->value ?? ($first->kind instanceof ResourceKind ? $first->kind->value : $first->kind)) : null;

        $commodityGroups = Arr::get($this->resource->data, 'CommodityGroups');
        $commodityGroups = is_array($commodityGroups) ? $commodityGroups : null;
        $leafGroup = $commodityGroups !== null && $commodityGroups !== [] ? end($commodityGroups) : null;
        $displayName = $leafGroup !== false && $leafGroup !== null
            ? $this->resource->name.' ('.$leafGroup.')'
            : $this->resource->name;

        return [
            'uuid' => $this->resource->uuid,
            'key' => $this->resource->key,
            'name' => $this->resource->name,
            'display_name' => $displayName,
            'slug' => $this->resource->slug,
            'description' => $this->resource->description,
            'tier' => $this->resource->tier,
            'refined_version' => $this->when($this->resource->refinedVersion, fn () => [
                'name' => $this->resource->refined_version_name,
                'uuid' => $this->resource->refinedVersion->uuid,
                'web_url' => $this->urlWithVersion(
                    route(
                        'web.commodities.show',
                        ['identifier' => $this->resource->refinedVersion->slug ?? $this->resource->refinedVersion->uuid]
                    ),
                    $request,
                ),
                'link' => $this->urlWithVersion(
                    route('commodities.show', ['commodity' => $this->resource->refinedVersion->uuid]),
                    $request,
                ),
            ]),
            'density_g_per_cc' => $this->formatDecimal($this->resource->density_g_per_cc, 2),
            'instability' => $this->formatDecimal($this->resource->instability, 0),
            'resistance' => $this->formatDecimal($this->resource->resistance, 2),
            'box_sizes_scu' => $this->resource->box_sizes_scu ?? [],
            'validate_default_cargo_box' => $this->resource->validate_default_cargo_box,
            'has_default_cargo_containers' => $this->resource->has_default_cargo_containers,

            'is_mineable' => $resourceDataCollection->isNotEmpty(),
            'has_ship_mineables' => $hasShip,
            'has_ground_vehicle_mineables' => $hasGround,
            'has_fps_mineables' => $hasFps,
            'has_harvestables' => $hasHarvestable,
            'has_salvage' => $hasSalvage,

            'signature' => ($sig = $resourceDataCollection->first()?->signature) !== null && $sig > 0 ? $sig : null,
            'kind' => empty($kind) ? null : $kind,
            'methods' => $this->buildMethodsFromFlags($hasShip, $hasGround, $hasFps, $hasHarvestable, $hasSalvage),
            'systems' => $this->buildSystems($locations),
            'locations' => $locations,

            'commodity_groups' => $commodityGroups,

            'link' => $this->urlWithVersion(
                route('commodities.show', ['commodity' => $this->resource->uuid]),
                $request,
            ),
            'web_url' => $this->urlWithVersion(
                route('web.commodities.show', ['identifier' => $this->resource->slug ?? $this->resource->uuid]),
                $request,
            ),
            'images' => $this->resource->images ?? [],
        ];
    }

    protected function buildLocations(Collection $resourceDataCollection): array
    {
        $flatPairs = $this->flattenLocationPairs($resourceDataCollection);

        $grouped = $flatPairs
            ->filter(static fn (array $pair): bool => ! Str::isUuid($pair['locationData']->name))
            ->groupBy(static fn (array $pair): string => $pair['locationData']->name.'@'.($pair['locationData']->system ?? ''));

        return $grouped
            ->map(function (Collection $pairs): array {
                $firstLocationData = $pairs->first()['locationData'];
                $designation = $firstLocationData->designation;
                $displayName = $designation !== null ? "{$designation}: {$firstLocationData->name}" : $firstLocationData->name;

                return [
                    'name' => $firstLocationData->name,
                    'display_name' => $displayName,
                    'system' => $firstLocationData->system,
                    'type' => $firstLocationData->type_name,
                    'parent_name' => $firstLocationData->parent?->name,
                    'parent_type' => $firstLocationData->parent?->type_name,
                    'uuid' => $firstLocationData->location?->uuid,
                    'link' => $firstLocationData->location?->uuid
                        ? route('locations.show', ['identifier' => $firstLocationData->location->uuid])
                        : null,
                    'entries' => $pairs
                        ->groupBy(static fn (array $pair): string => $pair['resourceLocation']->group_name)
                        ->map(static function (Collection $groupedEntries): array {
                            $first = $groupedEntries->first()['resourceLocation'];

                            return [
                                'group_name' => $first->group_name,
                                'resource_kind' => $first->resource_kind instanceof ResourceKind ? $first->resource_kind->value : $first->resource_kind,
                                'quality_min' => $groupedEntries->min(static fn (array $pair) => $pair['resourceLocation']->quality_min),
                                'quality_max' => $groupedEntries->max(static fn (array $pair) => $pair['resourceLocation']->quality_max),
                                'entry_count' => $groupedEntries->count(),
                            ];
                        })
                        ->sortBy('group_name')
                        ->values()
                        ->all(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    protected function flattenLocationPairs(Collection $resourceDataCollection): Collection
    {
        return $resourceDataCollection
            ->flatMap(static fn ($resourceData) => $resourceData->locations)
            ->flatMap(static fn (ResourceLocation $resourceLocation) => $resourceLocation->starmapLocationData
                ->map(static fn (StarmapLocationData $locationData) => [
                    'resourceLocation' => $resourceLocation,
                    'locationData' => $locationData,
                ]));
    }

    protected function buildSystems(array $locations): array
    {
        return collect($locations)
            ->pluck('system')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function formatDecimal(mixed $value, int $decimals): ?float
    {
        if ($value === null) {
            return null;
        }

        return round((float) $value, $decimals);
    }

    protected function extractGroupNames(Collection $resourceDataCollection): Collection
    {
        return $resourceDataCollection
            ->flatMap(static fn ($resourceData) => $resourceData->locations->pluck('group_name'))
            ->unique()
            ->values();
    }

    protected function resolveFlags(Collection $groupNames): array
    {
        $hasShip = $groupNames->contains('SpaceShip_Mineables');
        $hasGround = $groupNames->contains('GroundVehicle_Mineables');
        $hasFps = $groupNames->intersect(['FPS_Mineables', 'FPS mineables'])->isNotEmpty();
        $hasHarvestable = $groupNames->intersect(['Harvestables', 'Havestables', 'Plants'])->isNotEmpty();
        $hasSalvage = $groupNames->contains(static fn (string $name) => str_starts_with($name, 'Salvage'));

        return compact('hasShip', 'hasGround', 'hasFps', 'hasHarvestable', 'hasSalvage');
    }

    protected function buildMethodsFromFlags(bool $hasShip, bool $hasGround, bool $hasFps, bool $hasHarvestable, bool $hasSalvage): array
    {
        return array_values(array_filter([
            $hasShip ? 'Ship' : null,
            $hasGround ? 'Ground Vehicle' : null,
            $hasFps ? 'FPS' : null,
            $hasHarvestable ? 'Harvestable' : null,
            $hasSalvage ? 'Salvage' : null,
        ]));
    }
}
