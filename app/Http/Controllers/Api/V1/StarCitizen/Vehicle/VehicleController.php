<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Vehicle;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Vehicle\VehicleSearchRequest;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\StarCitizenUnpacked\Vehicle as UnpackedVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\VehicleLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\VehicleTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\VehicleTransformer as UnpackedVehicleTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class VehicleController extends ApiController
{
    /**
     * ShipController constructor.
     *
     * @param VehicleTransformer $transformer
     * @param Request $request
     */
    public function __construct(VehicleTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/vehicles',
        tags: ['Vehicles', 'RSI-Website', 'In-Game'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'vehicle_includes',
                    description: 'Available Vehicle includes',
                    collectionFormat: 'csv',
                    enum: [
                        'components',
                        'hardpoints',
                        'shops',
                        'shops.items',
                    ]
                ),
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/vehicle')
                )
            )
        ]
    )]
    public function index(Request $request): Response
    {
        if ($request->has('transformer') && $request->get('transformer') === 'link') {
            $this->transformer = new VehicleLinkTransformer();
            if (!$request->has('limit')) {
                $this->limit = 100;
            }
        }

        return $this->getResponse(Vehicle::query()->orderBy('name'));
    }

    #[OA\Get(
        path: '/api/vehicles/{name}',
        tags: ['Vehicles', 'RSI-Website', 'In-Game'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'vehicle_includes',
                    description: 'Available Vehicle includes',
                    collectionFormat: 'csv',
                    enum: [
                        'components',
                        'hardpoints',
                        'shops',
                        'shops.items',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'name',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'vehicle_name',
                    description: '(Partial) Vehicle name',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/vehicle',
                response: 200,
                description: 'A singular vehicle'
            )
        ]
    )]
    public function show(Request $request): Response
    {
        ['vehicle' => $vehicle] = Validator::validate(
            [
                'vehicle' => $request->vehicle,
            ],
            [
                'vehicle' => 'required|string|min:1|max:255',
            ]
        );

        $vehicle = urldecode($vehicle);

        try {
            $vehicleModel = Vehicle::query()
                ->where('name', 'LIKE', "%{$vehicle}%")
                ->orWhere('slug', 'LIKE', "%{$vehicle}%")
                ->first();

            if ($vehicleModel === null) {
                $vehicleModel = UnpackedVehicle::query()
                    ->where('name', 'like', '%' . $vehicle . '%')
                    ->orWhere('class_name', 'like', '%' . $vehicle . '%')
                    ->firstOrFail();

                $locale = $this->transformer->getLocale();
                $this->transformer = new UnpackedVehicleTransformer();
                if ($locale !== null) {
                    $this->transformer->setLocale($locale);
                }
            }
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $vehicle)], 404);
        }

        return $this->getResponse($vehicleModel);
    }

    #[OA\Post(
        path: '/api/vehicles/search',
        requestBody: new OA\RequestBody(
            description: 'Vehicle (partial) name or slug',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        schema: 'query',
                        type: 'json',
                    ),
                    example: '{"query": "Merchant"}',
                )
            ]
        ),
        tags: ['Vehicles', 'RSI-Website', 'In-Game'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of vehicles matching the query',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/vehicle')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No vehicle found.',
            )
        ],
    )]
    public function search(Request $request): Response
    {
        $rules = (new VehicleSearchRequest())->rules();

        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = Vehicle::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $query)], 404);
        }

        return $this->getResponse($queryBuilder);
    }
}
