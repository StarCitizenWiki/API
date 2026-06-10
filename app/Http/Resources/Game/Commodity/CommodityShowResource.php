<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Commodity;

use App\Enums\Game\ResourceKind;
use App\Http\Resources\Game\Concerns\ExpandsUexPrices;
use App\Models\Game\Commodity\Commodity;
use App\Support\Formatting\FormatDuration;
use App\Support\Resources\HasDepositFormatting;
use Illuminate\Http\Request;
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
                new OA\Property(property: 'resource_uuid', description: 'UUID of the resource (commodity) this deposit yields.', type: 'string', format: 'uuid', nullable: true),
                new OA\Property(property: 'group_name', description: 'Internal group name for the mining category (e.g. "SpaceShip_Mineables").', type: 'string'),
                new OA\Property(property: 'resource_kind', description: 'Resource extraction kind (e.g. "Mineable", "Harvestable").', type: 'string', nullable: true),
            ],
            type: 'object'
        ),
    ]
)]
#[OA\Schema(
    schema: 'commodity_show_location',
    title: 'Commodity Show Location',
    description: 'Detailed location data for a commodity deposit, including quality range and probability information.',
    properties: [
        new OA\Property(property: 'name', description: 'Canonical location name.', type: 'string'),
        new OA\Property(property: 'designation', description: 'Location designation code (e.g. "CRU-L1").', type: 'string', nullable: true),
        new OA\Property(property: 'display_name', description: 'Formatted display name combining designation and name.', type: 'string'),
        new OA\Property(property: 'system', description: 'Star system this location belongs to.', type: 'string', nullable: true),
        new OA\Property(property: 'type', description: 'Location type classification (e.g. "Moon", "Planet", "Outpost").', type: 'string', nullable: true),
        new OA\Property(property: 'parent_name', description: 'Name of the parent celestial body or location.', type: 'string', nullable: true),
        new OA\Property(property: 'parent_type', description: 'Type of the parent location.', type: 'string', nullable: true),
        new OA\Property(property: 'parent_uuid', description: 'UUID of the parent location entity.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'uuid', description: 'UUID of this starmap location entity.', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', description: 'API link to the full location details.', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'group_probability', description: 'Raw probability of this commodity group occurring at this location (0-1).', type: 'number'),
        new OA\Property(property: 'group_probability_percent', description: 'Group probability expressed as a percentage (0-100).', type: 'number'),
        new OA\Property(property: 'relative_probability', description: 'Raw relative probability compared to other commodities at this location (0-1).', type: 'number'),
        new OA\Property(property: 'relative_probability_percent', description: 'Relative probability expressed as a percentage (0-100).', type: 'number'),
        new OA\Property(property: 'quality_min', description: 'Minimum quality across all deposit instances at this location.', type: 'integer', nullable: true),
        new OA\Property(property: 'quality_max', description: 'Maximum quality across all deposit instances at this location.', type: 'integer', nullable: true),
        new OA\Property(
            property: 'areas',
            description: 'Areas with global modifiers that boost spawn rates at this location.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/area_boost'),
            nullable: true
        ),
        new OA\Property(
            property: 'resources',
            description: 'Individual deposit groups for this commodity at this location.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_deposit_group')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_version_entry',
    title: 'Commodity Version Entry',
    description: 'A raw or refined version of the commodity with navigation links.',
    properties: [
        new OA\Property(property: 'name', description: 'Display name of the versioned commodity.', type: 'string'),
        new OA\Property(property: 'uuid', description: 'UUID of the versioned commodity.', type: 'string', format: 'uuid'),
        new OA\Property(property: 'web_url', description: 'Frontend URL for the versioned commodity page.', type: 'string', format: 'uri'),
        new OA\Property(property: 'link', description: 'API link to the versioned commodity details.', type: 'string', format: 'uri'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'commodity_system_group',
    title: 'Commodity System Group',
    description: 'Locations grouped by star system for organized display.',
    properties: [
        new OA\Property(property: 'name', description: 'Star system name.', type: 'string'),
        new OA\Property(
            property: 'locations',
            description: 'All commodity deposit locations within this system.',
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
            description: 'Flat list of all locations with deposit details.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_show_location')
        ),
        new OA\Property(
            property: 'systems_grouped',
            description: 'Locations organized by star system for hierarchical display.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_system_group')
        ),
        new OA\Property(
            property: 'raw_versions',
            description: 'Raw (unrefined) versions of this commodity, if this is a refined commodity.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/commodity_version_entry'),
            nullable: true
        ),
        new OA\Property(
            property: 'blueprints',
            description: 'Crafting blueprints that use or produce this commodity.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'key', description: 'Unique blueprint key identifier.', type: 'string'),
                    new OA\Property(property: 'output_name', description: 'Name of the item produced by this blueprint.', type: 'string'),
                    new OA\Property(property: 'output_item_uuid', description: 'UUID of the output item entity.', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'craft_time_label', description: 'Human-readable crafting duration (e.g. "5m 30s").', type: 'string'),
                    new OA\Property(property: 'web_url', description: 'Frontend URL for the blueprint page.', type: 'string', format: 'uri'),
                    new OA\Property(property: 'link', description: 'API link to the blueprint details.', type: 'string', format: 'uri'),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(
            property: 'items',
            description: 'Physical items associated with this commodity in the game.',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', description: 'Item display name.', type: 'string'),
                    new OA\Property(property: 'uuid', description: 'UUID of the item entity.', type: 'string', format: 'uuid', nullable: true),
                    new OA\Property(property: 'type', description: 'Item type classification.', type: 'string', nullable: true),
                    new OA\Property(property: 'type_label', description: 'Human-readable label for the item type.', type: 'string', nullable: true),
                    new OA\Property(property: 'sub_type', description: 'Item sub-type classification.', type: 'string', nullable: true),
                    new OA\Property(property: 'sub_type_label', description: 'Human-readable label for the item sub-type.', type: 'string', nullable: true),
                    new OA\Property(property: 'size', description: 'Item size grade.', type: 'integer', nullable: true),
                    new OA\Property(property: 'web_url', description: 'Frontend URL for the item page.', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'link', description: 'API link to the item details.', type: 'string', format: 'uri', nullable: true),
                ],
                type: 'object'
            ),
            nullable: true
        ),
        new OA\Property(property: 'link', description: 'API link to this commodity\'s full details.', type: 'string', format: 'uri'),
        new OA\Property(property: 'web_url', description: 'Frontend URL for this commodity\'s page.', type: 'string', format: 'uri'),
        new OA\Property(
            property: 'uex_prices',
            description: 'Commodity prices from UEX Corp API.',
            properties: [
                new OA\Property(
                    property: 'purchase',
                    description: 'Purchase prices from UEX Corp.',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/uex_price'),
                ),
            ],
            type: 'object',
            nullable: true
        ),
    ],
    type: 'object'
)]
class CommodityShowResource extends CommodityIndexResource
{
    use ExpandsUexPrices;
    use HasDepositFormatting;

