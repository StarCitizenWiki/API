<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Starmap\StarmapLocationResource;
use App\Models\Game\StarmapLocationData;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StarmapLocationController extends Controller
{
    use FiltersJsonColumns;
    use ResolvesGameVersion;

    /**
     * @param  array<int, string>  $allowedIncludes
     * @return array<int, string>
     */
    private function parseRequestedIncludes(Request $request, array $allowedIncludes): array
    {
        $requestedIncludes = array_values(array_filter(array_map(
            static fn (string $include): string => trim($include),
            explode(',', (string) $request->query('include', ''))
        )));
        $unsupportedIncludes = array_diff($requestedIncludes, $allowedIncludes);

        if ($unsupportedIncludes !== []) {
            $allowedMessage = $allowedIncludes === []
                ? 'Requested includes are not allowed.'
                : 'Requested includes are not allowed. Allowed includes: '.implode(', ', $allowedIncludes);

            throw new BadRequestHttpException($allowedMessage);
        }

        return $requestedIncludes;
    }

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

        return [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('type_name'),
            AllowedFilter::callback('type_classification', $jsonFilter('type.classification')),
            AllowedFilter::callback('respawn_location_type', $jsonFilter('respawnLocationType')),
            AllowedFilter::callback('jurisdiction_name', $jsonFilter('jurisdiction.name')),
            AllowedFilter::callback('affiliation_name', $jsonFilter('affiliation.displayName')),
            AllowedFilter::exact('is_scannable'),
            AllowedFilter::exact('block_travel'),
            AllowedFilter::callback('amenity', $amenityFilter),
            AllowedFilter::callback('tag', $entityTagFilter),
            AllowedFilter::partial('parent_name', 'parent.name'),
            AllowedFilter::exact('parent_uuid', 'parent.location.uuid'),
            AllowedFilter::partial('system'),
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
                    ])
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
            ->allowedSorts(...[
                'name',
                'type_name',
                'size',
                'child_count',
            ])
            ->defaultSort('name')
            ->with($this->indexRelations($gameVersionId))
            ->withCount($this->childCountRelation($gameVersionId));
    }

    #[OA\Get(
        path: '/api/locations',
        description: 'Returns paginated versioned starmap locations with optional filters.',
        summary: 'Game Starmap Locations Overview',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', example: '-size,name')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[respawn_location_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[jurisdiction_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[affiliation_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_scannable]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[block_travel]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[amenity]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[tag]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[system]', in: 'query', schema: new OA\Schema(type: 'string')),
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
        $this->parseRequestedIncludes($request, []);

        return StarmapLocationResource::collection(
            $this->buildBaseQuery($request)->jsonPaginate()
        );
    }

    #[OA\Get(
        path: '/api/locations/{identifier}',
        description: 'Retrieve a versioned starmap location by location UUID.',
        summary: 'Game Starmap Location Detail',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(
                name: 'identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Starmap location UUID',
                    type: 'string',
                    format: 'uuid',
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
                description: 'No starmap location with specified UUID found.'
            ),
        ]
    )]
    public function show(Request $request, string $identifier): StarmapLocationResource
    {
        $requestedIncludes = $this->parseRequestedIncludes($request, StarmapLocationResource::validIncludes());
        $gameVersionId = $this->gameVersion()->id;
        $location = StarmapLocationData::query()
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->whereHas('location', static function (Builder $query) use ($identifier): void {
                $query->where('uuid', $identifier);
            })
            ->whereNotNull('system')
            ->with($this->detailRelations($gameVersionId))
            ->withCount($this->childCountRelation($gameVersionId))
            ->first();

        if ($location === null) {
            throw new NotFoundHttpException('No starmap location with specified UUID found.');
        }

        if (in_array('children', $requestedIncludes, true)) {
            $location->load($this->childSummaryRelation($gameVersionId));
        }

        return new StarmapLocationResource($location);
    }

    #[OA\Get(
        path: '/api/locations/filters',
        description: 'Return all available filter values for versioned starmap locations.',
        summary: 'Game Starmap Location Filters',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[respawn_location_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[jurisdiction_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[affiliation_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_scannable]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[block_travel]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[amenity]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[tag]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[system]', in: 'query', schema: new OA\Schema(type: 'string')),
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
                    'expr' => $this->jsonExpression('type.classification'),
                    'cast' => null,
                ],
                'respawn_location_type' => [
                    'expr' => $this->jsonExpression('respawnLocationType'),
                    'cast' => null,
                ],
                'jurisdiction_name' => [
                    'expr' => $this->jsonExpression('jurisdiction.name'),
                    'cast' => null,
                ],
                'affiliation_name' => [
                    'expr' => $this->jsonExpression('affiliation.displayName'),
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
