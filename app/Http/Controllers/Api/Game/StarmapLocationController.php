<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Includes\CustomEagerLoadInclude;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Starmap\StarmapLocationResource;
use App\Models\Game\StarmapLocationData;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StarmapLocationController extends Controller
{
    use FiltersJsonColumns;
    use ResolvesGameVersion;

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        $requestedFilterValues = fn (mixed $value): array => $this->requestedFilterValues($value);
        $jsonFilter = fn (string $path): Closure => function (Builder $query, mixed $value) use ($requestedFilterValues, $path): void {
            $this->applyJsonFilter($query, $path, $requestedFilterValues($value));
        };

        $amenityFilter = static function (Builder $query, mixed $value) use ($requestedFilterValues): void {
            $values = $requestedFilterValues($value);
            $textValues = array_values(array_filter($values, static fn (string $entry): bool => ! Str::isUuid($entry)));
            $uuidValues = array_values(array_filter($values, static fn (string $entry): bool => Str::isUuid($entry)));

            if ($textValues === [] && $uuidValues === []) {
                return;
            }

            $query->whereHas('amenities', static function (Builder $amenityQuery) use ($textValues, $uuidValues): void {
                $amenityQuery->where(static function (Builder $matchQuery) use ($textValues, $uuidValues): void {
                    if ($textValues !== []) {
                        $matchQuery->whereIn('name', $textValues)
                            ->orWhereIn('display_name', $textValues);
                    }

                    if ($uuidValues !== []) {
                        $method = $textValues === [] ? 'whereIn' : 'orWhereIn';
                        $matchQuery->{$method}('uuid', $uuidValues);
                    }
                });
            });
        };

        $entityTagFilter = static function (Builder $query, mixed $value) use ($requestedFilterValues): void {
            $values = $requestedFilterValues($value);
            $textValues = array_values(array_filter($values, static fn (string $entry): bool => ! Str::isUuid($entry)));
            $uuidValues = array_values(array_filter($values, static fn (string $entry): bool => Str::isUuid($entry)));

            if ($textValues === [] && $uuidValues === []) {
                return;
            }

            $query->whereHas('locationHierarchyEntityTag', static function (Builder $tagQuery) use ($textValues, $uuidValues): void {
                $tagQuery->where(static function (Builder $matchQuery) use ($textValues, $uuidValues): void {
                    if ($textValues !== []) {
                        $matchQuery->whereIn('name', $textValues);
                    }

                    if ($uuidValues !== []) {
                        $method = $textValues === [] ? 'whereIn' : 'orWhereIn';
                        $matchQuery->{$method}('uuid', $uuidValues);
                    }
                });
            });
        };

        $hasResourcesFilter = static function (Builder $query, mixed $value): void {
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                $query->whereHas('resourceLocations');
            } else {
                $query->whereDoesntHave('resourceLocations');
            }
        };

        $resourceFilter = static function (Builder $query, mixed $value) use ($requestedFilterValues): void {
            $values = $requestedFilterValues($value);
            $textValues = array_values(array_filter($values, static fn (string $entry): bool => ! Str::isUuid($entry)));
            $uuidValues = array_values(array_filter($values, static fn (string $entry): bool => Str::isUuid($entry)));

            if ($textValues === [] && $uuidValues === []) {
                return;
            }

            $query->whereHas('resourceLocations.resourceData.commodities', static function (Builder $commodityQuery) use ($textValues, $uuidValues): void {
                $commodityQuery->where(static function (Builder $matchQuery) use ($textValues, $uuidValues): void {
                    if ($textValues !== []) {
                        $matchQuery->whereIn('game_commodities.name', $textValues);
                    }

                    if ($uuidValues !== []) {
                        $method = $textValues === [] ? 'whereIn' : 'orWhereIn';
                        $matchQuery->{$method}('game_commodities.uuid', $uuidValues);
                    }
                });
            });
        };

        $hideMinorLocationsFilter = function (Builder $query, mixed $value): void {
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                $column = $this->getJsonTableName().'.'.$this->getJsonColumnName().'->OnlyShowWhenParentSelected';

                $query->where(function (Builder $q) use ($column): void {
                    $q->where($column, '!=', 'true')
                        ->orWhereNull($column);
                });
            }
        };

        return [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('type_name'),
            AllowedFilter::callback('type_classification', $jsonFilter('Type.Classification')),
            AllowedFilter::callback('respawn_location_type', $jsonFilter('RespawnLocationType')),
            AllowedFilter::callback('jurisdiction_name', $jsonFilter('Jurisdiction.Name')),
            AllowedFilter::callback('affiliation_name', $jsonFilter('Affiliation.DisplayName')),
            AllowedFilter::exact('is_scannable'),
            AllowedFilter::exact('block_travel'),
            AllowedFilter::callback('amenity', $amenityFilter),
            AllowedFilter::callback('tag', $entityTagFilter),
            AllowedFilter::partial('parent_name', 'parent.name'),
            AllowedFilter::exact('parent_uuid', 'parent.location.uuid'),
            AllowedFilter::partial('system'),
            AllowedFilter::callback('has_resources', $hasResourcesFilter),
            AllowedFilter::callback('resource', $resourceFilter),
            AllowedFilter::callback('hide_minor_locations', $hideMinorLocationsFilter),
            AllowedFilter::callback('query', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->where(static function (Builder $q) use ($value): void {
                    $q->whereLike('game_starmap_location_data.name', '%'.$value.'%');
                });
            }),
        ];
    }

    /**
     * @return array<string, Closure>
     */
    private function childCountRelation(int $gameVersionId): array
    {
        return [
            'children as child_count' => static function (Builder $query) use ($gameVersionId): void {
                $query->where('game_version_id', $gameVersionId);
            },
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function indexRelations(int $gameVersionId): array
    {
        return [
            'location',
            'gameVersion',
            'parent.location',
            'star.location',
            'amenities',
            'locationHierarchyEntityTag',
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function detailRelations(int $gameVersionId): array
    {
        return [
            'location',
            'gameVersion',
            'parent.location',
            'star.location',
            'amenities',
            'locationHierarchyEntityTag',
        ];
    }

    /**
     * @return array<string, Closure>
     */
    private function childSummaryRelation(int $gameVersionId): array
    {
        return [
            'children' => static function ($query) use ($gameVersionId): void {
                $query->where('game_version_id', $gameVersionId)
                    ->with([
                        'location',
                        'amenities',
                        'locationHierarchyEntityTag',
                    ])
                    ->withExists('resourceLocations as has_resources')
                    ->orderBy('name');
            },
        ];
    }

    private function buildBaseQuery(Request $request): QueryBuilder
    {
        $gameVersionId = $this->gameVersion()->id;

        return QueryBuilder::for(StarmapLocationData::class, $request)
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->whereNotNull('game_starmap_location_data.system')
            ->allowedFilters(...$this->allowedFilters())
            ->allowedIncludes('amenities')
            ->allowedSorts(...[
                'name',
                'type_name',
                'size',
                'child_count',
            ])
            ->defaultSort('name')
            ->with($this->indexRelations($gameVersionId))
            ->withCount($this->childCountRelation($gameVersionId))
            ->withExists('resourceLocations as has_resources')
            ->withCount('missions as mission_count');
    }

    #[OA\Get(
        path: '/api/locations',
        description: 'Returns paginated versioned starmap locations with optional filters. Each location includes amenities, hierarchy entity tags, parent and star relations, child count, mission count, and resource availability.',
        summary: 'Game Starmap Locations Overview',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: name, type_name, size, child_count.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '-size,name')
            ),
            new OA\Parameter(
                name: 'filter[name]',
                description: 'Partial match on location name.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Aberdeen')
            ),
            new OA\Parameter(
                name: 'filter[query]',
                description: 'Search locations by name.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'ArcCorp')
            ),
            new OA\Parameter(
                name: 'filter[type_name]',
                description: 'Exact match on location type name (see GET /api/locations/filters for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Planet')
            ),
            new OA\Parameter(
                name: 'filter[type_classification]',
                description: 'Location type classification from JSON data (see GET /api/locations/filters for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Outpost')
            ),
            new OA\Parameter(
                name: 'filter[respawn_location_type]',
                description: 'Respawn location type classification (see GET /api/locations/filters for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Hospital')
            ),
            new OA\Parameter(
                name: 'filter[jurisdiction_name]',
                description: 'Governing jurisdiction name (see GET /api/locations/filters for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'UEE')
            ),
            new OA\Parameter(
                name: 'filter[affiliation_name]',
                description: 'Faction or organization affiliation display name (see GET /api/locations/filters for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Private Security')
            ),
            new OA\Parameter(
                name: 'filter[is_scannable]',
                description: 'When true, only show scannable locations; when false, only show non-scannable.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
            new OA\Parameter(
                name: 'filter[block_travel]',
                description: 'When true, only show locations where travel is blocked; when false, only show locations where travel is allowed.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: false)
            ),
            new OA\Parameter(
                name: 'filter[amenity]',
                description: 'Filter by amenity name, display name, or UUID. Accepts comma-separated values (see GET /api/locations/filters for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Commodity Trading')
            ),
            new OA\Parameter(
                name: 'filter[tag]',
                description: 'Filter by hierarchy entity tag name or UUID. Accepts comma-separated values.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'HUR_L1')
            ),
            new OA\Parameter(
                name: 'filter[parent_name]',
                description: 'Partial match on parent location name.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'ArcCorp')
            ),
            new OA\Parameter(
                name: 'filter[parent_uuid]',
                description: 'Exact match on parent location UUID.',
                in: 'query',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: 'f8f07f5b-1c0e-47c9-aa50-46963065bf18')
            ),
            new OA\Parameter(
                name: 'filter[system]',
                description: 'Partial match on star system name (see GET /api/locations/filters for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Stanton System')
            ),
            new OA\Parameter(
                name: 'filter[has_resources]',
                description: 'When true, only locations with harvestable resources; when false, only locations without.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
            new OA\Parameter(
                name: 'filter[resource]',
                description: 'Filter by harvestable commodity name or UUID. Accepts comma-separated values (see GET /api/commodities for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Agricium')
            ),
            new OA\Parameter(
                name: 'filter[hide_minor_locations]',
                description: 'When true, exclude minor locations that are only shown when their parent is selected.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of starmap locations',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_starmap_location'))
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return StarmapLocationResource::collection(
            $this->buildBaseQuery($request)->jsonPaginate()
        );
    }

    #[OA\Get(
        path: '/api/locations/{identifier}',
        description: 'Retrieve a versioned starmap location by slug or UUID. Use the `include` parameter to load additional relations: `children` (child locations with amenities and tags), `resources` (harvestable resource placements with commodity data), `missions` (available missions with faction data).',
        summary: 'Game Starmap Location Detail',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(
                name: 'identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Starmap location slug or UUID',
                    type: 'string',
                    example: 'aberdeen',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A starmap location',
                content: new OA\JsonContent(ref: '#/components/schemas/game_starmap_location')
            ),
            new OA\Response(
                response: 404,
                description: 'No starmap location with specified identifier found.'
            ),
        ]
    )]
    public function show(Request $request, string $identifier): StarmapLocationResource
    {
        $gameVersionId = $this->gameVersion()->id;
        $versionCode = $this->gameVersionCode();
        $childSummaryRelation = $this->childSummaryRelation($gameVersionId);

        $location = QueryBuilder::for(StarmapLocationData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->whereHas('location', static function (Builder $query) use ($identifier): void {
                $query->when(Str::isUuid($identifier), fn (Builder $q) => $q->where('uuid', $identifier))
                    ->unless(Str::isUuid($identifier), fn (Builder $q) => $q->where('slug', $identifier));
            })
            ->whereNotNull('system')
            ->with($this->detailRelations($gameVersionId))
            ->withCount($this->childCountRelation($gameVersionId))
            ->withCount('missions as mission_count')
            ->allowedIncludes(
                AllowedInclude::custom('children', new CustomEagerLoadInclude($childSummaryRelation)),
                AllowedInclude::custom('resources', new CustomEagerLoadInclude([
                    'resourceLocations' => static function (BelongsToMany $q) use ($versionCode): void {
                        $q->whereHas('resourceData', static fn (Builder $subQ) => $subQ->forRequestedOrDefaultVersion($versionCode))
                            ->with([
                                'provider',
                                'resourceData' => static fn (BelongsTo $subQ) => $subQ
                                    ->forRequestedOrDefaultVersion($versionCode)
                                    ->with('commodities'),
                            ]);
                    },
                ])),
                AllowedInclude::custom('missions', new CustomEagerLoadInclude([
                    'missions' => static function (BelongsToMany $q): void {
                        $q->with(['mission', 'faction']);
                    },
                ])),
            )
            ->first();

        if ($location === null) {
            throw new NotFoundHttpException('No starmap location found for the specified identifier.');
        }

        return new StarmapLocationResource($location);
    }

    #[OA\Get(
        path: '/api/locations/filters',
        description: 'Return all available filter facet values for versioned starmap locations. Applies any provided filter parameters to scope the facet counts. Returns facets for: type_name, type_classification, respawn_location_type, jurisdiction_name, affiliation_name, system, parent_name, amenity, and resource.',
        summary: 'Game Starmap Location Filters',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'filter[name]',
                description: 'Partial match on location name.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Aberdeen')
            ),
            new OA\Parameter(
                name: 'filter[query]',
                description: 'Search locations by name.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'ArcCorp')
            ),
            new OA\Parameter(
                name: 'filter[type_name]',
                description: 'Exact match on location type name (see response for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Planet')
            ),
            new OA\Parameter(
                name: 'filter[type_classification]',
                description: 'Location type classification from JSON data (see response for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Outpost')
            ),
            new OA\Parameter(
                name: 'filter[respawn_location_type]',
                description: 'Respawn location type classification (see response for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Hospital')
            ),
            new OA\Parameter(
                name: 'filter[jurisdiction_name]',
                description: 'Governing jurisdiction name (see response for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'UEE')
            ),
            new OA\Parameter(
                name: 'filter[affiliation_name]',
                description: 'Faction or organization affiliation display name (see response for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Private Security')
            ),
            new OA\Parameter(
                name: 'filter[is_scannable]',
                description: 'When true, only show scannable locations; when false, only show non-scannable.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
            new OA\Parameter(
                name: 'filter[block_travel]',
                description: 'When true, only show locations where travel is blocked; when false, only show locations where travel is allowed.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: false)
            ),
            new OA\Parameter(
                name: 'filter[amenity]',
                description: 'Filter by amenity name, display name, or UUID. Accepts comma-separated values (see response for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Commodity Trading')
            ),
            new OA\Parameter(
                name: 'filter[tag]',
                description: 'Filter by hierarchy entity tag name or UUID. Accepts comma-separated values.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'HUR_L1')
            ),
            new OA\Parameter(
                name: 'filter[parent_name]',
                description: 'Partial match on parent location name.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'ArcCorp')
            ),
            new OA\Parameter(
                name: 'filter[parent_uuid]',
                description: 'Exact match on parent location UUID.',
                in: 'query',
                schema: new OA\Schema(type: 'string', format: 'uuid', example: 'f8f07f5b-1c0e-47c9-aa50-46963065bf18')
            ),
            new OA\Parameter(
                name: 'filter[system]',
                description: 'Partial match on star system name (see response for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Stanton System')
            ),
            new OA\Parameter(
                name: 'filter[has_resources]',
                description: 'When true, only locations with harvestable resources; when false, only locations without.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
            new OA\Parameter(
                name: 'filter[resource]',
                description: 'Filter by harvestable commodity name or UUID. Accepts comma-separated values (see GET /api/commodities for valid values).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'Agricium')
            ),
            new OA\Parameter(
                name: 'filter[hide_minor_locations]',
                description: 'When true, exclude minor locations that are only shown when their parent is selected.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean', example: true)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for starmap locations.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'type_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'type_classification', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'respawn_location_type', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'jurisdiction_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'affiliation_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'system', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'parent_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'amenity', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'resource', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
        $versionCode = $this->gameVersionCode();
        $resolver = function () use ($request, $versionCode): array {
            $baseQuery = QueryBuilder::for(StarmapLocationData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->whereNotNull('game_starmap_location_data.system')
                ->allowedFilters(...$this->allowedFilters());

            $facets = [
                'type_name' => [
                    'expr' => 'game_starmap_location_data.type_name',
                    'cast' => null,
                ],
                'type_classification' => [
                    'expr' => $this->jsonExpression('Type.Classification'),
                    'cast' => null,
                ],
                'respawn_location_type' => [
                    'expr' => $this->jsonExpression('RespawnLocationType'),
                    'cast' => null,
                ],
                'jurisdiction_name' => [
                    'expr' => $this->jsonExpression('Jurisdiction.Name'),
                    'cast' => null,
                ],
                'affiliation_name' => [
                    'expr' => $this->jsonExpression('Affiliation.DisplayName'),
                    'cast' => null,
                ],
                'system' => [
                    'expr' => 'game_starmap_location_data.system',
                    'cast' => null,
                ],
                'parent_name' => [
                    'expr' => 'parents.name',
                    'join' => static fn ($query) => $query
                        ->leftJoin('game_starmap_location_data as parents', 'game_starmap_location_data.parent_data_id', '=', 'parents.id'),
                    'cast' => null,
                ],
                'amenity' => [
                    'expr' => 'game_starmap_amenities.uuid',
                    'label_expr' => 'COALESCE(game_starmap_amenities.display_name, game_starmap_amenities.name)',
                    'group_by' => 'game_starmap_amenities.uuid, COALESCE(game_starmap_amenities.display_name, game_starmap_amenities.name)',
                    'order_by' => 'COALESCE(game_starmap_amenities.display_name, game_starmap_amenities.name) IS NULL, COALESCE(game_starmap_amenities.display_name, game_starmap_amenities.name), game_starmap_amenities.uuid',
                    'join' => static fn ($query) => $query
                        ->leftJoin('game_starmap_location_data_amenity', 'game_starmap_location_data.id', '=', 'game_starmap_location_data_amenity.location_data_id')
                        ->leftJoin('game_starmap_amenities', 'game_starmap_location_data_amenity.amenity_id', '=', 'game_starmap_amenities.id'),
                    'cast' => null,
                ],
                'resource' => [
                    'expr' => 'game_commodities.uuid',
                    'label_expr' => 'game_commodities.name',
                    'group_by' => 'game_commodities.uuid, game_commodities.name',
                    'order_by' => 'game_commodities.name IS NULL, game_commodities.name, game_commodities.uuid',
                    'join' => static fn ($query) => $query
                        ->leftJoin('game_resource_location_placements', 'game_starmap_location_data.id', '=', 'game_resource_location_placements.starmap_location_data_id')
                        ->leftJoin('game_resource_locations', 'game_resource_location_placements.resource_location_id', '=', 'game_resource_locations.id')
                        ->leftJoin('game_resource_data', 'game_resource_locations.resource_data_id', '=', 'game_resource_data.id')
                        ->leftJoin('game_resource_commodity', 'game_resource_data.id', '=', 'game_resource_commodity.resource_data_id')
                        ->leftJoin('game_commodities', 'game_resource_commodity.commodity_id', '=', 'game_commodities.id'),
                    'cast' => null,
                ],
            ];

            $out = [];

            foreach ($facets as $key => $facet) {
                $expr = $facet['expr'];
                $labelExpr = $facet['label_expr'] ?? null;
                $groupBy = $facet['group_by'] ?? $expr;
                $orderBy = $facet['order_by'] ?? "{$expr} IS NULL, {$expr}";
                $query = clone $baseQuery;

                if (isset($facet['join'])) {
                    ($facet['join'])($query);
                }

                $select = [
                    DB::raw("{$expr} as value"),
                ];

                if ($labelExpr !== null) {
                    $select[] = DB::raw("{$labelExpr} as label");
                }

                $rows = $query
                    ->select([
                        ...$select,
                        DB::raw('count(*) as count'),
                    ])
                    ->groupByRaw($groupBy)
                    ->orderByRaw($orderBy)
                    ->get();

                $out[$key] = FilterValues::fromRows($rows, $facet['cast'] ?? null);
            }

            return $out;
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []))) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                FilterCache::NAMESPACE_STARMAP_LOCATIONS,
                FilterCache::starmapLocationsKey($versionCode),
                $resolver
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
    }

    protected function getJsonTableName(): string
    {
        return 'game_starmap_location_data';
    }

    protected function getJsonColumnName(): string
    {
        return 'data';
    }

    /**
     * @return array<int, string>
     */
    private function requestedFilterValues(mixed $value): array
    {
        if (is_array($value)) {
            $values = $value;
        } elseif ($value === null) {
            return [];
        } else {
            $values = explode(',', (string) $value);
        }

        return array_values(array_filter(
            array_map(static fn (mixed $entry): string => trim((string) $entry), $values),
            static fn (string $entry): bool => $entry !== ''
        ));
    }
}
