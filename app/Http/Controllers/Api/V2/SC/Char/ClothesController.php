<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Char;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Char\ClothingResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Item\Item;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClothesController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/personal-clothings',
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
        $query = QueryBuilder::for(Item::class)
            ->where('type', 'LIKE', 'Char_Clothing%')
            ->limit($this->limit)
            ->allowedIncludes(ClothingResource::validIncludes())
            ->allowedFilters(['type'])
            ->paginate()
            ->appends(request()->query());

        return ItemResource::collection($query);
    }

    public function show(Request $request): AbstractBaseResource
    {
        ['clothing' => $clothing] = Validator::validate(
            [
                'clothing' => $request->clothing,
            ],
            [
                'clothing' => 'required|string|min:1|max:255',
            ]
        );

        try {
            $clothing = QueryBuilder::for(Item::class)
                ->where('type', 'LIKE', 'Char_Clothing%')
                ->where(function (Builder $query) use ($clothing) {
                    $query->where('uuid', $clothing)
                        ->orWhere('name', 'LIKE', sprintf('%%%s%%', $clothing));
                })
                ->allowedIncludes(ClothingResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Clothing with specified UUID or Name found.');
        }

        return new ItemResource($clothing);
    }
}
