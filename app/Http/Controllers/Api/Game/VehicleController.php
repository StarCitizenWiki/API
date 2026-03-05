<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Filters\SortByRelation;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Vehicle\VehicleResource;
use App\Http\Resources\StarCitizen\Vehicle\VehicleResource as ShipMatrixVehicleResource;
use App\Models\Game\Item;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends Controller
{
    use FiltersJsonColumns;
    use ResolvesGameVersion;

    protected function getJsonTableName(): string
    {
        return 'game_vehicle_data';
    }

    protected function getJsonColumnName(): string
    {
        return 'data';
    }

    #[OA\Get(
        path: '/api/vehicles',
        description: 'Returns paginated in-game vehicles for the requested version and vehicle type with optional filters.',
        summary: 'In-Game Vehicles Overview',
        tags: ['In-Game', 'Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Examples: name, -size, cargo_capacity, -speed.scm, shield.face_type. Use comma for multiple: size,-cargo_capacity',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '-cargo_capacity'
                )
            ),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[size_class]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[career]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[role]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_vehicle]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_gravlev]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_spaceship]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mass_total]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cargo_capacity]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[vehicle_inventory]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[crew.min]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[health]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.hp]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.face_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[speed.scm]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[speed.max]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[armor.health]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.length]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.width]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.height]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_quantum]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_shields]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_quantum]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_shields]', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/game_vehicle')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request);
        $vehicles = $query->jsonPaginate();

        return VehicleResource::collection(
            $this->transformToVehicles($vehicles)
        );
    }

    #[OA\Get(
        path: '/api/vehicles/{identifier}',
        description: 'Retrieve a vehicle by name, class name, or UUID along with requested includes.',
        summary: 'In-Game Vehicle Detail',
        tags: ['In-Game', 'Vehicles'],
        parameters: [
            new OA\Parameter(
                name: 'identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Vehicle name, class_name, or UUID',
                    type: 'string',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Vehicle',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: '#/components/schemas/game_vehicle'),
                        new OA\Schema(ref: '#/components/schemas/ship_matrix_vehicle'),
                    ]
                )
            ),
        ]
    )]
    public function show(Request $request, string $identifier): AbstractBaseResource
    {
        $original = $identifier;
        $versionCode = $this->gameVersionCode();
        $identifier = $this->cleanQueryName($identifier);
        $isUuid = Str::isUuid($identifier);
        $allowedIncludes = $this->allowedIncludes();

        $this->normalizeIncludes($request, $allowedIncludes);

        try {
            $vehicleData = QueryBuilder::for(VehicleData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->where(function (Builder $q) use ($identifier, $isUuid, $original) {
                    $underscored = str_replace(' ', '_', $identifier);

                    if ($isUuid) {
                        $q->whereHas('vehicle', fn (Builder $itemQuery) => $itemQuery->where('uuid', $identifier));
                    }

                    $q->orWhere('name', $identifier)
                        ->orWhereRaw('upper(display_name) = ?', [strtoupper($identifier)])
                        ->orWhereRaw('upper(class_name) = ?', [strtoupper($original)])
                        ->orWhere('class_name', strtoupper($underscored))
                        ->orWhere('class_name', 'LIKE', "%_{$underscored}");
                })
                ->allowedIncludes($allowedIncludes)
                ->with(['vehicle', 'gameVersion', 'manufacturer'])
                ->first();

            if ($vehicleData === null) {
                $shipMatrixVehicle = ShipMatrixVehicle::query()
                    ->where('name', $identifier)
                    ->orWhere('slug', $identifier)
                    ->with([
                        'foci',
                        'manufacturer',
                        'productionStatus',
                        'productionNote',
                        'type',
                        'size',
                        'loaner',
                        'skus',
                    ])
                    ->first();

                if ($shipMatrixVehicle !== null) {
                    return new ShipMatrixVehicleResource($shipMatrixVehicle);
                }

                throw new ModelNotFoundException('No Vehicle with specified UUID or Name found.');
            }

            $vehicle = $vehicleData->vehicle;
            $vehicle->setRelation('data', collect([$vehicleData]));

            $shipMatrixRelations = [
                'shipMatrixVehicle.foci',
                'shipMatrixVehicle.productionStatus',
                'shipMatrixVehicle.productionNote',
                'shipMatrixVehicle.type',
                'shipMatrixVehicle.size',
                'shipMatrixVehicle.loaner',
                'shipMatrixVehicle.skus',
                'shipMatrixVehicle.manufacturer',
                'shipMatrixVehicle.components',
            ];

            if ($this->requestIncludesComponents($request)) {
                $shipMatrixRelations[] = 'shipMatrixVehicle.components';
            }

            $vehicleData->load($shipMatrixRelations);

            $vehicle->load(['item' => function ($query) use ($vehicleData) {
                $query->with(['data' => function ($q) use ($vehicleData) {
                    $q->where('game_version_id', $vehicleData->game_version_id)
                        ->with('descriptionData');
                }]);
            }]);

            $this->eagerLoadPortItems($vehicleData, $vehicleData->game_version_id);
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Vehicle with specified UUID or Name found.');
        }

        return new VehicleResource($vehicle);
    }

    #[OA\Post(
        path: '/api/vehicles/search',
        description: 'Deprecated. Use GET /api/vehicles?filter[name]={value} for name search. This endpoint will be removed in a future version.',
        summary: 'In-Game Vehicle Search (Deprecated)',
        requestBody: new OA\RequestBody(
            description: 'Vehicle name, class_name, career, or UUID',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(type: 'object'),
                    example: '{"query": "Avenger"}',
                ),
            ]
        ),
        tags: ['In-Game', 'Vehicles', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[size_class]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[career]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[role]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_vehicle]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_gravlev]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_spaceship]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mass_total]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cargo_capacity]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[vehicle_inventory]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[crew.min]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[health]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.hp]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[shield.face_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[speed.scm]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[speed.max]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[armor.health]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.length]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.width]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[cross_section.height]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_quantum]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.ir_shields]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_quantum]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[signature.em_shields]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[focus]', description: 'Filter by Ship-Matrix focus slug', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Filter by Ship-Matrix type slug', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[production_status]', description: 'Filter by Ship-Matrix production status slug', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/game_vehicle')
                )
            ),
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|\Illuminate\Http\JsonResponse
    {
        $toSearch = $request->validated('query');
        $isUuid = Str::isUuid($toSearch);

        $query = $this->buildBaseQuery($request)
            ->where(function (Builder $query) use ($toSearch, $isUuid) {
                $underscored = str_replace(' ', '_', $toSearch);
                $query->where('name', 'like', "%{$toSearch}%")
                    ->orWhere('class_name', 'LIKE', "%{$underscored}%")
                    ->orWhere('career', 'LIKE', "%{$toSearch}%");

                if ($isUuid) {
                    $query->orWhereHas('vehicle', fn (Builder $q) => $q->where('uuid', $toSearch));
                }
            });

        $vehicles = $query->jsonPaginate();

        return VehicleResource::collection(
            $this->transformToVehicles($vehicles)
        )->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }

    #[OA\Get(
        path: '/api/vehicles/filters',
        description: 'Return all available filter values for in-game vehicles.',
        summary: 'In-Game Vehicle Filters',
        tags: ['In-Game', 'Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for in-game vehicles.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'manufacturer', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'is_vehicle', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'is_gravlev', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'is_spaceship', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'size', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'role', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'career', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
        $vehicleType = $request->route()->defaults['vehicle_type'] ?? 'vehicles';

        $filters = FilterCache::rememberForever(
            FilterCache::NAMESPACE_VEHICLES,
            FilterCache::vehiclesKey($versionCode, $vehicleType),
            static function () use ($versionCode, $vehicleType): array {
                $baseQuery = VehicleData::query()
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->forVehicleType($vehicleType);

                $facets = [
                    'manufacturer' => [
                        'expr' => 'game_manufacturers.name',
                        'join' => static fn ($q) => $q->leftJoinRelationship('manufacturer'),
                        'cast' => null,
                    ],
                    'is_vehicle' => [
                        'expr' => 'game_vehicle_data.is_vehicle',
                        'cast' => static fn ($value) => $value === null ? null : (bool) $value,
                    ],
                    'is_gravlev' => [
                        'expr' => 'game_vehicle_data.is_gravlev',
                        'cast' => static fn ($value) => $value === null ? null : (bool) $value,
                    ],
                    'is_spaceship' => [
                        'expr' => 'game_vehicle_data.is_spaceship',
                        'cast' => static fn ($value) => $value === null ? null : (bool) $value,
                    ],
                    'size' => [
                        'expr' => 'game_vehicle_data.size',
                        'cast' => static fn ($value) => $value === null ? null : (int) $value,
                    ],
                    'role' => [
                        'expr' => 'game_vehicle_data.role',
                        'cast' => null,
                    ],
                    'career' => [
                        'expr' => 'game_vehicle_data.career',
                        'cast' => null,
                    ],
                    'shield.face_type' => [
                        'expr' => "(game_vehicle_data.data #>> '{ShieldController,FaceType}')",
                        'cast' => null,
                    ],
                ];

                $out = [];

                foreach ($facets as $key => $facet) {
                    $expr = $facet['expr'];

                    $q = clone $baseQuery;

                    if (isset($facet['join'])) {
                        ($facet['join'])($q);
                    }

                    $rows = $q
                        ->select([
                            DB::raw("{$expr} as value"),
                            DB::raw('count(*) as count'),
                        ])
                        ->groupByRaw($expr)
                        ->orderByRaw("{$expr} IS NULL, {$expr}")
                        ->get();

                    $out[$key] = FilterValues::fromRows($rows, $facet['cast'] ?? null);
                }

                return $out;
            }
        );

        return response()->json([
            'filters' => $filters,
        ]);
    }

    /**
     * Normalize requested includes: case-insensitive, aliases, and filtering to allowed list.
     */
    private function normalizeIncludes(Request $request, array $allowedIncludes): void
    {
        $includeParam = $request->query('include');

        if ($includeParam === null || $includeParam === '') {
            return;
        }

        $allowedLookup = collect($allowedIncludes)
            ->mapWithKeys(fn (string $include) => [strtolower($include) => $include])
            ->toArray();

        $aliases = [
            'components' => 'shipmatrixvehicle.components',
        ];

        $resolved = collect(explode(',', (string) $includeParam))
            ->map(fn (string $include) => strtolower(trim($include)))
            ->filter()
            ->map(function (string $include) use ($aliases, $allowedLookup) {
                $include = $aliases[$include] ?? $include;

                return $allowedLookup[$include] ?? null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($resolved->isEmpty()) {
            $request->query->remove('include');

            return;
        }

        $request->query->set('include', $resolved->implode(','));
    }

    /**
     * Allow components to be requested via include=components or include=shipMatrixVehicle.components.
     */
    private function requestIncludesComponents(Request $request): bool
    {
        $includeParam = $request->query('include', '');

        if ($includeParam === '') {
            return false;
        }

        $includes = collect(explode(',', (string) $includeParam))
            ->map(fn (string $include) => strtolower(trim($include)))
            ->filter();

        return $includes->contains('components') || $includes->contains('shipmatrixvehicle.components');
    }

    /**
     * Build base query with filters, sorts, and includes for vehicles.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        $versionCode = $this->gameVersionCode();
        $vehicleType = $request->route()->defaults['vehicle_type'] ?? 'vehicles';
        $allowedIncludes = $this->allowedIncludes();

        $this->normalizeIncludes($request, $allowedIncludes);

        return QueryBuilder::for(VehicleData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->forVehicleType($vehicleType)
            ->allowedFilters($this->allowedFilters())
            ->allowedSorts($this->allowedSorts())
            ->defaultSort('name')
            ->allowedIncludes($allowedIncludes)
            ->with(['vehicle', 'gameVersion', 'manufacturer', 'shipMatrixVehicle.loaner', 'shipMatrixVehicle.skus']);
    }

    /**
     * Query builder includes for vehicles.
     */
    private function allowedIncludes(): array
    {
        return [
            'components',
            AllowedInclude::relationship('shipMatrixVehicle.components', 'components'),
        ];
    }

    /**
     * Allowed filters for in-game vehicles, including JSON-backed fields.
     */
    private function allowedFilters(): array
    {
        $manufacturerFilter = static function (Builder $query, mixed $value): void {
            $values = is_array($value) ? $value : [$value];

            $query->whereHas('manufacturer', static function ($manufacturerQuery) use ($values): void {
                $manufacturerQuery
                    ->whereIn('name', $values)
                    ->orWhereIn('code', $values);
            });
        };

        return [
            AllowedFilter::callback('manufacturer', $manufacturerFilter),
            AllowedFilter::callback('manufacturer.name', $manufacturerFilter),
            AllowedFilter::partial('class_name'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('career'),
            AllowedFilter::partial('role'),
            AllowedFilter::exact('is_vehicle'),
            AllowedFilter::exact('is_gravlev'),
            AllowedFilter::exact('is_spaceship'),
            AllowedFilter::exact('size'),
            AllowedFilter::exact('size_class', 'size'),
            AllowedFilter::callback('mass_total', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'MassTotal', $value, 'numeric');
            }),
            AllowedFilter::callback('cargo_capacity', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Cargo', $value, 'numeric');
            }),
            AllowedFilter::callback('vehicle_inventory', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Stowage', $value, 'numeric');
            }),
            AllowedFilter::callback('crew.min', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Crew', $value, 'numeric');
            }),
            AllowedFilter::callback('health', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Health', $value, 'numeric');
            }),
            AllowedFilter::callback('shield.hp', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'ShieldsTotal.Hp', $value, 'numeric');
            }),
            AllowedFilter::callback('shield.face_type', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'ShieldController.FaceType', $value);
            }),
            AllowedFilter::callback('speed.scm', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'FlightCharacteristics.Speeds.Scm', $value, 'numeric');
            }),
            AllowedFilter::callback('speed.max', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'FlightCharacteristics.Speeds.Max', $value, 'numeric');
            }),
            AllowedFilter::callback('armor.health', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Armor.Health', $value, 'numeric');
            }),
            AllowedFilter::callback('cross_section.length', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'CrossSection.X', $value, 'numeric');
            }),
            AllowedFilter::callback('cross_section.width', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'CrossSection.Y', $value, 'numeric');
            }),
            AllowedFilter::callback('cross_section.height', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'CrossSection.Z', $value, 'numeric');
            }),
            AllowedFilter::callback('signature.ir_quantum', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Emission.IrQuantum', $value, 'numeric');
            }),
            AllowedFilter::callback('signature.ir_shields', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Emission.IrShields', $value, 'numeric');
            }),
            AllowedFilter::callback('signature.em_quantum', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Emission.EmQuantum', $value, 'numeric');
            }),
            AllowedFilter::callback('signature.em_shields', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Emission.EmShields', $value, 'numeric');
            }),
        ];
    }

    /**
     * Allowed sorts for in-game vehicles, including JSON-backed fields.
     */
    private function allowedSorts(): array
    {
        return array_merge(
            [
                'name',
                'class_name',
                'career',
                'role',
                'is_vehicle',
                'is_gravlev',
                'is_spaceship',
                'size',
                AllowedSort::custom('manufacturer', new SortByRelation, 'manufacturer.name'),
                AllowedSort::custom('msrp', new SortByRelation, 'shipmatrixVehicle.msrp'),
                AllowedSort::field('size_class', 'size'),
            ],
            $this->allowedJsonSorts()
        );
    }

    /**
     * Get JSON-backed sort fields from configuration.
     *
     * @return array<AllowedSort>
     */
    private function allowedJsonSorts(): array
    {
        $sortConfig = config('sorts.vehicles', []);
        $allowedSorts = [];

        foreach ($sortConfig as $sortKey => $config) {
            $allowedSorts[] = $this->jsonSort(
                // Filter only by path in raw json
                $config['path'],
                $config['path'],
                $config['cast'] ?? 'numeric'
            );
        }

        return $allowedSorts;
    }

    /**
     * Transform VehicleData collection to Vehicles for resources.
     *
     * VehicleLinkResource expects Vehicle models with loaded data relationship.
     * This method transforms the VehicleData query results back to Vehicle models.
     */
    private function transformToVehicles($vehicleDataCollection): mixed
    {
        if ($vehicleDataCollection instanceof LengthAwarePaginator) {
            $vehicles = $vehicleDataCollection->getCollection()->map(function (VehicleData $vehicleData) {
                $vehicle = $vehicleData->vehicle;
                $vehicle->setRelation('data', collect([$vehicleData]));

                return $vehicle;
            });

            return $vehicleDataCollection->setCollection($vehicles);
        }

        return $vehicleDataCollection->map(function (VehicleData $vehicleData) {
            $vehicle = $vehicleData->vehicle;
            $vehicle->setRelation('data', collect([$vehicleData]));

            return $vehicle;
        });
    }

    /**
     * Eager load all port items from the vehicle's Loadout data.
     *
     * Extracts all item UUIDs from the nested Loadout structure and loads
     * them with their relationships to prevent N+1 queries in PortResource.
     */
    private function eagerLoadPortItems(VehicleData $vehicleData, int $gameVersionId): void
    {
        $uuids = $this->extractPortUuids($vehicleData->data['Loadout'] ?? []);

        if ($uuids === []) {
            return;
        }

        $items = Item::query()
            ->whereIn('uuid', $uuids)
            ->with(['data' => function ($query) use ($gameVersionId) {
                $query->where('game_version_id', $gameVersionId)
                    ->with(['manufacturer', 'gameVersion', 'descriptionData']);
            }])
            ->get()
            ->keyBy('uuid');

        request()->attributes->set('eager_loaded_port_items', $items);
    }

    /**
     * Recursively extract all item UUIDs from the Loadout structure.
     *
     * @return array<string>
     */
    private function extractPortUuids(array $loadout): array
    {
        $uuids = [];

        foreach ($loadout as $port) {
            $uuid = $port['UUID'] ?? null;

            if (is_string($uuid) && $uuid !== '') {
                $uuids[] = $uuid;
            }

            if (isset($port['Loadout']) && is_array($port['Loadout'])) {
                $uuids = array_merge($uuids, $this->extractPortUuids($port['Loadout']));
            }
        }

        return array_unique(array_filter($uuids));
    }
}
