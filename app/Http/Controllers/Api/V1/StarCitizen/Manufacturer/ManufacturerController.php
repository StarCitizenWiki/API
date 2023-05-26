<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Manufacturer;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Manufacturer\ManufacturerSearchRequest;
use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Transformers\Api\V1\StarCitizen\Manufacturer\ManufacturerTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ManufacturerController extends ApiController
{
    /**
     * ManufacturerController constructor.
     *
     * @param Request                 $request
     * @param ManufacturerTransformer $transformer
     */
    public function __construct(Request $request, ManufacturerTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/manufacturers',
        tags: ['Manufacturers', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'manufacturer_includes',
                    collectionFormat: 'csv',
                    enum: [
                        'vehicles',
                        'ships',
                    ]
                ),
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Manufacturers',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/manufacturer')
                )
            )
        ]
    )]
    public function index(): Response
    {
        return $this->getResponse(Manufacturer::query());
    }

    #[OA\Get(
        path: '/api/manufacturer/{code}',
        tags: ['Manufacturers', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'manufacturer_includes',
                    collectionFormat: 'csv',
                    enum: [
                        'vehicles',
                        'ships',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'manufacturer_code',
                    description: 'Manufacturer Code, e.g. RSI',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/manufacturer',
                response: 200,
                description: 'A singular manufacturer',
            ),
            new OA\Response(
                response: 404,
                description: 'No Manufacturer with specified CODE found.',
            )
        ]
    )]
    public function show(Request $request): Response
    {
        ['manufacturer' => $manufacturer] = Validator::validate(
            [
                'manufacturer' => $request->manufacturer,
            ],
            [
                'manufacturer' => 'required|string|min:1|max:255',
            ]
        );

        $manufacturer = urldecode($manufacturer);

        try {
            $model = Manufacturer::query()
                ->where('name_short', $manufacturer)
                ->orWhere('name', $manufacturer)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $manufacturer)], 404);
        }

        return $this->getResponse($model);
    }

    #[OA\Post(
        path: '/api/manufacturers/search',
        requestBody: new OA\RequestBody(
            description: 'Name',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        schema: 'query',
                        type: 'json',
                    ),
                    example: '{"query": "RSI"}',
                )
            ]
        ),
        tags: ['Manufacturers', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'manufacturer_includes',
                    collectionFormat: 'csv',
                    enum: [
                        'vehicles',
                        'ships',
                    ]
                ),
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of manufacturers matching the query',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/manufacturer')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No manufacturer found.',
            )
        ],
    )]
    public function search(Request $request): Response
    {
        $rules = (new ManufacturerSearchRequest())->rules();
        $request->validate($rules);

        $query = urldecode($request->get('query'));
        $queryBuilder = Manufacturer::query()
            ->where('name_short', 'like', "%{$query}%")
            ->orWhere('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $query)], 404);
        }

        return $this->getResponse($queryBuilder);
    }
}
