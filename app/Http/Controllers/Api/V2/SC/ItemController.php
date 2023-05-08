<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizenUnpacked\ItemSearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Item\Item;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemController extends AbstractApiV2Controller
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
        $query = QueryBuilder::for(Item::class)
            ->limit($this->limit)
            ->allowedIncludes(ItemResource::validIncludes())
            ->allowedFilters(['type', 'sub_type'])
            ->paginate()
            ->appends(request()->query());

        return ItemResource::collection($query);
    }

    public function show(Request $request): AbstractBaseResource
    {
        ['item' => $identifier] = Validator::validate(
            [
                'item' => $request->item,
            ],
            [
                'item' => 'required|string|min:1|max:255',
            ]
        );

        try {
            $item = QueryBuilder::for(Item::class)
                ->where('uuid', $identifier)
                ->orWhere('name', 'LIKE', sprintf('%%%s%%', $identifier))
                ->allowedIncludes(ItemResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Item with specified UUID or Name found.');
        }

        return new ItemResource($item);
    }

    public function search(ItemSearchRequest $request): JsonResource
    {
        $rules = (new ItemSearchRequest())->rules();
        $request->validate($rules);

        $query = $this->cleanQueryName($request->get('query'));

        try {
            $item = Item::query();

            $item->where('name', 'like', "%{$query}%")
                ->orWhere('uuid', $query)
                ->orWhere('type', $query)
                ->orWhere('sub_type', $query);
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return ItemResource::collection($item);
    }
}
