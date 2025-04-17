<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V3\SC\Vehicle;

use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Vehicle\VehicleResourceV3;
use App\Http\Resources\StarCitizen\Vehicle\VehicleResource;
use App\Models\SC\Vehicle\Vehicle as UnpackedVehicle;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleController extends \App\Http\Controllers\Api\V2\SC\Vehicle\VehicleController
{
    #[OA\Get(
        path: '/api/v3/vehicles',
        tags: ['Vehicles', 'RSI-Website', 'In-Game'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[chassis_id]', in: 'query', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Vehicles',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/vehicle_link_v2')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return parent::index($request);
    }

    #[OA\Get(
        path: '/api/v3/vehicles/{name}',
        tags: ['Vehicles', 'RSI-Website', 'In-Game'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'filter[ports]',
                description: 'Filter port types, prefix with "!" to remove these ports.',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    description: 'Available Vehicle includes',
                    type: 'array',
                    items: new OA\Items(
                        type: 'string',
                        enum: [
                            'components',
                            'ports',
                            'shops',
                        ]
                    ),
                ),
                explode: false,
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'name',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: '(Partial) Vehicle name',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular vehicle',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: '#/components/schemas/sc_vehicle_v3'),
                        new OA\Schema(ref: '#/components/schemas/vehicle_v2'),
                    ],
                )
            ),
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['vehicle' => $identifier] = Validator::validate(
            [
                'vehicle' => $request->vehicle,
            ],
            [
                'vehicle' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);
        $underscored = str_replace(' ', '_', $identifier);

        try {
            $vehicleModel = QueryBuilder::for(UnpackedVehicle::class)
                ->where('name', $identifier)
                ->orWhere('class_name', $underscored)
                ->orWhere('class_name', 'LIKE', "%_$underscored")
                ->orWhere('class_name', $identifier)
                ->orWhere('item_uuid', $identifier)
                ->with([
                    'armor',
                    'flightController',
                    'quantumDrives',
                    'shields',
                    'thrusters',
                    'partsWithoutParent',
                ])
                ->first();

            if ($vehicleModel === null) {
                $vehicleModel = QueryBuilder::for(Vehicle::class, $request)
                    ->where('name', $identifier)
                    ->orWhere('slug', $identifier)
                    ->orWhereRelation('sc', 'item_uuid', $identifier)
                    ->firstOrFail();

                return new VehicleResource($vehicleModel);
            }
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Vehicle with specified name found.'.$request->vehicle);
        }

        return new VehicleResourceV3($vehicleModel);
    }

    #[OA\Post(
        path: '/api/v3/vehicles/search',
        requestBody: new OA\RequestBody(
            description: 'Vehicle (partial) name or slug',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                    ),
                    example: '{"query": "Merchant"}',
                ),
            ]
        ),
        tags: ['Vehicles', 'RSI-Website', 'In-Game', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of vehicles matching the query',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/vehicle_link_v2')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No vehicle(s) found.',
            ),
        ],
    )]
    public function search(Request $request): AnonymousResourceCollection
    {
        return parent::search($request);
    }
}
