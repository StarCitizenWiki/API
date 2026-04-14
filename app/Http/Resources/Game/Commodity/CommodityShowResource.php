<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Commodity;

use App\Enums\Game\ResourceKind;
use App\Models\Game\Commodity\Commodity;
use App\Support\Formatting\FormatDuration;
use App\Support\Resources\HasDepositFormatting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'commodity_deposit_group',
    title: 'Commodity Deposit Group',
    description: 'Deposit base with commodity grouping fields.',
    allOf: [
        new OA\Schema(ref: '#/components/schemas/deposit_base'),
        new OA\Schema(
            properties: [
                new OA\Property(property: 'resource_uuid', type: 'string', format: 'uuid', nullable: true),
                new OA\Property(property: 'group_name', type: 'string'),
                new OA\Property(property: 'resource_kind', type: 'string', nullable: true),
            ],
            type: 'object'
        ),
    ]
)]
#[OA\Schema(
    schema: 'commodity_show_location',
    title: 'Commodity Show Location',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'designation', type: 'string', nullable: true),
        new OA\Property(property: 'display_name', type: 'string'),
        new OA\Property(property: 'system', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'parent_name', type: 'string', nullable: true),
        new OA\Property(property: 'parent_type', type: 'string', nullable: true),
        new OA\Property(property: 'parent_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'group_probability', type: 'number'),
        new OA\Property(property: 'group_probability_percent', type: 'number'),
        new OA\Property(property: 'relative_probability', type: 'number'),
        new OA\Property(property: 'relative_probability_percent', type: 'number'),
        new OA\Property(property: 'quality_min', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', type: 'integer', nullable: true),
        new OA\Property(
            property: 'areas',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/area_boost'),
            nullable: true
        ),
        new OA\Property(
            property: 'resources',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_deposit_group')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_version_entry',
    title: 'Commodity Version Entry',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_system_group',
    title: 'Commodity System Group',
    properties: [
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(
            property: 'locations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_show_location')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_show',
    title: 'Commodity Show',
    description: 'Full game commodity detail used in show responses.',
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
            items: new OA\Items(ref: '#/components/schemas/commodity_show_location')
        ),
        new OA\Property(
            property: 'systems_grouped',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_system_group')
        ),
        new OA\Property(
            property: 'raw_versions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_version_entry'),
            nullable: true
        ),
        new OA\Property(
            property: 'blueprints',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'key', type: 'string'),
                    new OA\Property(property: 'output_name', type: 'string'),
                    new OA\Property(property: 'output_item_uuid', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'craft_time_label', type: 'string'),
                    new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
                    new OA\Property(property: 'link', type: 'string', format: 'uri'),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'type', type: 'string', nullable: true),
                    new OA\Property(property: 'sub_type', type: 'string', nullable: true),
                    new OA\Property(property: 'size', type: 'integer', nullable: true),
                    new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'link', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
class CommodityShowResource extends CommodityIndexResource
{
    use HasDepositFormatting;

    public static function validIncludes(): array
    {
        return ['blueprints', 'items'];
    }

    public function toArray(Request $request): array
    {
        $resourceDataCollection = $this->resourceData ?? collect();
        $locations = $this->buildDetailedLocations($resourceDataCollection, $this->id);
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
            'kind' => ($first = $resourceDataCollection->first()) ? ($first->locations->first()?->resource_kind instanceof ResourceKind ? $first->locations->first()->resource_kind->value : ($first->kind instanceof ResourceKind ? $first->kind->value : $first->kind)) : null,
            'methods' => $this->buildMethodsFromFlags($hasShip, $hasGround, $hasFps, $hasHarvestable, $hasSalvage),
            'systems' => $this->buildSystems($locations),
            'locations' => $locations,
            'systems_grouped' => $this->buildSystemsGrouped($locations),

            'raw_versions' => $this->whenLoaded('rawVersions', fn (): array => $this->rawVersions
                ->map(fn (Commodity $raw): array => [
                    'name' => $raw->name,
                    'uuid' => $raw->uuid,
                    'web_url' => $this->urlWithVersion(
                        route('web.commodities.show', ['identifier' => $raw->uuid]),
                        $request,
                    ),
                    'link' => $this->urlWithVersion(
                        route('commodities.show', ['commodity' => $raw->uuid]),
                        $request,
                    ),
                ])->values()->all(), []),

            'blueprints' => $this->whenLoaded('blueprints', fn () => $this->blueprints
                ->map(fn ($blueprintData): array => [
                    'key' => $blueprintData->key,
                    'output_name' => $blueprintData->output_name,
                    'output_item_uuid' => $blueprintData->output_item_uuid,
                    'craft_time_label' => FormatDuration::fromSeconds($blueprintData->craft_time_seconds),
                    'web_url' => $this->urlWithVersion(
                        route('web.blueprints.show', ['blueprint' => $blueprintData->blueprint->uuid]),
                        $request,
                    ),
                    'link' => $this->urlWithVersion(
                        route('blueprints.show', ['blueprint' => $blueprintData->blueprint->uuid]),
                        $request,
                    ),
                ])->values()->all(), []),

            'items' => $this->whenLoaded('items', fn () => $this->items
                ->map(fn ($itemData): array => [
                    'name' => $itemData->name,
                    'uuid' => $itemData->item?->uuid,
                    'type' => $itemData->type,
                    'sub_type' => $itemData->sub_type,
                    'size' => $itemData->size,
                    'web_url' => $itemData->item?->uuid
                        ? $this->urlWithVersion(
                            route('web.items.show', ['item' => $itemData->item->uuid]),
                            $request,
                        )
                        : null,
                    'link' => $itemData->item?->uuid
                        ? $this->urlWithVersion(
                            route('items.show', ['identifier' => $itemData->item->uuid]),
                            $request,
                        )
                        : null,
                ])->values()->all(), []),

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

    private function buildDetailedLocations(Collection $resourceDataCollection, int $currentCommodityId): array
    {
        $flatPairs = $this->flattenLocationPairs($resourceDataCollection);

        $grouped = $flatPairs
            ->filter(static fn (array $pair): bool => ! Str::isUuid($pair['locationData']->name))
            ->groupBy(static fn (array $pair): string => $pair['locationData']->name.'@'.($pair['locationData']->system ?? ''));

        return $grouped
            ->map(function (Collection $pairs) use ($currentCommodityId): array {
                $firstLocationData = $pairs->first()['locationData'];
                $firstResourceLocation = $pairs->first()['resourceLocation'];

                $depositGroups = $pairs
                    ->groupBy(static fn (array $pair): string => $pair['resourceLocation']->resource_kind === ResourceKind::Mineable
                        ? $pair['resourceLocation']->resourceData->key.'@'.($pair['resourceLocation']->resource_provider_id ?? 'none')
                        : (string) $pair['resourceLocation']->resourceData->id)
                    ->map(function (Collection $depositPairs) use ($currentCommodityId): array {
                        $representative = $depositPairs->first()['resourceLocation'];
                        $resourceData = $representative->resourceData;

                        $deposit = self::buildDepositBase($depositPairs, $resourceData, $currentCommodityId);
                        $deposit['group_name'] = $representative->group_name;
                        $deposit['resource_kind'] = $representative->resource_kind instanceof ResourceKind ? $representative->resource_kind->value : $representative->resource_kind;

                        return $deposit;
                    })
                    ->sortBy('key')
                    ->values()
                    ->all();

                $allAreas = self::formatAllAreas($firstResourceLocation->provider?->areas);

                $designation = $firstLocationData->designation;
                $displayName = $designation !== null ? "{$designation}: {$firstLocationData->name}" : $firstLocationData->name;

                return [
                    'name' => $firstLocationData->name,
                    'designation' => $designation,
                    'display_name' => $displayName,
                    'system' => $firstLocationData->system,
                    'type' => $firstLocationData->type_name,
                    'parent_name' => $firstLocationData->parent?->name,
                    'parent_type' => $firstLocationData->parent?->type_name,
                    'parent_uuid' => $firstLocationData->parent?->location?->uuid,
                    'uuid' => $firstLocationData->location?->uuid,
                    'link' => $firstLocationData->location?->uuid
                        ? route('locations.show', ['identifier' => $firstLocationData->location->uuid])
                        : null,
                    'group_probability' => (float) $firstResourceLocation->group_probability,
                    'group_probability_percent' => self::formatPercent((float) $firstResourceLocation->group_probability, 1),
                    'relative_probability' => (float) $firstResourceLocation->relative_probability,
                    'relative_probability_percent' => self::formatPercent((float) $firstResourceLocation->relative_probability, 1),
                    'quality_min' => $pairs->min(static fn (array $pair) => $pair['resourceLocation']->quality_min),
                    'quality_max' => $pairs->max(static fn (array $pair) => $pair['resourceLocation']->quality_max),
                    'areas' => $allAreas,
                    'resources' => $depositGroups,
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    private function buildSystemsGrouped(array $locations): array
    {
        return collect($locations)
            ->groupBy(static fn (array $location): string => $location['system'] ?? 'Unknown System')
            ->sortKeys()
            ->map(static fn (Collection $systemLocations): array => [
                'name' => $systemLocations->first()['system'] ?? 'Unknown System',
                'locations' => $systemLocations
                    ->sortBy(static fn (array $loc): array => [$loc['designation'] ?? "\xFF", $loc['name'] ?? ''])
                    ->values()
                    ->all(),
            ])
            ->values()
            ->all();
    }
}
