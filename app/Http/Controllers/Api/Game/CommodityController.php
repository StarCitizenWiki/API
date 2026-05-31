<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Http\Includes\IncludeDefinition;
use App\Http\Requests\Api\Game\CommodityIndexRequest;
use App\Http\Resources\Game\Commodity\CommodityIndexResource;
use App\Http\Resources\Game\Commodity\CommodityShowResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\Resource\ResourceData;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('commodities')]
class CommodityController extends Controller
{
    use ResolvesGameVersion;

    /**
     * @return array<int, IncludeDefinition>
     */
    private function includeDefinitions(): array
    {
        return [
            IncludeDefinition::alwaysLoaded('blueprints'),
            IncludeDefinition::alwaysLoaded('items'),
        ];
    }

    private const string GROUP_SHIP = 'SpaceShip_Mineables';

    private const string GROUP_GROUND_VEHICLE = 'GroundVehicle_Mineables';

    private const array GROUPS_FPS = ['FPS_Mineables', 'FPS mineables'];

    private const array GROUPS_HARVESTABLE = ['Harvestables', 'Havestables', 'Plants'];

    #[OA\Get(
        path: '/api/commodities',
        operationId: 'listCommodities',
        description: 'Returns paginated game commodities with location data and resource metadata, optionally filtered to only those consumed by blueprints. Results include refined version info and starmap location data scoped to the requested or default game version.',
        summary: 'List Game Commodities',
        tags: ['Commodities'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: key, name, rarity, density, instability, resistance, signature.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '-rarity')
            ),
            new OA\Parameter(
                name: 'filter[used]',
                description: 'When true, only commodities used by blueprint ingredients in the requested or default game version are returned. Example: `true`',
                in: 'query',
                schema: new OA\Schema(type: 'boolean')
            ),
            new OA\Parameter(name: 'filter[system]', description: 'Star system where the commodity can be found (see GET /api/commodities/filters for valid values). Example: `Stanton System`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Location type name (see GET /api/commodities/filters for valid values). Example: `Planet`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[rarity]', description: 'Commodity tier/rarity level, lowercase (see GET /api/commodities/filters for valid values). Example: `epic`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[kind]', description: 'Resource kind (see GET /api/commodities/filters for valid values). Example: `mineable`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[group]', description: 'Commodity group name. Comma-separated for multiple. Example: `ProcessedGoods,Food`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[refined_version]', description: 'Refined version name (see GET /api/commodities/filters for valid values). Example: `Agricium`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'filter[location]',
                description: 'Partial match on starmap location name. Example: `ArcCorp`',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(name: 'filter[query]', description: 'Search commodities by name or key. Example: `Agricium`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[ship]', description: 'When true, only show commodities mineable by ships. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[ground_vehicle]', description: 'When true, only show commodities mineable by ground vehicles. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[fps]', description: 'When true, only show commodities mineable on foot (FPS). Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[harvestable]', description: 'When true, only show commodities collectible from harvestable or plant deposits. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[salvage]', description: 'When true, only show commodities obtainable through salvage. Example: `false`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mineable]', description: 'When true, only show mineable commodities; when false, only show non-mineable. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of commodities',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'commodities_by_location',
                            summary: 'Commodities filtered by location',
                            value: [
                                'data' => [
                                    ['uuid' => '00000000-0000-0000-0000-000000000000', 'name' => 'Agricium', 'slug' => 'agricium'],
                                ],
                                'links' => ['first' => 'https://api.star-citizen.wiki/api/commodities?page[number]=1', 'last' => null, 'prev' => null, 'next' => null],
                                'meta' => ['current_page' => 1, 'per_page' => 30, 'total' => 1],
                            ],
                        ),
                    ],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/commodity_link')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object',
                )
            ),
        ]
    )]
    public function index(CommodityIndexRequest $request): AnonymousResourceCollection
    {
        $versionCode = $this->gameVersionCode();

        $commodities = $this->buildIndexQuery($request)
            ->with([
                'refinedVersion',
                'resourceData' => fn (BelongsToMany $relation) => $relation
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->with(['locations.starmapLocationData.location', 'locations.starmapLocationData.parent', 'locations.starmapLocationData.locationHierarchyEntityTag']),
            ])
            ->orderBy('key')
            ->jsonPaginate()
            ->appends($request->query());

        return CommodityIndexResource::collection($commodities);
    }

    #[OA\Get(
        path: '/api/commodities/{commodity}',
        operationId: 'getCommodity',
        description: 'Returns full details for a single game commodity including detailed location entries with starmap data, resource composition, areas, clustering data, and raw/refined version info. Results are scoped to the requested or default game version. Optionally include related blueprints and items.',
        summary: 'Show Game Commodity',
        tags: ['Commodities'],
        parameters: [
            new OA\Parameter(
                name: 'commodity',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Commodity UUID or slug',
                    type: 'string',
                    example: 'agricium',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'include',
                description: 'Comma-separated relationships to include. Available: blueprints (crafting blueprints that use this commodity), items (items that produce or require this commodity).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'blueprints,items')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Commodity details',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'commodity_detail',
                            summary: 'Commodity detail response',
                            value: ['data' => ['uuid' => '00000000-0000-0000-0000-000000000000', 'name' => 'Agricium', 'slug' => 'agricium']],
                        ),
                    ],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/commodity_show'),
                    ],
                    type: 'object',
                )
            ),
            new OA\Response(response: 404, description: 'Commodity not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    public function show(Request $request, string $commodity): JsonResource
    {
        $versionCode = $this->gameVersionCode();

        $commodityModel = QueryBuilder::for(Commodity::class, $request)
            ->when(Str::isUuid($commodity), fn (Builder $q) => $q->where('uuid', $commodity))
            ->unless(Str::isUuid($commodity), fn (Builder $q) => $q->where('slug', $commodity))
            ->allowedIncludes(
                IncludeDefinition::callback('blueprints', fn (BelongsToMany $query) => $query
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->with(['blueprint', 'gameVersion', 'ingredients']))->toSpatieInclude(),
                IncludeDefinition::callback('items', fn (BelongsToMany $query) => $query
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->with(['item', 'gameVersion']))->toSpatieInclude(),
            )
            ->with([
                'refinedVersion',
                'rawVersions',
                'resourceData' => fn (BelongsToMany $relation) => $relation
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->with([
                        'resource',
                        'locations.starmapLocationData.location',
                        'locations.starmapLocationData.parent.location',
                        'locations.starmapLocationData.locationHierarchyEntityTag',
                        'locations.resourceData',
                        'locations.commodity',
                        'locations.provider',
                        'commodities',
                    ]),
            ])
            ->first();

        if ($commodityModel === null) {
            throw new NotFoundHttpException('No commodity found with the given identifier.');
        }

        return (new CommodityShowResource($commodityModel))
            ->setValidIncludes(IncludeDefinition::toNames($this->includeDefinitions()));
    }

    #[OA\Get(
        path: '/api/commodities/filters',
        operationId: 'listCommodityFilters',
        description: 'Returns all available filter values for game commodities, scoped to the requested or default game version. Filter values can be combined; providing a system filter will narrow the location facet to that system only.',
        summary: 'Game Commodity Filters',
        tags: ['Commodities'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[used]', description: 'When true, filter facets to only commodities used by blueprint ingredients. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[system]', description: 'Star system where the commodity can be found. Example: `Stanton System`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Location type name. Example: `Planet`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[rarity]', description: 'Commodity tier/rarity level, lowercase. Example: `epic`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[kind]', description: 'Resource kind. Example: `mineable`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[group]', description: 'Commodity group name. Comma-separated for multiple. Example: `ProcessedGoods,Food`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[refined_version]', description: 'Refined version name. Example: `Agricium`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[location]', description: 'Partial match on starmap location name. Example: `ArcCorp`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search commodities by name or key. Example: `Agricium`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[ship]', description: 'When true, only show commodities mineable by ships. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[ground_vehicle]', description: 'When true, only show commodities mineable by ground vehicles. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[fps]', description: 'When true, only show commodities mineable on foot (FPS). Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[harvestable]', description: 'When true, only show commodities collectible from harvestable or plant deposits. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[salvage]', description: 'When true, only show commodities obtainable through salvage. Example: `false`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mineable]', description: 'When true, only show mineable commodities; when false, only show non-mineable. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for game commodities.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'system', description: 'Star systems where commodities can be found', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'type', description: 'Location type names', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'rarity', description: 'Commodity tier/rarity levels', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'kind', description: 'Resource kinds', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'refined_version', description: 'Refined version names', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'group', description: 'Commodity groups', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'location', description: 'Starmap locations grouped by system', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function filters(Request $request): JsonResponse
    {
        $versionCode = $this->gameVersionCode() ?? $this->gameVersion()->code;
        $resolver = function () use ($request, $versionCode): array {
            $out = [];

            $simpleFacets = [
                'rarity' => 'game_commodities.tier',
                'refined_version' => 'game_commodities.refined_version_name',
            ];

            foreach ($simpleFacets as $key => $expr) {
                $rows = QueryBuilder::for(Commodity::class, $request)
                    ->allowedFilters(...$this->allowedFilters())
                    ->select([
                        DB::raw("{$expr} as value"),
                        DB::raw('count(*) as count'),
                    ])
                    ->groupByRaw($expr)
                    ->orderByRaw("{$expr} IS NULL, {$expr}")
                    ->get();

                $labelResolver = $key === 'rarity'
                    ? static fn (mixed $value, ?string $label): ?string => is_string($value) ? Str::title($value) : null
                    : null;

                $out[$key] = FilterValues::fromRows($rows, labelResolver: $labelResolver);
            }

            $locationFacets = [
                'system' => 'sld.system',
                'type' => 'sld.type_name',
                'kind' => 'grd.kind',
            ];

            $baseQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters());

            foreach ($locationFacets as $key => $expr) {
                $query = clone $baseQuery;

                $rows = $query
                    ->select([
                        DB::raw("{$expr} as value"),
                        DB::raw('count(distinct game_commodities.id) as count'),
                    ])
                    ->groupByRaw($expr)
                    ->orderByRaw("{$expr} IS NULL, {$expr}")
                    ->get();

                $labelResolver = $key === 'kind'
                    ? static fn (mixed $value, ?string $label): ?string => is_string($value) ? Str::title($value) : null
                    : null;

                $out[$key] = FilterValues::fromRows($rows, labelResolver: $labelResolver);
            }

            $locationQuery = clone $baseQuery;

            if ($system = $request->input('filter.system')) {
                $locationQuery->where('sld.system', $system);
            }

            $locationRows = $locationQuery
                ->select([
                    DB::raw('sld.name as value'),
                    DB::raw('sld.system as grp'),
                    DB::raw('count(distinct game_commodities.id) as count'),
                ])
                ->groupByRaw('sld.name, sld.system')
                ->orderByRaw('sld.system, sld.name')
                ->get();

            $out['location'] = FilterValues::fromRows($locationRows, groupColumn: 'grp');

            if (DB::getDriverName() === 'pgsql') {
                $groupRows = QueryBuilder::for(Commodity::class, $request)
                    ->allowedFilters(...$this->allowedFilters())
                    ->select([
                        DB::raw("jsonb_array_elements_text(data->'CommodityGroups') as value"),
                        DB::raw('count(*) as count'),
                    ])
                    ->whereRaw("data->'CommodityGroups' IS NOT NULL")
                    ->groupByRaw('value')
                    ->orderBy('value')
                    ->get();

                $out['group'] = FilterValues::fromRows($groupRows);
            }

            return $out;
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []))) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                FilterCache::NAMESPACE_COMMODITIES,
                FilterCache::commoditiesKey($versionCode),
                $resolver
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
    }

    private function buildFiltersBaseQuery(Request $request, string $versionCode): QueryBuilder
    {
        $versionedResourceData = ResourceData::query()
            ->select('game_resource_data.id', 'game_resource_data.kind', 'grc.commodity_id')
            ->join('game_resource_commodity as grc', 'game_resource_data.id', '=', 'grc.resource_data_id')
            ->whereHas('gameVersion', static function (Builder $q) use ($versionCode): void {
                $q->whereRaw('LOWER(code) = ?', [strtolower($versionCode)]);
            });

        return QueryBuilder::for(Commodity::class, $request)
            ->leftJoinSub($versionedResourceData, 'grd', 'game_commodities.id', '=', 'grd.commodity_id')
            ->leftJoin('game_resource_locations as grl', 'grd.id', '=', 'grl.resource_data_id')
            ->leftJoin('game_resource_location_placements as grlp', 'grl.id', '=', 'grlp.resource_location_id')
            ->leftJoin('game_starmap_location_data as sld', 'grlp.starmap_location_data_id', '=', 'sld.id');
    }

    private function buildIndexQuery(CommodityIndexRequest $request): QueryBuilder
    {
        return QueryBuilder::for(Commodity::class, $request)
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(...$this->allowedSorts());
    }

    /**
     * @return array<int, string|AllowedSort>
     */
    private function allowedSorts(): array
    {
        return [
            'key',
            'name',
            AllowedSort::field('rarity', 'tier'),
            AllowedSort::field('density', 'density_g_per_cc'),
            AllowedSort::field('instability', 'instability'),
            AllowedSort::field('resistance', 'resistance'),
            AllowedSort::callback('signature', function (Builder $query, bool $descending): void {
                $direction = $descending ? 'desc' : 'asc';
                $versionCode = $this->gameVersionCode();

                $subQuery = ResourceData::query()
                    ->select('game_resource_data.signature')
                    ->join('game_resource_commodity as grc', 'game_resource_data.id', '=', 'grc.resource_data_id')
                    ->whereColumn('grc.commodity_id', 'game_commodities.id')
                    ->whereNotNull('game_resource_data.signature')
                    ->when($versionCode !== null, function (Builder $q) use ($versionCode): void {
                        $q->whereHas('gameVersion', static function (Builder $q) use ($versionCode): void {
                            $q->whereRaw('LOWER(code) = ?', [strtolower($versionCode)]);
                        });
                    })
                    ->orderByDesc('game_resource_data.signature')
                    ->limit(1);

                $query->orderByRaw("({$subQuery->toSql()}) {$direction} nulls last", $subQuery->getBindings());
            }),
        ];
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::callback('used', function (Builder $query, mixed $value): void {
                if ($value !== true) {
                    return;
                }

                $query->whereHas('blueprints', function (Builder $q): void {
                    $q->forRequestedOrDefaultVersion($this->gameVersionCode());
                });
            }),
            AllowedFilter::callback('system', function (Builder $query, mixed $value): void {
                $query->whereHas('resourceData', function (Builder $q) use ($value): void {
                    $q->forRequestedOrDefaultVersion($this->gameVersionCode())
                        ->whereHas('locations.starmapLocationData', function (Builder $q) use ($value): void {
                            $q->where('system', $value);
                        });
                });
            }),
            AllowedFilter::callback('type', function (Builder $query, mixed $value): void {
                $query->whereHas('resourceData', function (Builder $q) use ($value): void {
                    $q->forRequestedOrDefaultVersion($this->gameVersionCode())
                        ->whereHas('locations.starmapLocationData', function (Builder $q) use ($value): void {
                            $q->where('type_name', $value);
                        });
                });
            }),
            AllowedFilter::exact('rarity', 'tier'),
            AllowedFilter::callback('group', static function (Builder $query, mixed $value): void {
                $groups = is_array($value) ? $value : explode(',', (string) $value);
                $query->where(function (Builder $q) use ($groups): void {
                    foreach ($groups as $group) {
                        $encoded = json_encode([$group]);
                        $q->orWhereRaw("data->'CommodityGroups' @> ?::jsonb", [$encoded]);
                    }
                });
            }),
            AllowedFilter::callback('kind', function (Builder $query, mixed $value): void {
                $query->whereHas('resourceData', function (Builder $q) use ($value): void {
                    $q->forRequestedOrDefaultVersion($this->gameVersionCode())
                        ->where('kind', $value);
                });
            }),
            AllowedFilter::exact('refined_version', 'refined_version_name'),
            AllowedFilter::callback('location', function (Builder $query, mixed $value): void {
                $query->whereHas('resourceData', function (Builder $q) use ($value): void {
                    $q->forRequestedOrDefaultVersion($this->gameVersionCode())
                        ->whereHas('locations.starmapLocationData', function (Builder $q) use ($value): void {
                            $q->whereLike('name', '%'.$value.'%');
                        });
                });
            }),
            AllowedFilter::callback('query', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->where(static function (Builder $q) use ($value): void {
                    $q->whereLike('game_commodities.name', '%'.$value.'%')
                        ->orWhereLike('game_commodities.key', '%'.$value.'%');
                });
            }),
            AllowedFilter::callback('ship', $this->booleanFilterCallback(self::GROUP_SHIP)),
            AllowedFilter::callback('ground_vehicle', $this->booleanFilterCallback(self::GROUP_GROUND_VEHICLE)),
            AllowedFilter::callback('fps', $this->booleanFilterCallback(self::GROUPS_FPS)),
            AllowedFilter::callback('harvestable', $this->booleanFilterCallback(self::GROUPS_HARVESTABLE)),
            AllowedFilter::callback('salvage', function (Builder $query, mixed $value): void {
                if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    return;
                }

                $query->whereHas('resourceData', function (Builder $q): void {
                    $q->forRequestedOrDefaultVersion($this->gameVersionCode())
                        ->whereHas('locations', function (Builder $q): void {
                            $q->whereLike('group_name', 'Salvage%');
                        });
                });
            }),
            AllowedFilter::callback('mineable', function (Builder $query, mixed $value): void {
                $versionCode = $this->gameVersionCode();

                if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                    $query->whereHas('resourceData', function (Builder $q) use ($versionCode): void {
                        $q->forRequestedOrDefaultVersion($versionCode);
                    });
                } else {
                    $query->whereDoesntHave('resourceData', function (Builder $q) use ($versionCode): void {
                        $q->forRequestedOrDefaultVersion($versionCode);
                    });
                }
            }),
        ];
    }

    private function booleanFilterCallback(string|array $groupName): callable
    {
        return function (Builder $query, mixed $value) use ($groupName): void {
            if (! filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return;
            }

            $query->whereHas('resourceData', function (Builder $q) use ($groupName): void {
                $q->forRequestedOrDefaultVersion($this->gameVersionCode())
                    ->whereHas('locations', function (Builder $q) use ($groupName): void {
                        if (is_array($groupName)) {
                            $q->whereIn('group_name', $groupName);
                        } else {
                            $q->where('group_name', $groupName);
                        }
                    });
            });
        };
    }
}
