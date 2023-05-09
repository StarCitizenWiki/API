<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\StarCitizenUnpacked\ItemSearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\ManufacturerResource;
use App\Http\Resources\SC\Shop\ShopResource;
use App\Models\SC\Item\Item;
use App\Models\SC\Manufacturer;
use App\Models\SC\Shop\Shop;
use Dingo\Api\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ManufacturerController extends AbstractApiV2Controller
{

    #[OA\Get(
        path: '/api/v2/manufacturers',
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
        $query = QueryBuilder::for(Manufacturer::class)
            ->limit($this->limit)
            ->allowedIncludes(ManufacturerResource::validIncludes())
            ->paginate()
            ->appends(request()->query());

        return ManufacturerResource::collection($query);
    }

    public function show(Request $request): AbstractBaseResource
    {
        ['manufacturer' => $identifier] = Validator::validate(
            [
                'manufacturer' => $request->manufacturer,
            ],
            [
                'manufacturer' => 'required|string|min:1|max:255',
            ]
        );

        try {
            $shop = QueryBuilder::for(Manufacturer::class)
                ->where('uuid', $identifier)
                ->orWhere('name', 'LIKE', sprintf('%%%s%%', $identifier))
                ->orWhere('code', 'LIKE', sprintf('%%%s%%', $identifier))
                //->allowedIncludes(ManufacturerResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Manufacturer with specified UUID or Name found.');
        }

        $resource = new ManufacturerResource($shop);
        $resource->setIsShow(true);

        return $resource;
    }

    public function search(ItemSearchRequest $request): JsonResource
    {
        $rules = (new ItemSearchRequest())->rules();
        $request->validate($rules);

        $query = $this->cleanQueryName($request->get('query'));

        try {
            $item = Manufacturer::query();

            $item->where('name', 'like', "%{$query}%")
                ->orWhere('uuid', $query)
                ->orWhere('name', 'LIKE', sprintf('%%%s%%', $query))
                ->orWhere('code', 'LIKE', sprintf('%%%s%%', $query));
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException(sprintf(static::NOT_FOUND_STRING, $query));
        }

        return ManufacturerResource::collection($item);
    }
}
