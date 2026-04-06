<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Commodity;

use App\Enums\Game\ResourceKind;
use App\Http\Resources\AbstractBaseResource;
use App\Models\Game\Resource\ResourceLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'commodity_index_location_entry',
    title: 'Commodity Index Location Entry',
    properties: [
        new OA\Property(property: 'group_name', type: 'string'),
        new OA\Property(property: 'resource_kind', type: 'string', nullable: true),
        new OA\Property(property: 'quality_min', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', type: 'integer', nullable: true),
        new OA\Property(property: 'entry_count', type: 'integer'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_index_location',
    title: 'Commodity Index Location',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'display_name', type: 'string'),
        new OA\Property(property: 'system', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'parent_name', type: 'string', nullable: true),
        new OA\Property(property: 'parent_type', type: 'string', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(
            property: 'entries',
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
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'key', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'slug', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'tier', type: 'string', nullable: true),
        new OA\Property(
            property: 'refined_version',
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
                new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
                new OA\Property(property: 'link', type: 'string', format: 'uri'),
            ],
            type: 'object',
            nullable: true
        ),
        new OA\Property(property: 'density_g_per_cc', type: 'number', nullable: true),
        new OA\Property(property: 'instability', type: 'number', nullable: true),
        new OA\Property(property: 'resistance', type: 'number', nullable: true),
        new OA\Property(
            property: 'box_sizes_scu',
            type: 'array',
            items: new OA\Items(type: 'number')
        ),
        new OA\Property(property: 'validate_default_cargo_box', type: 'boolean'),
        new OA\Property(property: 'has_default_cargo_containers', type: 'boolean'),
        new OA\Property(property: 'is_mineable', type: 'boolean'),
        new OA\Property(property: 'has_ship_mineables', type: 'boolean'),
        new OA\Property(property: 'has_ground_vehicle_mineables', type: 'boolean'),
        new OA\Property(property: 'has_fps_mineables', type: 'boolean'),
        new OA\Property(property: 'has_harvestables', type: 'boolean'),
        new OA\Property(property: 'has_salvage', type: 'boolean'),
        new OA\Property(property: 'signature', type: 'integer', nullable: true),
        new OA\Property(property: 'kind', type: 'string', nullable: true),
        new OA\Property(
            property: 'methods',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(
            property: 'systems',
            type: 'array',
            items: new OA\Items(type: 'string')
        ),
        new OA\Property(
            property: 'locations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_index_location')
        ),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
class CommodityIndexResource extends AbstractBaseResource
{
    public function toArray(Request $request): array
    {
        $resourceDataCollection = $this->resourceData ?? collect();
        $locations = $this->buildLocations($resourceDataCollection);
        $groupNames = $this->extractGroupNames($resourceDataCollection);
        ['hasShip' => $hasShip, 'hasGround' => $hasGround, 'hasFps' => $hasFps, 'hasHarvestable' => $hasHarvestable, 'hasSalvage' => $hasSalvage] = $this->resolveFlags($groupNames);

        return [
            'uuid' => $this->uuid,
            'key' => $this->key,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'tier' => $this->tier,
            'refined_version' => $this->when($this->refinedVersion, fn () => [
                'name' => $this->refined_version_name,
                'uuid' => $this->refinedVersion->uuid,
                'web_url' => $this->urlWithVersion(
                    route('web.commodities.show', ['identifier' => $this->refinedVersion->uuid]),
                    $request,
                ),
                'link' => $this->urlWithVersion(
                    route('commodities.show', ['commodity' => $this->refinedVersion->uuid]),
                    $request,
                ),
            ]),
            'density_g_per_cc' => $this->formatDecimal($this->density_g_per_cc, 2),
            'instability' => $this->formatDecimal($this->instability, 0),
            'resistance' => $this->formatDecimal($this->resistance, 2),
            'box_sizes_scu' => $this->box_sizes_scu ?? [],
            'validate_default_cargo_box' => $this->validate_default_cargo_box,
            'has_default_cargo_containers' => $this->has_default_cargo_containers,

            'is_mineable' => $resourceDataCollection->isNotEmpty(),
            'has_ship_mineables' => $hasShip,
            'has_ground_vehicle_mineables' => $hasGround,
            'has_fps_mineables' => $hasFps,
            'has_harvestables' => $hasHarvestable,
            'has_salvage' => $hasSalvage,

            'signature' => ($sig = $resourceDataCollection->first()?->signature) !== null && $sig > 0 ? $sig : null,
            'kind' => ($first = $resourceDataCollection->first()) ? ($first->locations->first()?->resource_kind?->value ?? ($first->kind instanceof ResourceKind ? $first->kind->value : $first->kind)) : null,
            'methods' => $this->buildMethodsFromFlags($hasShip, $hasGround, $hasFps, $hasHarvestable, $hasSalvage),
            'systems' => $this->buildSystems($locations),
            'locations' => $locations,

            'link' => $this->urlWithVersion(
                route('commodities.show', ['commodity' => $this->uuid]),
                $request,
            ),
            'web_url' => $this->urlWithVersion(
                route('web.commodities.show', ['identifier' => $this->uuid]),
                $request,
            ),
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
