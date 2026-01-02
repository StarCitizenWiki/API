<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Filters\VehicleFocusFilter;
use App\Http\Filters\VehicleProductionStatusFilter;
use App\Http\Filters\VehicleTypeFilter;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Vehicle\VehicleLinkResource;
use App\Http\Resources\Game\Vehicle\VehicleResource;
use App\Http\Resources\StarCitizen\Vehicle\VehicleResource as ShipMatrixVehicleResource;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends Controller
{
    use ResolvesGameVersion;

    #[OA\Get(
        path: '/api/vehicles',
        tags: ['In-Game', 'Vehicles'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[career]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[role]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[focus]', description: 'Filter by Ship-Matrix focus slug', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Filter by Ship-Matrix type slug', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[production_status]', description: 'Filter by Ship-Matrix production status slug', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/vehicle_link')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $versionCode = $this->gameVersionCode();
        $vehicleType = $request->route()->defaults['vehicle_type'] ?? 'vehicles';
        $allowedIncludes = $this->allowedIncludes();

        $this->normalizeIncludes($request, $allowedIncludes);

        $query = QueryBuilder::for(VehicleData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->forVehicleType($vehicleType)
            ->allowedFilters([
                AllowedFilter::exact('manufacturer', 'manufacturer.name'),
                AllowedFilter::exact('size'),
                AllowedFilter::partial('career'),
                AllowedFilter::partial('role'),
                AllowedFilter::custom('focus', new VehicleFocusFilter),
                AllowedFilter::custom('type', new VehicleTypeFilter),
                AllowedFilter::custom('production_status', new VehicleProductionStatusFilter),
            ])
            ->allowedSorts(['name', 'size', 'career', 'role'])
            ->defaultSort('name')
            ->allowedIncludes($allowedIncludes)
            ->with(['vehicle', 'gameVersion']);

        $vehicles = $query->paginate()->appends($request->query());

        return VehicleLinkResource::collection(
            $this->transformToVehicles($vehicles)
        );
    }

    #[OA\Get(
        path: '/api/vehicles/{identifier}',
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
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Vehicle',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: '#/components/schemas/game_vehicle'),
                        new OA\Schema(ref: '#/components/schemas/vehicle_v2'),
                    ]
                )
            ),
        ]
    )]
    public function show(Request $request, string $identifier): AbstractBaseResource
    {
        $versionCode = $this->gameVersionCode();
        $identifier = $this->cleanQueryName($identifier);
        $isUuid = Str::isUuid($identifier);
        $allowedIncludes = $this->allowedIncludes();

        $this->normalizeIncludes($request, $allowedIncludes);

        try {
            $vehicleData = null;

            if ($isUuid) {
                $vehicleData = QueryBuilder::for(VehicleData::class, $request)
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->whereHas('vehicle', fn (Builder $q) => $q->where('uuid', $identifier))
                    ->allowedIncludes($allowedIncludes)
                    ->with(['vehicle', 'gameVersion'])
                    ->first();
            }

            if ($vehicleData === null) {
                $underscored = str_replace(' ', '_', $identifier);
                $vehicleData = QueryBuilder::for(VehicleData::class, $request)
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->where(function (Builder $q) use ($identifier, $underscored) {
                        $q->where('name', $identifier)
                            ->orWhereRaw('upper(display_name) = ?', [strtoupper($identifier)])
                            ->orWhere('class_name', strtoupper($underscored))
                            ->orWhere('class_name', 'LIKE', "%_{$underscored}");
                    })
                    ->allowedIncludes($allowedIncludes)
                    ->with(['vehicle', 'gameVersion'])
                    ->first();
            }

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
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Vehicle with specified UUID or Name found.');
        }

        return new VehicleResource($vehicle);
    }

    #[OA\Post(
        path: '/api/vehicles/search',
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
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'integer')),
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
                    items: new OA\Items(ref: '#/components/schemas/vehicle_link')
                )
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $versionCode = $this->gameVersionCode();
        $vehicleType = $request->route()->defaults['vehicle_type'] ?? 'vehicles';
        $toSearch = $this->cleanQueryName($request->validated('query'));
        $isUuid = Str::isUuid($toSearch);
        $allowedIncludes = $this->allowedIncludes();

        $this->normalizeIncludes($request, $allowedIncludes);

        $query = QueryBuilder::for(VehicleData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->forVehicleType($vehicleType)
            ->allowedFilters([
                AllowedFilter::exact('manufacturer', 'manufacturer.name'),
                AllowedFilter::exact('size'),
                AllowedFilter::custom('focus', new VehicleFocusFilter),
                AllowedFilter::custom('type', new VehicleTypeFilter),
                AllowedFilter::custom('production_status', new VehicleProductionStatusFilter),
            ])
            ->allowedSorts(['name', 'size', 'career', 'role'])
            ->defaultSort('name')
            ->where(function (Builder $query) use ($toSearch, $isUuid) {
                $underscored = str_replace(' ', '_', $toSearch);
                $query->where('name', 'like', "%{$toSearch}%")
                    ->orWhere('class_name', 'LIKE', "%{$underscored}%")
                    ->orWhere('career', 'LIKE', "%{$toSearch}%");

                if ($isUuid) {
                    $query->orWhereHas('vehicle', fn (Builder $q) => $q->where('uuid', $toSearch));
                }
            })
            ->allowedIncludes($allowedIncludes)
            ->with(['vehicle', 'gameVersion']);

        $vehicles = $query->paginate()->appends($request->query());

        return VehicleLinkResource::collection(
            $this->transformToVehicles($vehicles)
        );
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
     * Query builder includes for vehicles.
     */
    private function allowedIncludes(): array
    {
        return [
            'manufacturer',
            'shipMatrixVehicle',
            'shipMatrixVehicle.components',
        ];
    }

    /**
     * Clean the name for query use.
     */
    private function cleanQueryName(string $name): string
    {
        return str_replace('_', ' ', urldecode($name));
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
}
