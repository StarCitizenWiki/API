<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Filters\ItemVariantsFilter;
use App\Http\Filters\SortByRelation;
use App\Http\Includes\CustomEagerLoadInclude;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Item\ItemResource;
use App\Models\Game\ItemData;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ItemController extends Controller
{
    use FiltersJsonColumns;
    use ResolvesGameVersion;

    protected function getJsonTableName(): string
    {
        return 'game_item_data';
    }

    protected function getJsonColumnName(): string
    {
        return 'data';
    }

    /**
     * Get allowed includes with custom handlers.
     */
    private function allowedIncludes(): array
    {
        return array_merge(
            ItemResource::validIncludes(),
            [
                AllowedInclude::custom('shops', new CustomEagerLoadInclude),
                AllowedInclude::custom('shops.items', new CustomEagerLoadInclude),
                AllowedInclude::custom('variants', new CustomEagerLoadInclude([
                    'variants.item', 'variants.manufacturer', 'variants.gameVersion', 'variants.baseVariant', 'variants.variantGroupItem',
                ])),
                AllowedInclude::custom('related_items', new CustomEagerLoadInclude([
                    'variantGroupItem.variantGroup.items.itemData.item',
                    'setItems.item',
                    'variants.item', 'variants.manufacturer', 'variants.gameVersion', 'variants.baseVariant', 'variants.variantGroupItem',
                    'baseVariant.item',
                ])),
                AllowedInclude::custom('blueprints', new CustomEagerLoadInclude),
            ]
        );
    }

    /**
     * Build base query with filters, sorts, and includes for items.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        $versionCode = $this->gameVersionCode();
        $category = $request->route()->defaults['category'] ?? 'items';

        $withRelations = ['item', 'gameVersion', 'manufacturer', 'descriptionData'];

        return QueryBuilder::for(ItemData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->forCategory($category)
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(...array_merge(
                [
                    'name',
                    'class_name',
                    'class',
                    'size',
                    'grade',
                    'type',
                    'sub_type',
                    'classification',
                    AllowedSort::custom('manufacturer', new SortByRelation, 'manufacturer.name'),
                    AllowedSort::custom('manufacturer.name', new SortByRelation, 'manufacturer.name'),
                ],
                $this->allowedJsonSorts()
            ))
            ->defaultSort('name')
            ->allowedIncludes(...$this->allowedIncludes())
            ->with($withRelations);
    }

    /**
     * Get JSON-backed sort fields from configuration.
     *
     * @return array<AllowedSort>
     */
    private function allowedJsonSorts(): array
    {
        $sortConfig = config('sorts.items', []);
        $allowedSorts = [];

        foreach ($sortConfig as $sortKey => $config) {
            $allowedSorts[] = $this->jsonSort(
                $config['path'], // $sortKey,
                'stdItem.'.$config['path'],
                $config['cast'] ?? 'numeric'
            );
        }

        return $allowedSorts;
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        $manufacturerFilter = static function (Builder $query, mixed $value): void {
            $values = is_array($value) ? $value : [$value];

            $query->whereHas('manufacturer', static function ($manufacturerQuery) use ($values): void {
                $manufacturerQuery
                    ->whereIn('name', $values)
                    ->orWhereIn('code', $values);
            });
        };

        return [
            AllowedFilter::scope('category'),
            AllowedFilter::exact('type'),
            AllowedFilter::exact('sub_type'),
            AllowedFilter::callback('manufacturer', $manufacturerFilter),
            AllowedFilter::callback('manufacturer.name', $manufacturerFilter),
            AllowedFilter::partial('class_name'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('classification'),
            AllowedFilter::exact('size'),
            AllowedFilter::exact('grade'),
            AllowedFilter::exact('class'),
            AllowedFilter::custom('variants', new ItemVariantsFilter),
        ];
    }

    #[OA\Get(
        path: '/api/weapons',
        description: 'Alias for /api/items scoped to weapon items (FPS weapons).',
        summary: 'In-Game Weapons Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Weapons', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/weapon-attachments',
        description: 'Alias for /api/items scoped to weapon attachment items.',
        summary: 'In-Game Weapon Attachments Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Weapon Attachments', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/clothes',
        description: 'Alias for /api/items scoped to clothing items.',
        summary: 'In-Game Clothes Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Clothes', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/armor',
        description: 'Alias for /api/items scoped to armor items.',
        summary: 'In-Game Armor Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Armor', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/food',
        description: 'Alias for /api/items scoped to food items.',
        summary: 'In-Game Food Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Food Items', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-weapons',
        description: 'Alias for /api/items scoped to vehicle weapon items.',
        summary: 'In-Game Vehicle Weapons Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Vehicle Weapons', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-items',
        description: 'Alias for /api/items scoped to vehicle component items.',
        summary: 'In-Game Vehicle Items Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Vehicle Items', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
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
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supports 250+ JSON fields. Examples: name, -grade, weapon.damage.alpha_total, -shield_controller.face_type. Use comma for multiple: grade,-name',
                in: 'query',
                schema: new OA\Schema(
                    type: 'string',
                    example: '-weapon.damage.alpha_total'
                )
            ),
            new OA\Parameter(name: 'filter[variants]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[category]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
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
    /**
     * Get paginated list of items with optional sorting.
     *
     * Common sort examples:
     * - Basic: ?sort=name, ?sort=-grade, ?sort=size
     * - Manufacturer: ?sort=manufacturer.name
     * - Weapons: ?sort=-weapon.damage.alpha_total, ?sort=weapon.rate_of_fire
     * - Shields: ?sort=-shield.max_health, ?sort=shield_controller.face_type
     * - Mining: ?sort=mining_laser.power_transfer, ?sort=-mining_module.charges
     * - Power: ?sort=-resource_network.usage.power.maximum
     * - Multiple: ?sort=grade,-weapon.damage.alpha_total
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request);
        $items = $query->jsonPaginate();

        ItemData::loadCraftingBlueprints($items->getCollection());

        return ItemResource::collection($items);
    }

    #[OA\Get(
        path: '/api/weapons/{identifier}',
        description: 'Alias for /api/items/{identifier} scoped to weapons.',
        summary: 'In-Game Weapon Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Weapon', content: new OA\JsonContent(ref: '#/components/schemas/game_item')),
        ]
    )]
    #[OA\Get(
        path: '/api/weapon-attachments/{identifier}',
        description: 'Alias for /api/items/{identifier} scoped to weapon attachments.',
        summary: 'In-Game Weapon Attachment Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Weapon Attachment', content: new OA\JsonContent(ref: '#/components/schemas/game_item')),
        ]
    )]
    #[OA\Get(
        path: '/api/clothes/{identifier}',
        description: 'Alias for /api/items/{identifier} scoped to clothing.',
        summary: 'In-Game Clothing Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Clothing Item', content: new OA\JsonContent(ref: '#/components/schemas/game_item')),
        ]
    )]
    #[OA\Get(
        path: '/api/armor/{identifier}',
        description: 'Alias for /api/items/{identifier} scoped to armor.',
        summary: 'In-Game Armor Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'An Armor Item', content: new OA\JsonContent(ref: '#/components/schemas/game_item')),
        ]
    )]
    #[OA\Get(
        path: '/api/food/{identifier}',
        description: 'Alias for /api/items/{identifier} scoped to food.',
        summary: 'In-Game Food Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Food Item', content: new OA\JsonContent(ref: '#/components/schemas/game_item')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-weapons/{identifier}',
        description: 'Alias for /api/items/{identifier} scoped to vehicle weapons.',
        summary: 'In-Game Vehicle Weapon Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Vehicle Weapon', content: new OA\JsonContent(ref: '#/components/schemas/game_item')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-items/{identifier}',
        description: 'Alias for /api/items/{identifier} scoped to vehicle items.',
        summary: 'In-Game Vehicle Item Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Vehicle Item', content: new OA\JsonContent(ref: '#/components/schemas/game_item')),
        ]
    )]
    #[OA\Get(
        path: '/api/items/{identifier}',
        description: 'Retrieve a specific item by name or UUID with metadata and includes.',
        summary: 'In-Game Item Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
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
            $baseQuery = fn () => QueryBuilder::for(ItemData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->allowedIncludes(...$this->allowedIncludes())
                ->with(['entityTags', 'item', 'gameVersion', 'variantGroupItem', 'baseVariant.item', 'baseVariant.manufacturer', 'baseVariant.gameVersion', 'manufacturer', 'descriptionData', 'commodities']);

            $itemData = null;

            if ($isUuid) {
                $itemData = $baseQuery()
                    ->whereHas('item', fn (Builder $itemQuery) => $itemQuery->where('uuid', $identifier))
                    ->first();
            }

            if ($itemData === null) {
                $itemData = $baseQuery()
                    ->where(function (Builder $q) use ($identifier, $original) {
                        $underscored = str_replace(' ', '_', $identifier);
                        $q->where('name', $identifier)
                            ->orWhereRaw('upper(name) = ?', [strtoupper($identifier)])
                            ->orWhere('class_name', $underscored)
                            ->orWhereRaw('upper(class_name) = ?', [strtoupper($original)])
                            ->orWhere('class_name', 'LIKE', "%_{$underscored}");
                    })
                    ->first();
            }

            if ($itemData === null) {
                throw new ModelNotFoundException;
            }

            $includeBlueprint = collect(explode(',', (string) $request->input('include', '')))
                ->map(fn (string $value): string => trim($value))
                ->contains('blueprints');

            if ($includeBlueprint) {
                ItemData::loadCraftingBlueprints(new Collection([$itemData]), true);
            } else {
                ItemData::loadCraftingBlueprints(new Collection([$itemData]));
            }
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Item with specified UUID or Name found.');
        }

        if ($itemData->type === 'NOITEM_Vehicle') {
            return redirect(sprintf('/api/vehicles/%s', $itemData->item->uuid));
        }

        return new ItemResource($itemData);
    }

    #[OA\Post(
        path: '/api/items/search',
        description: 'Deprecated. Use GET /api/items?filter[name]={value} for name search. Note: OR search across name/uuid/type is no longer supported. This endpoint will be removed in a future version.',
        summary: 'In-Game Item Search (Deprecated)',
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
            new OA\Parameter(name: 'filter[category]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
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
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $toSearch = $request->validated('query');
        $isUuid = Str::isUuid($toSearch);
        $normalizedSearch = mb_strtolower($toSearch);

        $query = $this->buildBaseQuery($request)
            ->where(function (Builder $query) use ($toSearch, $isUuid, $normalizedSearch) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$normalizedSearch}%"])
                    ->orWhereRaw('LOWER(type) = ?', [$normalizedSearch])
                    ->orWhereRaw('LOWER(sub_type) = ?', [$normalizedSearch]);

                if ($isUuid) {
                    $query->orWhereHas('item', fn (Builder $q) => $q->where('uuid', $toSearch));
                }
            });

        $items = $query->jsonPaginate();

        ItemData::loadCraftingBlueprints($items->getCollection());

        return ItemResource::collection($items)
            ->additional([
                'meta' => ['deprecated' => true],
            ])->response()->header('Deprecated', 'true');
    }

    #[OA\Get(
        path: '/api/items/filters',
        description: 'Return all available filter values for in-game items, grouped by field.',
        summary: 'In-Game Item Filters',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[variants]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[category]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[class]', in: 'query', schema: new OA\Schema(type: 'string')),
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
        $category = $request->input('filter.category');

        if (! is_string($category) || $category === '') {
            $category = $request->route()->defaults['category'] ?? 'items';
        } else {
            $category = trim($category);
        }

        $resolver = function () use ($request, $versionCode, $category): array {
            $baseQuery = QueryBuilder::for(ItemData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->forCategory($category)
                ->allowedFilters(...$this->allowedFilters());

            $facets = [
                'type' => [
                    'expr' => 'game_item_data.type',
                    'cast' => null,
                ],
                'sub_type' => [
                    'expr' => 'game_item_data.sub_type',
                    'cast' => null,
                ],
                'classification' => [
                    'expr' => 'game_item_data.classification',
                    'cast' => null,
                ],
                'size' => [
                    'expr' => 'game_item_data.size',
                    'cast' => static fn ($value) => $value === null ? null : (int) $value,
                ],
                'grade' => [
                    'expr' => 'game_item_data.grade',
                    'cast' => static fn ($value) => $value === null ? null : (int) $value,
                    'labelResolver' => static fn ($value, $Lbl) => match ($value) {
                        1 => 'A',
                        2 => 'B',
                        3 => 'C',
                        4 => 'D',
                        5 => 'E',
                        6 => 'F',
                        7 => 'G',
                        default => null,
                    },
                ],
                'class' => [
                    'expr' => 'game_item_data.class',
                    'cast' => null,
                ],
                'manufacturer' => [
                    'expr' => 'game_manufacturers.name',
                    'join' => static fn ($q) => $q->leftJoin('game_manufacturers', 'game_item_data.manufacturer_id', '=', 'game_manufacturers.id'),
                    'cast' => null,
                ],
            ];

            $out = [];

            foreach ($facets as $key => $facet) {
                $expr = $facet['expr'];

                $q = clone $baseQuery;

                if (isset($facet['join'])) {
                    ($facet['join'])($q);
                }

                $rows = $q
                    ->select([
                        DB::raw("{$expr} as value"),
                        DB::raw('count(*) as count'),
                    ])
                    ->groupByRaw($expr)
                    ->orderByRaw("{$expr} IS NULL, {$expr}")
                    ->get();

                $out[$key] = FilterValues::fromRows($rows, $facet['cast'] ?? null, $facet['labelResolver'] ?? null);
            }

            return $out;
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []), ['category'])) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                FilterCache::NAMESPACE_ITEMS,
                FilterCache::itemsKey($versionCode, $category),
                $resolver
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
    }
}
