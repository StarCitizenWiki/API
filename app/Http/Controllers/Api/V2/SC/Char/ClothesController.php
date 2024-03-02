<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\SC\Char;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Filters\ItemVariantsFilter;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\SC\Char\ClothingResource;
use App\Http\Resources\SC\Item\ItemLinkResource;
use App\Http\Resources\SC\Item\ItemResource;
use App\Models\SC\Char\Clothing\Clothes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClothesController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/clothes',
        tags: ['Clothing', 'In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(ref: '#/components/parameters/clothing_filter_v2'),
            new OA\Parameter(ref: '#/components/parameters/variant_includes_v2'),
            new OA\Parameter(name: 'filter[variants]', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Clothing',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link_v2')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Clothes::class, $request)
            ->where('type', 'LIKE', 'Char_Clothing%')
            ->allowedFilters([
                AllowedFilter::partial('type'),
                AllowedFilter::custom('variants', new ItemVariantsFilter()),
            ])
            ->paginate($this->limit)
            ->appends(request()->query());

        return ItemLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/clothes/{clothing}',
        tags: ['Clothing', 'In-Game', 'Item'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/clothing_includes_v2'),
            new OA\Parameter(ref: '#/components/parameters/variant_includes_v2'),
            new OA\Parameter(
                name: 'clothing',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Clothing name or UUID',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Clothing Item',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_v2')
                )
            ),
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
            $identifier = QueryBuilder::for(Clothes::class, $request)
                ->where('type', 'LIKE', 'Char_Clothing%')
                ->where('uuid', $identifier)
                ->orWhere('name', $identifier)
                ->orderByDesc('version')
                ->allowedIncludes(ClothingResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Clothing with specified UUID or Name found.');
        }

        return new ItemResource($identifier);
    }
}