    public function toArray(Request $request): array
    {
        $resourceDataCollection = $this->resource->relationLoaded('resourceData') ? $this->resource->resourceData->all() : [];
        $locations = $this->buildDetailedLocations($resourceDataCollection, $this->resource->id);

        $this->setCanonicalResource(
            'commodity',
            $this->resource->uuid,
            $this->resource->slug,
            $this->urlWithVersion(route('commodities.show', ['commodity' => $this->resource->uuid]), $request),
            $this->urlWithVersion(route('web.commodities.show', ['identifier' => $this->resource->slug ?? $this->resource->uuid]), $request),
            $request->query('version'),
        );

        return array_merge(parent::toArray($request), [
            'locations' => $locations,
            'systems' => $this->buildSystems($locations),
            'systems_grouped' => $this->buildSystemsGrouped($locations),

            'raw_versions' => $this->whenLoaded('rawVersions', fn (): array => $this->resource->rawVersions
                ->map(fn (Commodity $raw): array => [
                    'name' => $raw->name,
                    'uuid' => $raw->uuid,
                    'web_url' => $this->urlWithVersion(
                        route('web.commodities.show', ['identifier' => $raw->slug ?? $raw->uuid]),
                        $request,
                    ),
                    'link' => $this->urlWithVersion(
                        route('commodities.show', ['commodity' => $raw->uuid]),
                        $request,
                    ),
                ])->values()->all(), []),

            'blueprints' => $this->whenLoaded('blueprints', fn () => $this->resource->blueprints
                ->map(fn ($blueprintData): array => [
                    'key' => $blueprintData->key,
                    'output_name' => $blueprintData->output_name,
                    'output_item_uuid' => $blueprintData->output_item_uuid,
                    'craft_time_label' => FormatDuration::fromSeconds($blueprintData->craft_time_seconds),
                    'web_url' => $this->urlWithVersion(
                        route('web.blueprints.show', ['blueprint' => $blueprintData->blueprint->slug ?? $blueprintData->blueprint->uuid]),
                        $request,
                    ),
                    'link' => $this->urlWithVersion(
                        route('blueprints.show', ['blueprint' => $blueprintData->blueprint->uuid]),
                        $request,
                    ),
                ])->values()->all(), []),

            'items' => $this->whenLoaded('items', fn () => $this->resource->items
                ->map(fn ($itemData): array => [
                    'name' => $itemData->name,
                    'uuid' => $itemData->item?->uuid,
                    'type' => $itemData->type,
                    'type_label' => $itemData->type_label,
                    'sub_type' => $itemData->sub_type,
                    'sub_type_label' => $itemData->sub_type_label,
                    'size' => $itemData->size,
                    'web_url' => $itemData->item?->uuid
                        ? $this->urlWithVersion(
                            route('web.items.show', ['item' => $itemData->item->slug ?? $itemData->item->uuid]),
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

            'uex_prices' => [
                'purchase' => $this->expandPrices((array) ($this->resource->uex_prices ?? [])),
            ],
        ]);
    }

    private function buildDetailedLocations(array $resourceDataCollection, int $currentCommodityId): array
    {
        $flatPairs = $this->flattenLocationPairs($resourceDataCollection);

        // Group by location key
        $groups = [];

        foreach ($flatPairs as $pair) {
            if (Str::isUuid($pair['locationData']->name)) {
                continue;
            }

            $key = $pair['locationData']->name.'@'.($pair['locationData']->system ?? '');
            $groups[$key][] = $pair;
        }

        $locations = [];

        foreach ($groups as $pairs) {
            $firstLocationData = $pairs[0]['locationData'];
            $firstResourceLocation = $pairs[0]['resourceLocation'];

            // Group deposits
            $depositGroups = [];

            foreach ($pairs as $pair) {
                $rl = $pair['resourceLocation'];
                $depositKey = $rl->resource_kind === ResourceKind::Mineable
                    ? $rl->resourceData->key.'@'.($rl->resource_provider_id ?? 'none')
                    : (string) $rl->resourceData->id;
                $depositGroups[$depositKey][] = $pair;
            }

            $depositEntries = [];

            foreach ($depositGroups as $depositPairs) {
                $representative = $depositPairs[0]['resourceLocation'];
                $resourceData = $representative->resourceData;

                $deposit = self::buildDepositBase($depositPairs, $resourceData, $currentCommodityId);
                $deposit['group_name'] = $representative->group_name;
                $deposit['resource_kind'] = $representative->resource_kind instanceof ResourceKind ? $representative->resource_kind->value : $representative->resource_kind;
                $depositEntries[] = $deposit;
            }

            usort($depositEntries, static fn (array $a, array $b): int => $a['key'] <=> $b['key']);

            $allAreas = self::formatAllAreas($firstResourceLocation->provider?->areas);

            $designation = $firstLocationData->designation;
            $displayName = $designation !== null ? "{$designation}: {$firstLocationData->name}" : $firstLocationData->name;

            $qMin = PHP_INT_MAX;
            $qMax = PHP_INT_MIN;

            foreach ($pairs as $p) {
                $v = $p['resourceLocation']->quality_min;

                if ($v !== null && $v < $qMin) {
                    $qMin = $v;
                }

                $v = $p['resourceLocation']->quality_max;

                if ($v !== null && $v > $qMax) {
                    $qMax = $v;
                }
            }

            $locations[$firstLocationData->name] = [
                'name' => $firstLocationData->name,
                'designation' => $designation,
                'display_name' => $displayName,
                'system' => $firstLocationData->system,
                'type' => $firstLocationData->type_name,
                'parent_name' => $firstLocationData->parent_name,
                'parent_type' => $firstLocationData->parent_type_name,
                'parent_uuid' => $firstLocationData->parent_location_uuid,
                'uuid' => $firstLocationData->location_uuid,
                'link' => $firstLocationData->location_uuid
                    ? route('locations.show', ['identifier' => $firstLocationData->location_uuid])
                    : null,
                'group_probability' => (float) $firstResourceLocation->group_probability,
                'group_probability_percent' => self::formatPercent((float) $firstResourceLocation->group_probability, 1),
                'relative_probability' => (float) $firstResourceLocation->relative_probability,
                'relative_probability_percent' => self::formatPercent((float) $firstResourceLocation->relative_probability, 1),
                'quality_min' => $qMin === PHP_INT_MAX ? null : $qMin,
                'quality_max' => $qMax === PHP_INT_MIN ? null : $qMax,
                'areas' => $allAreas,
                'resources' => array_values($depositEntries),
            ];
        }

        uasort($locations, static fn (array $a, array $b): int => $a['name'] <=> $b['name']);

        return array_values($locations);
    }

    public function buildSystemsGrouped(array $locations): array
    {
        $groups = [];

        foreach ($locations as $location) {
            $system = $location['system'] ?? 'Unknown System';
            $groups[$system][] = $location;
        }

        ksort($groups);

        $result = [];

        foreach ($groups as $systemName => $systemLocations) {
            usort($systemLocations, static fn (array $a, array $b): int => ($a['designation'] ?? "\xFF") <=> ($b['designation'] ?? "\xFF")
                ?: ($a['name'] ?? '') <=> ($b['name'] ?? '')
            );

            $result[] = [
                'name' => $systemName,
                'locations' => $systemLocations,
            ];
        }

        return $result;
    }
}
