<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\FoodResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FoodController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/food',
        tags: ['In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Foods',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Item::class)
            ->where('type', 'Food')
            ->paginate($this->limit)
            ->appends(request()->query());

        return ItemLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/food/{food}',
        tags: ['In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/commodity_includes_v2'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Food Item',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/food_v2')
                )
            )
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['food' => $identifier] = Validator::validate(
            [
                'food' => $request->food,
            ],
            [
                'food' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $identifier = QueryBuilder::for(Item::class)
                ->where('type', 'Food')
                ->where(function (Builder $query) use ($identifier) {
                    $query->where('uuid', $identifier)
                        ->orWhere('name', 'LIKE', sprintf('%%%s%%', $identifier));
                })
                ->allowedIncludes(FoodResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Weapon with specified UUID or Name found.');
        }

        return new ItemResource($identifier);
    }
}
