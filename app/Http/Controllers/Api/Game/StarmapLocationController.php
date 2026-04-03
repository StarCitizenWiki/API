<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Starmap\StarmapLocationResource;
use App\Models\Game\StarmapLocationData;
use App\Support\Filters\FilterCache;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StarmapLocationController extends Controller
{
    use ResolvesGameVersion;

    /**
     * @return array<int, AllowedInclude|string>
     */
    private function allowedIncludes(): array
    {
        return [
            'location',
            AllowedInclude::relationship('parent', 'parent.location'),
            AllowedInclude::relationship('children', 'children.location'),
            'amenities',
            AllowedInclude::relationship('tag', 'locationHierarchyEntityTag'),
        ];
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        $gameVersionId = $this->gameVersion()->id;
        $requestedFilterValues = fn (mixed $value): array => $this->requestedFilterValues($value);

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
            AllowedFilter::exact('type_classification'),
            AllowedFilter::exact('respawn_location_type'),
            AllowedFilter::exact('is_scannable'),
            AllowedFilter::exact('hide_in_starmap'),
            AllowedFilter::exact('hide_in_world'),
            AllowedFilter::exact('block_travel'),
            AllowedFilter::exact('jurisdiction_name'),
            AllowedFilter::exact('jurisdiction_is_prison'),
            AllowedFilter::exact('affiliation_name'),
            AllowedFilter::callback('amenity', $amenityFilter),
            AllowedFilter::callback('tag', $entityTagFilter),
            AllowedFilter::partial('parent_name', 'parent.name'),
            AllowedFilter::exact('parent_uuid', 'parent.location.uuid'),
            AllowedFilter::callback('system_name', function (Builder $query, mixed $value) use ($gameVersionId, $requestedFilterValues): void {
                $values = $requestedFilterValues($value);

                if ($values === []) {
                    return;
                }

                $query->whereHas('location.system.data', function (Builder $systemDataQuery) use ($gameVersionId, $values): void {
                    $systemDataQuery->where('game_version_id', $gameVersionId);
                    $this->applyEscapedPartialFilter($systemDataQuery, $values);
                });
            }),
            AllowedFilter::exact('system_uuid', 'location.system_uuid'),
        ];
    }

    private function buildBaseQuery(Request $request): QueryBuilder
    {
        $gameVersionId = $this->gameVersion()->id;

        return QueryBuilder::for(StarmapLocationData::class, $request)
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->whereHas('location', static function (Builder $query): void {
                $query->whereNotNull('system_uuid');
            })
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(...[
                'name',
                'type_name',
                'type_classification',
                'respawn_location_type',
                'size',
                'minimum_display_size',
                'jurisdiction_name',
                'affiliation_name',
                'child_count',
            ])
            ->defaultSort('name')
            ->allowedIncludes(...$this->allowedIncludes())
            ->with([
                'location',
                'gameVersion',
                'parent.location',
                'amenities',
                'locationHierarchyEntityTag',
                'location.system.data' => static function ($query) use ($gameVersionId): void {
                    $query->where('game_version_id', $gameVersionId);
                },
            ])
            ->withCount([
                'children as child_count' => static function (Builder $query) use ($gameVersionId): void {
                    $query->where('game_version_id', $gameVersionId);
                },
            ]);
    }

    #[OA\Get(
        path: '/api/starmap-locations',
        description: 'Returns paginated versioned starmap locations with optional filters and includes.',
        summary: 'Game Starmap Locations Overview',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string', example: '-size,name')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[respawn_location_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_scannable]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[hide_in_starmap]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[hide_in_world]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[block_travel]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[jurisdiction_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[jurisdiction_is_prison]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[affiliation_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[amenity]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[tag]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[system_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[system_uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of starmap locations',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/game_starmap_location')
                )
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
        path: '/api/starmap-locations/{identifier}',
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
        try {
            $location = $this->buildBaseQuery($request)
                ->whereHas('location', static function (Builder $query) use ($identifier): void {
                    $query->where('uuid', $identifier);
                })
                ->first();

            if ($location === null) {
                throw new ModelNotFoundException;
            }
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No starmap location with specified UUID found.');
        }

        return new StarmapLocationResource($location);
    }

    #[OA\Get(
        path: '/api/starmap-locations/filters',
        description: 'Return all available filter values for versioned starmap locations.',
        summary: 'Game Starmap Location Filters',
        tags: ['In-Game', 'Starmap'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type_classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[respawn_location_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_scannable]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[hide_in_starmap]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[hide_in_world]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[block_travel]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[jurisdiction_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[jurisdiction_is_prison]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[affiliation_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[amenity]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[tag]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[parent_uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[system_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[system_uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
                                new OA\Property(property: 'type_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'type_classification', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'respawn_location_type', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'jurisdiction_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'affiliation_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'system_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'parent_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'jurisdiction_is_prison', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
                                new OA\Property(property: 'amenity', type: 'array', items: new OA\Items(ref: '#/components/schemas/starmap_filter_value')),
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
        $gameVersionId = $this->gameVersion()->id;
        $filtersHash = $this->filtersCacheHash($request);

        $filters = FilterCache::rememberForever(
            FilterCache::NAMESPACE_STARMAP_LOCATIONS,
            FilterCache::starmapLocationsFiltersKey($versionCode, $filtersHash),
            function () use ($request, $versionCode, $gameVersionId): array {
                $baseQuery = QueryBuilder::for(StarmapLocationData::class, $request)
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->whereHas('location', static function (Builder $query): void {
                        $query->whereNotNull('system_uuid');
                    })
                    ->allowedFilters(...$this->allowedFilters());

                $facets = [
                    'type_name' => [
                        'expr' => 'game_starmap_location_data.type_name',
                        'cast' => null,
                    ],
                    'type_classification' => [
                        'expr' => 'game_starmap_location_data.type_classification',
                        'cast' => null,
                    ],
                    'respawn_location_type' => [
                        'expr' => 'game_starmap_location_data.respawn_location_type',
                        'cast' => null,
                    ],
                    'jurisdiction_name' => [
                        'expr' => 'game_starmap_location_data.jurisdiction_name',
                        'cast' => null,
                    ],
                    'affiliation_name' => [
                        'expr' => 'game_starmap_location_data.affiliation_name',
                        'cast' => null,
                    ],
                    'system_name' => [
                        'expr' => 'system_data.name',
                        'join' => static fn ($query) => $query
                            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
                            ->join('game_starmap_locations as systems', 'game_starmap_locations.system_uuid', '=', 'systems.uuid')
                            ->join('game_starmap_location_data as system_data', static function ($join) use ($gameVersionId): void {
                                $join->on('system_data.starmap_location_id', '=', 'systems.id')
                                    ->where('system_data.game_version_id', '=', $gameVersionId);
                            }),
                        'cast' => null,
                    ],
                    'parent_name' => [
                        'expr' => 'parents.name',
                        'join' => static fn ($query) => $query
                            ->leftJoin('game_starmap_location_data as parents', 'game_starmap_location_data.parent_data_id', '=', 'parents.id'),
                        'cast' => null,
                    ],
                    'jurisdiction_is_prison' => [
                        'expr' => 'game_starmap_location_data.jurisdiction_is_prison',
                        'cast' => static fn ($value) => $value === null ? null : (bool) $value,
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
                        ->select($select)
                        ->groupByRaw($groupBy)
                        ->orderByRaw($orderBy)
                        ->get();

                    $out[$key] = $this->formatFilterRowsWithoutCount($rows, $facet['cast'] ?? null);
                }

                return $out;
            }
        );

        return response()->json([
            'filters' => $filters,
        ]);
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array<int, array{value: mixed, label: string}>
     */
    private function formatFilterRowsWithoutCount(Collection $rows, ?Closure $valueCaster = null): array
    {
        return $rows->map(function (object $row) use ($valueCaster): array {
            $value = $row->value ?? null;

            if ($valueCaster !== null) {
                $value = $valueCaster($value);
            }

            if ($value === '') {
                $value = null;
            }

            $label = $row->label ?? null;

            if (is_string($label) && $label === '') {
                $label = null;
            }

            return [
                'value' => $value,
                'label' => $label ?? $this->defaultFilterLabel($value),
            ];
        })->values()->all();
    }

    private function defaultFilterLabel(mixed $value): string
    {
        if ($value === null) {
            return 'Unknown';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return (string) $value;
    }

    /**
     * @param  array<int, mixed>  $values
     */
    private function applyEscapedPartialFilter(Builder $query, array $values, string $column = 'name'): void
    {
        $normalized = array_values(array_filter(
            array_map(static fn (mixed $entry): string => trim((string) $entry), $values),
            static fn (string $entry): bool => $entry !== ''
        ));

        if ($normalized === []) {
            return;
        }

        $wrappedColumn = $query->getQuery()->getGrammar()->wrap($query->qualifyColumn($column));
        $databaseDriver = $query->getConnection()->getDriverName();

        $query->where(function (Builder $partialQuery) use ($databaseDriver, $normalized, $wrappedColumn): void {
            foreach ($normalized as $value) {
                [$sql, $bindings] = $this->partialWhereRawParameters($value, $wrappedColumn, $databaseDriver);
                $partialQuery->orWhereRaw($sql, $bindings);
            }
        });
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    private function partialWhereRawParameters(string $value, string $column, string $driver): array
    {
        $loweredValue = mb_strtolower($value, 'UTF8');

        return [
            "LOWER({$column}) LIKE ?".$this->partialWhereEscapeClause($driver),
            ['%'.$this->escapeLikeValue($loweredValue).'%'],
        ];
    }

    private function escapeLikeValue(string $value): string
    {
        return str_replace(
            ['\\', '_', '%'],
            ['\\\\', '\\_', '\\%'],
            $value,
        );
    }

    private function partialWhereEscapeClause(string $driver): string
    {
        if (! in_array($driver, ['sqlite', 'sqlsrv'], true)) {
            return '';
        }

        return " ESCAPE '\\'";
    }

    private function filtersCacheHash(Request $request): string
    {
        $filters = $this->normalizeFilterParams($request->input('filter', []));

        if ($filters === []) {
            return 'all';
        }

        return hash('sha256', json_encode($filters) ?: '');
    }

    /**
     * @return array<string, string>
     */
    private function normalizeFilterParams(mixed $filters): array
    {
        if (! is_array($filters) || $filters === []) {
            return [];
        }

        $normalized = [];

        foreach ($filters as $field => $value) {
            if (! is_string($field) || $field === '') {
                continue;
            }

            $normalizedValue = $this->normalizeFilterValue($value);

            if ($normalizedValue === null) {
                continue;
            }

            $normalized[$field] = $normalizedValue;
        }

        ksort($normalized);

        return $normalized;
    }

    private function normalizeFilterValue(mixed $value): ?string
    {
        if (is_array($value)) {
            $values = array_map(static fn (mixed $entry): string => trim((string) $entry), $value);
            $values = array_values(array_filter($values, static fn (string $entry): bool => $entry !== ''));

            if ($values === []) {
                return null;
            }

            sort($values);

            return implode(',', $values);
        }

        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $parts = array_map('trim', explode(',', $normalized));
        $parts = array_values(array_filter($parts, static fn (string $entry): bool => $entry !== ''));

        if ($parts === []) {
            return null;
        }

        sort($parts);

        return implode(',', $parts);
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
