<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Filters\ItemVariantsFilter;
use App\Http\Includes\PassthroughInclude;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Item\ItemResource;
use App\Models\Game\ItemData;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemController extends Controller
{
    use ResolvesGameVersion;

    /**
     * Get allowed includes with custom handlers.
     *
     * 'related_items' is handled as a custom include because it's computed
     * in ItemResource rather than being an Eloquent relationship.
     */
    private function allowedIncludes(bool $includeRelatedItems = false): array
    {
        $includes = ItemResource::validIncludes();

        if ($includeRelatedItems) {
            $includes[] = AllowedInclude::custom('related_items', new PassthroughInclude);
        }

        return $includes;
    }

    #[OA\Get(
        path: '/api/items',
        description: 'Returns paginated in-game items for the requested category and version with optional filters/includes.',
        summary: 'In-Game Item Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[variants]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[class]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/game_item')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $versionCode = $this->gameVersionCode();
        $category = $request->route()->defaults['category'] ?? 'items';

        $include = str_replace('related_items', '', $request->input('include', ''));
        if (! empty($include)) {
            $request->merge(['include' => $include]);
        }

        $query = QueryBuilder::for(ItemData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->forCategory($category)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('sub_type'),
                AllowedFilter::callback('manufacturer', static function ($query, mixed $value): void {
                    $values = is_array($value) ? $value : [$value];

                    $query->whereHas('manufacturer', static function ($manufacturerQuery) use ($values): void {
                        $manufacturerQuery
                            ->whereIn('name', $values)
                            ->orWhereIn('code', $values);
                    });
                }),
                AllowedFilter::partial('classification'),
                AllowedFilter::exact('size'),
                AllowedFilter::exact('grade'),
                AllowedFilter::exact('class'),
                AllowedFilter::custom('variants', new ItemVariantsFilter),
            ])
            ->allowedSorts(['name', 'size', 'grade', 'type', 'sub_type', 'classification'])
            ->defaultSort('name')
            ->allowedIncludes($this->allowedIncludes())
            ->with(['item', 'gameVersion']);

        $items = $query->jsonPaginate();

        return ItemResource::collection(
            $this->transformToItems($items, $versionCode)
        );
    }

    #[OA\Get(
        path: '/api/items/{identifier}',
        description: 'Retrieve a specific item by name or UUID with metadata and includes.',
        summary: 'In-Game Item Detail',
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
                content: new OA\JsonContent(ref: '#/components/schemas/game_item')
            ),
        ]
    )]
    public function show(Request $request, string $identifier): ItemResource|RedirectResponse
    {
        $original = $identifier;
        $versionCode = $this->gameVersionCode();
        $identifier = $this->cleanQueryName($identifier);
        $isUuid = Str::isUuid($identifier);

        try {
            $itemData = null;

            if ($isUuid) {
                $itemData = QueryBuilder::for(ItemData::class, $request)
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->whereHas('item', fn (Builder $q) => $q->where('uuid', $identifier))
                    ->allowedIncludes($this->allowedIncludes(includeRelatedItems: true))
                    ->with(['entityTags', 'item', 'gameVersion'])
                    ->first();
            }

            if ($itemData === null) {
                $underscored = str_replace(' ', '_', $identifier);
                $itemData = QueryBuilder::for(ItemData::class, $request)
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->where(function (Builder $q) use ($identifier, $underscored, $original) {
                        $q->where('name', $identifier)
                            ->orWhereRaw('upper(name) = ?', [strtoupper($identifier)])
                            ->orWhere('class_name', $underscored)
                            ->orWhereRaw('upper(class_name) = ?', [strtoupper($original)])
                            ->orWhere('class_name', 'LIKE', "%_{$underscored}");
                    })
                    ->allowedIncludes($this->allowedIncludes(includeRelatedItems: true))
                    ->with(['entityTags', 'item', 'gameVersion', 'baseVariant'])
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
        description: 'Search items by title, manufacturer, or classification with optional filters.',
        summary: 'In-Game Item Search',
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
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
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
            new OA\Parameter(name: 'filter[class]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Items',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/game_item')
                )
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $versionCode = $this->gameVersionCode();
        $toSearch = $request->validated('query');

        $query = QueryBuilder::for(ItemData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->allowedFilters([
                AllowedFilter::exact('type'),
                AllowedFilter::exact('sub_type'),
                AllowedFilter::callback('manufacturer', static function ($query, mixed $value): void {
                    $values = is_array($value) ? $value : [$value];

                    $query->whereHas('manufacturer', static function ($manufacturerQuery) use ($values): void {
                        $manufacturerQuery
                            ->whereIn('name', $values)
                            ->orWhereIn('code', $values);
                    });
                }),
                AllowedFilter::custom('variants', new ItemVariantsFilter),
                AllowedFilter::partial('classification'),
                AllowedFilter::exact('size'),
                AllowedFilter::exact('grade'),
                AllowedFilter::exact('class'),
            ])
            ->allowedSorts(['name', 'size', 'grade', 'type', 'sub_type', 'classification'])
            ->defaultSort('name')
            ->allowedIncludes($this->allowedIncludes())
            ->where(function (Builder $query) use ($toSearch) {
                $query->where('name', 'like', "%{$toSearch}%")
                    ->orWhereHas('item', fn (Builder $q) => $q->where('uuid', $toSearch))
                    ->orWhere('type', $toSearch)
                    ->orWhere('sub_type', $toSearch);
            })
            ->with(['item', 'gameVersion']);

        $items = $query->jsonPaginate();

        return ItemResource::collection(
            $this->transformToItems($items, $versionCode)
        );
    }

    #[OA\Get(
        path: '/api/items/filters',
        description: 'Return all available filter values for in-game items, grouped by field.',
        summary: 'In-Game Item Filters',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'version', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for in-game items.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'type', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'sub_type', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'classification', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'size', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'grade', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'class', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'manufacturer', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function filters(Request $request): JsonResponse
    {
        $versionCode = $this->gameVersionCode();
        $category = $request->route()->defaults['category'] ?? 'items';

        $filters = FilterCache::rememberForever(
            FilterCache::NAMESPACE_ITEMS,
            FilterCache::itemsKey($versionCode, $category),
            static function () use ($versionCode, $category): array {
                $baseQuery = ItemData::query()
                    ->forRequestedOrDefaultVersion($versionCode)
                    ->forCategory($category);

                $typeRows = (clone $baseQuery)
                    ->selectRaw('game_item_data.type as value, count(*) as count')
                    ->groupBy('game_item_data.type')
                    ->orderByRaw('game_item_data.type IS NULL, game_item_data.type')
                    ->get();

                $subTypeRows = (clone $baseQuery)
                    ->selectRaw('game_item_data.sub_type as value, count(*) as count')
                    ->groupBy('game_item_data.sub_type')
                    ->orderByRaw('game_item_data.sub_type IS NULL, game_item_data.sub_type')
                    ->get();

                $classificationRows = (clone $baseQuery)
                    ->selectRaw('game_item_data.classification as value, count(*) as count')
                    ->groupBy('game_item_data.classification')
                    ->orderByRaw('game_item_data.classification IS NULL, game_item_data.classification')
                    ->get();

                $sizeRows = (clone $baseQuery)
                    ->selectRaw('game_item_data.size as value, count(*) as count')
                    ->groupBy('game_item_data.size')
                    ->orderByRaw('game_item_data.size IS NULL, game_item_data.size')
                    ->get();

                $gradeRows = (clone $baseQuery)
                    ->selectRaw('game_item_data.grade as value, count(*) as count')
                    ->groupBy('game_item_data.grade')
                    ->orderByRaw('game_item_data.grade IS NULL, game_item_data.grade')
                    ->get();

                $classRows = (clone $baseQuery)
                    ->selectRaw('game_item_data.class as value, count(*) as count')
                    ->groupBy('game_item_data.class')
                    ->orderByRaw('game_item_data.class IS NULL, game_item_data.class')
                    ->get();

                $manufacturerRows = (clone $baseQuery)
                    ->leftJoin('game_manufacturers', 'game_item_data.manufacturer_id', '=', 'game_manufacturers.id')
                    ->selectRaw('game_manufacturers.name as value, count(*) as count')
                    ->groupBy('game_manufacturers.name')
                    ->orderByRaw('game_manufacturers.name IS NULL, game_manufacturers.name')
                    ->get();

                return [
                    'type' => FilterValues::fromRows($typeRows),
                    'sub_type' => FilterValues::fromRows($subTypeRows),
                    'classification' => FilterValues::fromRows($classificationRows),
                    'size' => FilterValues::fromRows($sizeRows, static fn ($value) => $value === null ? null : (int) $value),
                    'grade' => FilterValues::fromRows($gradeRows, static fn ($value) => $value === null ? null : (int) $value),
                    'class' => FilterValues::fromRows($classRows),
                    'manufacturer' => FilterValues::fromRows($manufacturerRows),
                ];
            }
        );

        return response()->json([
            'filters' => $filters,
        ]);
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
