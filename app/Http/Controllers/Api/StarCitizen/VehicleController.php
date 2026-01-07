<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen;

use App\Http\Controllers\Controller;
use App\Http\Filters\ShipMatrixFocusFilter;
use App\Http\Filters\ShipMatrixProductionStatusFilter;
use App\Http\Filters\ShipMatrixTypeFilter;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\StarCitizen\Vehicle\VehicleResource;
use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Focus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends Controller
{
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
        $query = QueryBuilder::for(Vehicle::class, $request)
            ->allowedFilters([
                AllowedFilter::exact('manufacturer', 'manufacturer.name'),
                AllowedFilter::exact('size', 'size.slug'),
                AllowedFilter::custom('type', new ShipMatrixTypeFilter),
                AllowedFilter::custom('focus', new ShipMatrixFocusFilter),
                AllowedFilter::custom('production_status', new ShipMatrixProductionStatusFilter),
                AllowedFilter::partial('name'),
            ]);

        $vehicles = $query
            ->allowedSorts([
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
                AllowedSort::field('msrp'),
                AllowedSort::callback('manufacturer', $this->relationSortCallback('manufacturer')),
                AllowedSort::callback('focus', $this->relationSortCallback('focus')),
                AllowedSort::callback('type', $this->relationSortCallback('type')),
                AllowedSort::callback('size', $this->relationSortCallback('size')),
            ])
            ->jsonPaginate();

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

                $manufacturerRows = (clone $baseQuery)
                    ->leftJoin('shipmatrix_manufacturers', 'shipmatrix_vehicles.manufacturer_id', '=', 'shipmatrix_manufacturers.id')
                    ->selectRaw('shipmatrix_manufacturers.name as value, count(*) as count')
                    ->groupBy('shipmatrix_manufacturers.name')
                    ->orderByRaw('shipmatrix_manufacturers.name IS NULL, shipmatrix_manufacturers.name')
                    ->get();

                $sizeRows = (clone $baseQuery)
                    ->leftJoin('shipmatrix_vehicle_sizes', 'shipmatrix_vehicles.size_id', '=', 'shipmatrix_vehicle_sizes.id')
                    ->selectRaw('shipmatrix_vehicle_sizes.slug as value, count(*) as count')
                    ->groupBy('shipmatrix_vehicle_sizes.slug')
                    ->orderByRaw('shipmatrix_vehicle_sizes.slug IS NULL, shipmatrix_vehicle_sizes.slug')
                    ->get();

                $typeRows = (clone $baseQuery)
                    ->leftJoin('shipmatrix_vehicle_types', 'shipmatrix_vehicles.type_id', '=', 'shipmatrix_vehicle_types.id')
                    ->selectRaw('shipmatrix_vehicle_types.slug as value, count(*) as count')
                    ->groupBy('shipmatrix_vehicle_types.slug')
                    ->orderByRaw('shipmatrix_vehicle_types.slug IS NULL, shipmatrix_vehicle_types.slug')
                    ->get();

                $focusRows = (clone $baseQuery)
                    ->leftJoin('shipmatrix_vehicle_vehicle_focus', 'shipmatrix_vehicles.id', '=', 'shipmatrix_vehicle_vehicle_focus.vehicle_id')
                    ->leftJoin('shipmatrix_vehicle_foci', 'shipmatrix_vehicle_vehicle_focus.focus_id', '=', 'shipmatrix_vehicle_foci.id')
                    ->selectRaw('shipmatrix_vehicle_foci.slug as value, count(*) as count')
                    ->groupBy('shipmatrix_vehicle_foci.slug')
                    ->orderByRaw('shipmatrix_vehicle_foci.slug IS NULL, shipmatrix_vehicle_foci.slug')
                    ->get();

                $productionStatusRows = (clone $baseQuery)
                    ->leftJoin('shipmatrix_production_statuses', 'shipmatrix_vehicles.production_status_id', '=', 'shipmatrix_production_statuses.id')
                    ->selectRaw('shipmatrix_production_statuses.slug as value, count(*) as count')
                    ->groupBy('shipmatrix_production_statuses.slug')
                    ->orderByRaw('shipmatrix_production_statuses.slug IS NULL, shipmatrix_production_statuses.slug')
                    ->get();

                return [
                    'manufacturer' => FilterValues::fromRows($manufacturerRows),
                    'size' => FilterValues::fromRows($sizeRows),
                    'type' => FilterValues::fromRows($typeRows),
                    'focus' => FilterValues::fromRows($focusRows),
                    'production_status' => FilterValues::fromRows($productionStatusRows),
                ];
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
            $includes = collect(explode(',', $request->get('include', '')))
                ->map('trim')
                ->filter()
                ->toArray();

            if (in_array('components', $includes, true)) {
                $vehicle->load('components');
            }

            if (in_array('loaner', $includes, true)) {
                $vehicle->load('loaner');
            }

            if (in_array('skus', $includes, true)) {
                $vehicle->load('skus');
            }
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Vehicle with specified slug found.');
        }

        return new VehicleResource($vehicle);
    }

    #[OA\Post(
        path: '/api/shipmatrix/vehicles/search',
        description: 'Search Ship Matrix vehicles by name with optional filters for manufacturer, size, and status.',
        summary: 'Ship Matrix Vehicle Search',
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
            new OA\Response(
                response: 404,
                description: 'No matching vehicles found'
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $toSearch = urldecode($request->validated('query'));

        $query = QueryBuilder::for(Vehicle::class, $request)
            ->allowedFilters([
                AllowedFilter::exact('manufacturer', 'manufacturer.name'),
                AllowedFilter::exact('size', 'size.slug'),
                AllowedFilter::custom('type', new ShipMatrixTypeFilter),
                AllowedFilter::custom('focus', new ShipMatrixFocusFilter),
                AllowedFilter::custom('production_status', new ShipMatrixProductionStatusFilter),
                AllowedFilter::partial('name'),
            ])
            ->where(function (Builder $query) use ($toSearch) {
                $query->where('name', 'like', "%{$toSearch}%");
            });

        $vehicles = $query->jsonPaginate();

        return VehicleResource::collection($vehicles);
    }

    private function relationSortCallback(string $relation): callable
    {
        return static function (Builder $query, bool $descending, string $property) use ($relation): void {
            $vehiclesTable = $query->getModel()->getTable();
            $direction = $descending ? 'desc' : 'asc';

            $subquery = match ($relation) {
                'manufacturer' => (new Manufacturer)->newQuery()
                    ->select('name')
                    ->whereColumn('shipmatrix_manufacturers.id', $vehiclesTable.'.manufacturer_id'),
                'type' => (new Type)->newQuery()
                    ->select('slug')
                    ->whereColumn('shipmatrix_vehicle_types.id', $vehiclesTable.'.type_id'),
                'size' => (new Size)->newQuery()
                    ->select('slug')
                    ->whereColumn('shipmatrix_vehicle_sizes.id', $vehiclesTable.'.size_id'),
                'focus' => (new Focus)->newQuery()
                    ->selectRaw('min(shipmatrix_vehicle_foci.slug)')
                    ->join(
                        'shipmatrix_vehicle_vehicle_focus',
                        'shipmatrix_vehicle_foci.id',
                        '=',
                        'shipmatrix_vehicle_vehicle_focus.focus_id'
                    )
                    ->whereColumn('shipmatrix_vehicle_vehicle_focus.vehicle_id', $vehiclesTable.'.id'),
                default => null,
            };

            if ($subquery === null) {
                return;
            }

            $query->orderBy($subquery, $direction);
        };
    }
}
