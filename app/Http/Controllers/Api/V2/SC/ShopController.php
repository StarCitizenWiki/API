<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Shop\ShopLinkResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Models\SC\Shop\Shop;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShopController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/shops',
        tags: ['In-Game', 'Shops'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Shops',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/shop_link_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Shop::class, $request)
            ->withCount('items')
            ->paginate($this->limit)
            ->appends(request()->query());

        return ShopLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/shops/{shop}',
        tags: ['In-Game', 'Shops'],
        parameters: [
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    description: 'Available Commodity Item includes',
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
                    description: 'Shop name or UUID',
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
                    items: new OA\Items(ref: '#/components/schemas/shop_v2')
                )
            )
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['shop' => $identifier] = Validator::validate(
            [
                'shop' => $request->shop,
            ],
            [
                'shop' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $shop = QueryBuilder::for(Shop::class, $request)
                ->where('uuid', $identifier)
                ->orWhere('name', 'LIKE', sprintf('%%%s%%', $identifier))
                ->orWhere('name_raw', 'LIKE', sprintf('%%%s%%', $identifier))
                ->allowedIncludes(ShopResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Item with specified UUID or Name found.');
        }

        return new ShopResource($shop);
    }
}
