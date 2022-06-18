<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizenUnpacked\Item;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizenUnpacked\ItemSearchRequest;
use App\Models\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Shops\Inventory;
use App\Transformers\Api\V1\StarCitizenUnpacked\ItemTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class ItemController extends ApiController
{
    /**
     * @param ItemTransformer $transformer
     * @param Request $request
     */
    public function __construct(ItemTransformer $transformer, Request $request)
    {
        $this->transformer = $transformer;

        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/items',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'item_includes',
                    description: 'Available Item includes',
                    collectionFormat: 'csv',
                    enum: [
                        'shops',
                    ]
                ),
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of In-game Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item')
                )
            )
        ]
    )]
    public function index(): Response
    {
        return $this->getResponse(Item::query()->orderBy('name'));
    }

    #[OA\Get(
        path: '/api/items/{item}',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'item_includes',
                    description: 'Available Item includes',
                    collectionFormat: 'csv',
                    enum: [
                        'shops',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'item',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'item_name_uuid',
                    description: 'Item name or UUID',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/item',
                response: 200,
                description: 'A singular Item',
            ),
            new OA\Response(
                response: 404,
                description: 'No Item with specified UUID or name found.',
            )
        ]
    )]
    public function show(Request $request): Response
    {
        ['item' => $item] = Validator::validate(
            [
                'item' => $request->item,
            ],
            [
                'item' => 'required|string|min:1|max:255',
            ]
        );

        $item = urldecode($item);

        try {
            $item = Item::query()
                ->where('name', $item)
                ->orWhere('uuid', $item)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $item));
        }

        return $this->getResponse($item);
    }

    #[OA\Post(
        path: '/api/items/search',
        requestBody: new OA\RequestBody(
            description: 'Article (partial) name, type, sub-type or uuid',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        schema: 'query',
                        type: 'json',
                    ),
                    example: '{"query": "Arrowhead"}',
                )
            ]
        ),
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'item_includes',
                    description: 'Available Item includes',
                    collectionFormat: 'csv',
                    enum: [
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
                description: 'List of items matching the query',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No item found.',
            )
        ],
    )]
    public function search(ItemSearchRequest $request): Response
    {
        $rules = (new ItemSearchRequest())->rules();
        $request->validate($rules);

        $query = urldecode($request->get('query'));

        try {
            $item = Item::query();

            if ($request->has('shop') && $request->get('shop') !== null) {
                $item
                    ->whereHas('shopsRaw', function ($query) use ($request) {
                        $query->where('shop_uuid', $request->get('shop'));
                    });
            }

            $item->where('name', 'like', "%{$query}%")
                ->orWhere('uuid', $query)
                ->orWhere('type', $query)
                ->orWhere('sub_type', $query);
        } catch (ModelNotFoundException $e) {
            $this->response->errorNotFound(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return $this->getResponse($item);
    }

    #[OA\Get(
        path: '/api/items/tradeables',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'item_includes',
                    description: 'Available Item includes',
                    collectionFormat: 'csv',
                    enum: [
                        'shops',
                    ]
                ),
                allowReserved: true
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of tradeable In-game Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item')
                )
            )
        ]
    )]
    public function indexTradeables(): Response
    {
        return $this->getResponse(
            Item::query()
                ->whereIn('type', Inventory::EXTRA_TYPES)
                ->orderBy('name')
        );
    }
}
