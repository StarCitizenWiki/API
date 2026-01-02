<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Filters\ItemVariantsFilter;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Item\ItemLinkResource;
use App\Http\Resources\Game\Item\ItemResource;
use App\Models\Game\ItemData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemController extends Controller
{
    use ResolvesGameVersion;

    #[OA\Get(
        path: '/api/items',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[variants]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $versionCode = $this->gameVersionCode();
        $category = $request->route()->defaults['category'] ?? 'items';

        $query = QueryBuilder::for(ItemData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->forCategory($category)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('sub_type'),
                AllowedFilter::exact('manufacturer', 'manufacturer.name'),
                AllowedFilter::partial('classification'),
                AllowedFilter::exact('size'),
                AllowedFilter::exact('grade'),
                AllowedFilter::custom('variants', new ItemVariantsFilter),
            ])
            ->allowedSorts(['name', 'size', 'grade', 'type', 'sub_type', 'classification'])
            ->defaultSort('name')
            ->allowedIncludes(ItemResource::validIncludes())
            ->with(['item', 'gameVersion']);

        $items = $query->paginate()->appends($request->query());

        return ItemLinkResource::collection(
            $this->transformToItems($items, $versionCode)
        );
    }

    #[OA\Get(
        path: '/api/items/{identifier}',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(
                name: 'identifier',
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
                description: 'An Item',
                content: new OA\JsonContent(ref: '#/components/schemas/item_base')
            ),
        ]
    )]
    public function show(Request $request, string $identifier): ItemResource
    {
        $versionCode = $this->gameVersionCode();
        $identifier = $this->cleanQueryName($identifier);

        try {
            $itemData = QueryBuilder::for(ItemData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->whereHas('item', fn (Builder $q) => $q->where('uuid', $identifier))
                ->allowedIncludes(ItemResource::validIncludes())
                ->with(['entityTags', 'item', 'gameVersion'])
                ->first();

            if ($itemData === null) {
                $itemData = QueryBuilder::for(ItemData::class, $request)
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->where('name', $identifier)
                    ->allowedIncludes(ItemResource::validIncludes())
                    ->with(['entityTags', 'item', 'gameVersion'])
                    ->first();
            }

            if ($itemData === null) {
                throw new ModelNotFoundException;
            }

            $item = $itemData->item;
            $item->setRelation('data', collect([$itemData]));
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Item with specified UUID or Name found.');
        }

        if ($item->data->first()?->type === 'NOITEM_Vehicle') {
            return redirect(sprintf('/api/vehicles/%s', $item->uuid));
        }

        return new ItemResource($item);
    }

    #[OA\Post(
        path: '/api/items/search',
        requestBody: new OA\RequestBody(
            description: 'Item Name or (sub)type',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(type: 'object'),
                    example: '{"query": "Arrow"}',
                ),
            ]
        ),
        tags: ['In-Game', 'Items', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[variants]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/item_link')
                )
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $versionCode = $this->gameVersionCode();
        $toSearch = $this->cleanQueryName($request->validated('query'));

        $query = QueryBuilder::for(ItemData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('sub_type'),
                AllowedFilter::exact('manufacturer', 'manufacturer.name'),
                AllowedFilter::custom('variants', new ItemVariantsFilter),
                AllowedFilter::partial('classification'),
                AllowedFilter::exact('size'),
                AllowedFilter::exact('grade'),
            ])
            ->allowedSorts(['name', 'size', 'grade', 'type', 'sub_type', 'classification'])
            ->defaultSort('name')
            ->allowedIncludes(ItemResource::validIncludes())
            ->where(function (Builder $query) use ($toSearch) {
                $query->where('name', 'like', "%{$toSearch}%")
                    ->orWhereHas('item', fn (Builder $q) => $q->where('uuid', $toSearch))
                    ->orWhere('type', $toSearch)
                    ->orWhere('sub_type', $toSearch);
            })
            ->with(['item', 'gameVersion']);

        $items = $query->paginate()->appends($request->query());

        return ItemLinkResource::collection(
            $this->transformToItems($items, $versionCode)
        );
    }

    /**
     * Clean the name for query use.
     */
    private function cleanQueryName(string $name): string
    {
        return str_replace('_', ' ', urldecode($name));
    }

    /**
     * Transform ItemData collection to Items for resources.
     *
     * ItemLinkResource expects Item models with loaded data relationship.
     * This method transforms the ItemData query results back to Item models.
     */
    private function transformToItems($itemDataCollection, ?string $versionCode): mixed
    {
        if ($itemDataCollection instanceof LengthAwarePaginator) {
            $items = $itemDataCollection->getCollection()->map(function (ItemData $itemData) {
                $item = $itemData->item;
                $item->setRelation('data', collect([$itemData]));

                return $item;
            });

            return $itemDataCollection->setCollection($items);
        }

        return $itemDataCollection->map(function (ItemData $itemData) {
            $item = $itemData->item;
            $item->setRelation('data', collect([$itemData]));

            return $item;
        });
    }
}
