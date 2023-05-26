<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizenUnpacked\ItemSearchRequest;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/items',
        tags: ['In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/commodity_includes_v2'),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Item::class, $request)
            ->where(
                'version',
                'LIKE',
                $request->get('version', config('api.sc_data_version')) . '%'
            )
            ->allowedFilters([
                'type',
                'sub_type',
                AllowedFilter::exact('manufacturer', 'manufacturer.name')
            ])
            ->allowedIncludes(ItemResource::validIncludes())
            ->paginate($this->limit)
            ->appends(request()->query());

        return ItemLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/items/{item}',
        tags: ['In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/commodity_includes_v2'),
            new OA\Parameter(
                name: 'item',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Item name or UUID',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'An Item',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_v2')
                )
            )
        ]
    )]
    public function show(Request $request)
    {
        ['item' => $identifier] = Validator::validate(
            [
                'item' => $request->item,
            ],
            [
                'item' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $item = QueryBuilder::for(Item::class, $request)
                ->where('uuid', $identifier)
                ->orWhere('name', $identifier)
                ->orderByDesc('version')
                ->with([
                    'powerData',
                    'distortionData',
                    'heatData',
                    'durabilityData',
                ])
                ->allowedIncludes(ItemResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Item with specified UUID or Name found.');
        }

        if ($item->type === 'NOITEM_Vehicle') {
            return redirect(sprintf('/api/v2/vehicles/%s', $item->uuid));
        }

        return new ItemResource($item);
    }

    #[OA\Post(
        path: '/api/v2/items/search',
        requestBody: new OA\RequestBody(
            description: 'Item Name or (sub)type',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                    ),
                    example: '{"query": "Arrow"}',
                )
            ]
        ),
        tags: ['In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/commodity_includes_v2'),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            )
        ]
    )]
    public function search(ItemSearchRequest $request): JsonResource
    {
        $request->validate([
            'query' => 'required|string|min:1|max:255',
            'shop' => 'nullable|uuid',
        ]);

        $query = $this->cleanQueryName($request->get('query'));

        $items = QueryBuilder::for(Item::class)
            ->allowedFilters([
                'type',
                'sub_type',
                AllowedFilter::exact('manufacturer', 'manufacturer.name')
            ])
            ->allowedIncludes(['shops.items']);

        if ($request->has('shop') && $request->get('shop') !== null) {
            $items->whereRelation('shops', 'uuid', $request->get('shop'));
        }

        $items
            ->where('name', 'like', "%{$query}%")
            ->orWhere('uuid', $query)
            ->orWhere('type', $query)
            ->orWhere('sub_type', $query)
            ->paginate($this->limit)
            ->appends(request()->query());

        if ($items->count() === 0) {
            throw new NotFoundHttpException(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return ItemLinkResource::collection($items);
    }
}
