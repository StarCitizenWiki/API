<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Shop;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizenUnpacked\Shop\Shop;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ShopController extends ApiController
{
    /**
     * @param ShopTransformer $transformer
     * @param Request $request
     */
    public function __construct(ShopTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/shops',
        tags: ['In-Game', 'Shops'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'shop_includes',
                    description: 'Available Shop includes',
                    collectionFormat: 'csv',
                    enum: [
                        'items',
                    ]
                ),
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Shops',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/shop')
                )
            )
        ]
    )]
    public function index(): Response
    {
        return $this->getResponse(Shop::query()
            ->where('version', config(self::SC_DATA_KEY)));
    }

    #[OA\Get(
        path: '/api/shops/{shop}',
        tags: ['In-Game', 'Shops'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'shop_includes',
                    description: 'Available Shop includes',
                    collectionFormat: 'csv',
                    enum: [
                        'items',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'shop',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'shop_name',
                    description: 'Shop name or position',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/shop',
                response: 200,
                description: 'A singular shop',
            ),
            new OA\Response(
                response: 404,
                description: 'No shop with specified name found.',
            )
        ]
    )]
    public function show(Request $request): Response
    {
        ['shop' => $shop] = Validator::validate(
            [
                'shop' => $request->shop,
            ],
            [
                'shop' => 'required|string|min:1|max:255',
            ]
        );

        $shop = $this->cleanQueryName($shop);

        try {
            $shop = Shop::query()
                ->where('name_raw', 'LIKE', sprintf('%%%s%%%%', $shop))
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $shop)], 404);
        }

        return $this->getResponse($shop);
    }

    #[OA\Get(
        path: '/api/shops/position/{position}',
        tags: ['In-Game', 'Shops'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'shop_includes',
                    description: 'Available Shop includes',
                    collectionFormat: 'csv',
                    enum: [
                        'items',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'position',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'shop_position',
                    description: 'Shop position',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Shops in that position',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/shop')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No shop with specified position found.',
            )
        ]
    )]
    public function showPosition(Request $request): Response
    {
        ['position' => $position] = Validator::validate(
            [
                'position' => $request->position,
            ],
            [
                'position' => 'required|string|min:1|max:255',
            ]
        );

        $position = $this->cleanQueryName($position);
        $positions = Shop::query()
            ->where('position', 'LIKE', sprintf('%%%s%%%%', $position))
            ->get();

        if ($positions->isEmpty()) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $position)], 404);
        }

        return $this->getResponse($positions);
    }

    #[OA\Get(
        path: '/api/shops/name/{name}',
        tags: ['In-Game', 'Shops'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'shop_includes',
                    description: 'Available Shop includes',
                    collectionFormat: 'csv',
                    enum: [
                        'items',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'position',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'name',
                    description: 'Shop Name',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/shop',
                response: 200,
                description: 'Shop matching that name'
            ),
            new OA\Response(
                response: 404,
                description: 'No shop with specified name found.',
            )
        ]
    )]
    public function showName(Request $request): Response
    {
        ['name' => $name] = Validator::validate(
            [
                'name' => $request->name,
            ],
            [
                'name' => 'required|string|min:1|max:255',
            ]
        );

        $name = $this->cleanQueryName($name);
        $positions = Shop::query()
            ->where('name', 'LIKE', sprintf('%%%s%%%%', $name))
            ->get();

        if ($positions->isEmpty()) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $name)], 404);
        }

        return $this->getResponse($positions);
    }

    #[OA\Get(
        path: '/api/shops/{position}/{name}',
        tags: ['In-Game', 'Shops'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'shop_includes',
                    description: 'Available Shop includes',
                    collectionFormat: 'csv',
                    enum: [
                        'items',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'position',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'name',
                    description: 'Shop Name',
                    type: 'string',
                ),
            ),
            new OA\Parameter(
                name: 'name',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'name',
                    description: 'Shop Name',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/shop',
                response: 200,
                description: 'Shop matching that name'
            ),
            new OA\Response(
                response: 404,
                description: 'No shop with specified name found.',
            )
        ]
    )]
    public function showShopAtPosition(Request $request): Response
    {
        ['position' => $position, 'name' => $name] = Validator::validate(
            [
                'position' => $request->position,
                'name' => $request->name,
            ],
            [
                'position' => 'required|string|min:1|max:255',
                'name' => 'required|string|min:1|max:255',
            ]
        );

        $position = $this->cleanQueryName($position);
        $name = $this->cleanQueryName($name);

        try {
            $shop = Shop::query()
                ->where('position', $position)
                ->where('name', $name)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $position)], 404);
        }

        return $this->getResponse($shop);
    }
}
