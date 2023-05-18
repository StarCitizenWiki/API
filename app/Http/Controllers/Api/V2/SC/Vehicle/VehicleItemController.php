<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Vehicle;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Http\Resources\SC\Vehicle\VehicleItemLinkResource;
use App\Models\SC\Item\Item;
use App\Models\SC\Vehicle\VehicleItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VehicleItemController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/vehicle-items',
        tags: ['Vehicles', 'In-Game'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[grade]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Vehicle Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(VehicleItem::class, $request)
            ->allowedFilters([
                AllowedFilter::callback('type', static function (Builder $query, $value) {
                    $query->whereRelation('descriptionData', 'name', 'Item Type')
                        ->whereRelation('descriptionData', 'value', $value);
                }),
                AllowedFilter::callback('type', static function (Builder $query, $value) {
                    $query->whereRelation('descriptionData', 'name', 'Grade')
                        ->whereRelation('descriptionData', 'value', $value);
                }),
                AllowedFilter::callback('type', static function (Builder $query, $value) {
                    $query->whereRelation('descriptionData', 'name', 'Class')
                        ->whereRelation('descriptionData', 'value', $value);
                }),
            ])
            ->allowedIncludes(['shops', 'shops.items'])
            ->paginate($this->limit)
            ->appends(request()->query());

        return VehicleItemLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/vehicle-items/{item}',
        tags: ['Vehicles', 'In-Game'],
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
                description: 'A Vehicle Item',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/vehicle_weapon_v2')
                )
            )
        ]
    )]
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

        $identifier = $this->cleanQueryName($identifier);

        try {
            $identifier = QueryBuilder::for(VehicleItem::class, $request)
                ->with([
                    'powerData',
                    'distortionData',
                    'heatData',
                ])
                ->where('uuid', $identifier)
                ->orWhere('name', $identifier)
                ->orderByDesc('version')
                ->allowedIncludes(ItemResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Item with specified UUID or Name found.');
        }

        return new ItemResource($identifier);
    }
}
