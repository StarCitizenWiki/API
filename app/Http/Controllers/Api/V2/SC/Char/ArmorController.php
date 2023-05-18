<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Char;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Char\ClothingResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Char\Clothing\Armor;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[OA\Parameter(
    parameter: 'clothing_includes_v2',
    name: 'include',
    in: 'query',
    schema: new OA\Schema(
        description: 'Available Clothing/Armor Item includes',
        type: 'array',
        items: new OA\Items(
            type: 'string',
            enum: [
                'shops',
                'shops.items',
            ]
        ),
    ),
    explode: false,
    allowReserved: true
)]
#[OA\Parameter(
    parameter: 'clothing_filter_v2',
    name: 'filter[type]',
    in: 'query',
    schema: new OA\Schema(
        description: 'Filter list based on type',
        type: 'string',
    ),
    allowReserved: true
)]
class ArmorController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/armor',
        tags: ['Armor', 'In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/clothing_filter_v2'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Armors',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            )
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Armor::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('type'),
            ])
            ->paginate($this->limit)
            ->appends(request()->query());

        return ItemLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/armor/{armor}',
        tags: ['Armor', 'In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/clothing_includes_v2'),
            new OA\Parameter(
                name: 'armor',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Armor name of UUID',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'An Armor Item',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_v2')
                )
            )
        ]
    )]
    public function show(Request $request): AbstractBaseResource
    {
        ['clothing' => $identifier] = Validator::validate(
            [
                'clothing' => $request->clothing,
            ],
            [
                'clothing' => 'required|string|min:1|max:255',
            ]
        );

        $identifier = $this->cleanQueryName($identifier);

        try {
            $identifier = QueryBuilder::for(Armor::class, $request)
                ->where('uuid', $identifier)
                ->orWhere('name', $identifier)
                ->orderByDesc('version')
                ->allowedIncludes(ClothingResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Armor with specified UUID or Name found.');
        }

        return new ItemResource($identifier);
    }
}
