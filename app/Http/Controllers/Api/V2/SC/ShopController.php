<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Models\SC\Item\Item;
use App\Models\SC\Shop\Shop;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShopController extends AbstractApiV2Controller
{

    #[OA\Get(
        path: '/api/v2/item',
        tags: ['Stats', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of stats',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/stat_v2')
                )
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Shop::class)
            ->limit($this->limit)
            ->allowedIncludes(ShopResource::validIncludes())
            ->paginate()
            ->appends(request()->query());

        return ShopResource::collection($query);
    }

    public function show($id, Request $request): AbstractBaseResource
    {
        ['shop' => $identifier] = Validator::validate(
            [
                'shop' => $request->shop,
            ],
            [
                'shop' => 'required|string|min:1|max:255',
            ]
        );

        try {
            $shop = QueryBuilder::for(Shop::class)
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
