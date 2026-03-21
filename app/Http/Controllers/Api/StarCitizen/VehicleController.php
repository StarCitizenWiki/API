<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen;

use App\Http\Controllers\Controller;
use App\Http\Filters\SortByRelation;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\StarCitizen\Vehicle\VehicleResource;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends Controller
{
    /**
     * Build base query with filters and sorts for Ship Matrix vehicles.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Vehicle::class, $request)
            ->allowedFilters(...[
                AllowedFilter::exact('manufacturer', 'manufacturer.name'),
                AllowedFilter::exact('size', 'size.slug'),
                AllowedFilter::scope('type'),
                AllowedFilter::scope('focus'),
                AllowedFilter::scope('production_status'),
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(...[
                AllowedSort::field('id', 'cig_id'),
                'chassis_id',
                'name',
                'msrp',
                'updated_at',
                'length',
                AllowedSort::field('width', 'beam'),
                'height',
                'cargo_capacity',
                AllowedSort::field('min_crew'),
                AllowedSort::field('max_crew'),
                AllowedSort::custom('manufacturer', new SortByRelation, 'manufacturer.name'),
                AllowedSort::custom('focus', new SortByRelation, 'focus.slug'),
                AllowedSort::custom('type', new SortByRelation, 'type.slug'),
                AllowedSort::custom('size', new SortByRelation, 'size.slug'),
            ]);
    }

    #[OA\Get(
        path: '/api/shipmatrix/vehicles',
        description: 'Returns paginated Ship Matrix vehicles with optional filters for manufacturer, size, and status.',
        summary: 'Ship Matrix Vehicles Overview',
        tags: ['Ship-Matrix', 'Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Filter by vehicle type slug', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[focus]', description: 'Filter by vehicle focus slug (comma-separated for multiple)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[production_status]', description: 'Filter by production status slug', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Ship-Matrix Vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ship_matrix_vehicle')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request)
            ->with(['skus', 'loaner']);
        $vehicles = $query->jsonPaginate();

        return VehicleResource::collection($vehicles);
    }

    #[OA\Get(
        path: '/api/shipmatrix/vehicles/filters',
        description: 'Return all available filter values for Ship Matrix vehicles.',
        summary: 'Ship Matrix Vehicle Filters',
        tags: ['Ship-Matrix', 'Vehicles'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for Ship Matrix vehicles.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'manufacturer', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'size', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'type', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'focus', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'production_status', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function filters(): JsonResponse
    {
        $filters = FilterCache::rememberForever(
            FilterCache::NAMESPACE_SHIPMATRIX,
            FilterCache::shipMatrixKey(),
            static function (): array {
                $baseQuery = (new Vehicle)->newQueryWithoutRelationships()->toBase();

                $facets = [
                    'manufacturer' => [
                        'expr' => 'shipmatrix_manufacturers.name',
                        'join' => static fn ($q) => $q->leftJoin('shipmatrix_manufacturers', 'shipmatrix_vehicles.manufacturer_id', '=', 'shipmatrix_manufacturers.id'),
                        'cast' => null,
                    ],
                    'size' => [
                        'expr' => 'shipmatrix_vehicle_sizes.slug',
                        'join' => static fn ($q) => $q->leftJoin('shipmatrix_vehicle_sizes', 'shipmatrix_vehicles.size_id', '=', 'shipmatrix_vehicle_sizes.id'),
                        'cast' => null,
                    ],
                    'type' => [
                        'expr' => 'shipmatrix_vehicle_types.slug',
                        'join' => static fn ($q) => $q->leftJoin('shipmatrix_vehicle_types', 'shipmatrix_vehicles.type_id', '=', 'shipmatrix_vehicle_types.id'),
                        'cast' => null,
                    ],
                    'focus' => [
                        'expr' => 'shipmatrix_vehicle_foci.slug',
                        'join' => static fn ($q) => $q
                            ->leftJoin('shipmatrix_vehicle_vehicle_focus', 'shipmatrix_vehicles.id', '=', 'shipmatrix_vehicle_vehicle_focus.vehicle_id')
                            ->leftJoin('shipmatrix_vehicle_foci', 'shipmatrix_vehicle_vehicle_focus.focus_id', '=', 'shipmatrix_vehicle_foci.id'),
                        'cast' => null,
                    ],
                    'production_status' => [
                        'expr' => 'shipmatrix_production_statuses.slug',
                        'join' => static fn ($q) => $q->leftJoin('shipmatrix_production_statuses', 'shipmatrix_vehicles.production_status_id', '=', 'shipmatrix_production_statuses.id'),
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

    #[OA\Get(
        path: '/api/shipmatrix/vehicles/{slug}',
        description: 'Retrieve a Ship Matrix vehicle by slug with optional related data.',
        summary: 'Ship Matrix Vehicle Detail',
        tags: ['Ship-Matrix', 'Vehicles'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Vehicle slug',
                    type: 'string',
                ),
            ),
            new OA\Parameter(
                name: 'include',
                description: 'Include additional relationships (components, loaner, skus)',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Ship-Matrix Vehicle',
                content: new OA\JsonContent(ref: '#/components/schemas/ship_matrix_vehicle')
            ),
            new OA\Response(
                response: 404,
                description: 'Vehicle not found'
            ),
        ]
    )]
    public function show(Request $request, string $slug): VehicleResource
    {
        try {
            $vehicle = Vehicle::query()
                ->where('slug', urldecode($slug))
                ->firstOrFail();

            // Handle optional includes
            $requestedIncludes = collect(explode(',', $request->get('include', '')))
                ->map('trim')
                ->filter()
                ->intersect(['components', 'loaner', 'skus'])
                ->toArray();

            if (! empty($requestedIncludes)) {
                $vehicle->load($requestedIncludes);
            }
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Vehicle with specified slug found.');
        }

        return new VehicleResource($vehicle);
    }

    #[OA\Post(
        path: '/api/shipmatrix/vehicles/search',
        description: 'Deprecated. Use GET /api/shipmatrix/vehicles?filter[name]={value} for name search. This endpoint will be removed in a future version.',
        summary: 'Ship Matrix Vehicle Search (Deprecated)',
        requestBody: new OA\RequestBody(
            description: 'Vehicle name to search for',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(type: 'object'),
                    example: '{"query": "Avenger"}',
                ),
            ]
        ),
        tags: ['Ship-Matrix', 'Vehicles', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Filter by vehicle type slug', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[focus]', description: 'Filter by vehicle focus slug', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[production_status]', description: 'Filter by production status slug', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Ship-Matrix Vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/ship_matrix_vehicle')
                )
            ),
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $toSearch = urldecode($request->validated('query'));

        $query = $this->buildBaseQuery($request)
            ->where(function (Builder $query) use ($toSearch) {
                $query->where('name', 'like', "%{$toSearch}%");
            })
            ->with(['skus', 'loaner']);

        $vehicles = $query->jsonPaginate();

        return VehicleResource::collection($vehicles)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }
}
