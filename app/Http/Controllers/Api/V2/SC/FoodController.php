<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\FoodResource;
use App\Models\SC\Food\Food;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FoodController extends AbstractApiV2Controller
{

    #[OA\Get(
        path: '/api/v2/food',
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
        $query = QueryBuilder::for(Food::class)
            ->limit($this->limit)
            ->allowedIncludes(FoodResource::validIncludes())
            ->paginate()
            ->appends(request()->query());

        return FoodResource::collection($query);
    }

    public function show($id, Request $request): AbstractBaseResource
    {
        ['food' => $food] = Validator::validate(
            [
                'food' => $request->food,
            ],
            [
                'food' => 'required|string|min:1|max:255',
            ]
        );

        try {
            $food = QueryBuilder::for(Food::class)
                ->where('item_uuid', $food)
                ->orWhereRelation('item', 'name', 'LIKE', sprintf('%%%s%%', $food))
                ->allowedIncludes(FoodResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Weapon with specified UUID or Name found.');
        }

        return new FoodResource($food);
    }
}
