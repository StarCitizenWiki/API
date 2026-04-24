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
use App\Support\Filters\ItemClassificationLabel;
use App\Support\Filters\ItemTypeLabel;
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
        description: 'Alias for /api/items scoped to FPS weapons (WeaponPersonal type). Returns weapon items with manufacturer, game version, and description data.',
        summary: 'In-Game Weapons Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'KnightBridge Arms')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on item class name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'MGA_Assault')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Arrow')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0–12).', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
            new OA\Parameter(name: 'filter[grade]', description: 'Exact item grade (1–7, mapped to A–G).', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Weapons', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/weapon-attachments',
        description: 'Alias for /api/items scoped to weapon attachments (WeaponAttachment type, excluding magazines and missiles). Returns attachment items with manufacturer, game version, and description data.',
        summary: 'In-Game Weapon Attachments Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Clark Defense Systems')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Iron Sight')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0–12).', in: 'query', schema: new OA\Schema(type: 'number', example: 1)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Weapon Attachments', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/clothes',
        description: 'Alias for /api/items scoped to clothing (FPS.Clothing.* classification). Returns clothing items with manufacturer, game version, and description data.',
        summary: 'In-Game Clothes Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Stegman\'s')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Jacket')),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Clothing). (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'FPS.Clothing.Torso')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Clothes', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/armor',
        description: 'Alias for /api/items scoped to armor (FPS.Armor.* classification). Returns armor items with manufacturer, game version, and description data.',
        summary: 'In-Game Armor Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Clark Defense Systems')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Core')),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Armor). (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'FPS.Armor.Torso')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Armor', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/food',
        description: 'Alias for /api/items scoped to food and drink (Food, Bottle, Drink types). Returns consumable items with manufacturer, game version, and description data.',
        summary: 'In-Game Food Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'TDD')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Burger')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Food Items', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-weapons',
        description: 'Alias for /api/items scoped to vehicle weapons (WeaponGun type). Returns ship weapon items with manufacturer, game version, and description data.',
        summary: 'In-Game Vehicle Weapons Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'KnightBridge Arms')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Cannon')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0–12).', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Vehicle Weapons', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-items',
        description: 'Alias for /api/items scoped to vehicle components (coolers, shields, power plants, quantum drives, thrusters, etc.). Returns component items with manufacturer, game version, and description data.',
        summary: 'In-Game Vehicle Items Overview',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Aegis Dynamics')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Shield')),
            new OA\Parameter(name: 'filter[type]', description: 'Exact match on item type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Cooler')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Exact match on item sub-type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Default')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Vehicle Items', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item'))),
        ]
    )]
    #[OA\Get(
        path: '/api/items',
        description: 'Returns paginated in-game items for the requested category and game version. Always includes manufacturer, game version, and description data. Crafting blueprints are loaded automatically. Supports filtering by type, classification, manufacturer, size, grade, and more. Available includes: shops, variants, related_items, blueprints, shops.items. Supports 150+ JSON field sorts. (see GET /api/items/filters for valid filter values)',
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
            new OA\Parameter(name: 'filter[variants]', description: 'When false, excludes variant items (base_id IS NOT NULL) and returns only base items. When true or omitted, returns all items including variants.', in: 'query', schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'filter[category]', description: 'Item category to scope results. Accepts: weapons, weapon-attachments, clothes, armor, food, medical, mining-modifiers, fps-items, vehicle-weapons, vehicle-items, vehicle-flair-items, vehicle-components.', in: 'query', schema: new OA\Schema(type: 'string', example: 'weapons')),
            new OA\Parameter(name: 'filter[type]', description: 'Exact match on item type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'WeaponPersonal')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Exact match on item sub-type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Barrel')),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'KnightBridge Arms')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Same as filter[manufacturer]. Accepts comma-separated values for OR matching.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Anvil Aerospace')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on item class name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'MGA_Assault')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Arrow')),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Armor). (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'FPS.Armor.Torso')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0–12).', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
            new OA\Parameter(name: 'filter[grade]', description: 'Exact item grade (1–7, mapped to A–G).', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
            new OA\Parameter(name: 'filter[class]', description: 'Exact match on item class. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Military')),
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
        description: 'Retrieve a specific FPS weapon by name or UUID. Alias for /api/items/{identifier} scoped to weapons. Supports includes: shops, variants, related_items, blueprints, shops.items.',
        summary: 'In-Game Weapon Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Arrow')),
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
        description: 'Retrieve a specific weapon attachment by name or UUID. Alias for /api/items/{identifier} scoped to weapon attachments. Supports includes: shops, variants, related_items, blueprints, shops.items.',
        summary: 'In-Game Weapon Attachment Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Iron Sight')),
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
        description: 'Retrieve a specific clothing item by name or UUID. Alias for /api/items/{identifier} scoped to clothing. Supports includes: shops, variants, related_items, blueprints, shops.items.',
        summary: 'In-Game Clothing Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Jacket')),
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
        description: 'Retrieve a specific armor item by name or UUID. Alias for /api/items/{identifier} scoped to armor. Supports includes: shops, variants, related_items, blueprints, shops.items.',
        summary: 'In-Game Armor Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Core')),
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
        description: 'Retrieve a specific food or drink item by name or UUID. Alias for /api/items/{identifier} scoped to food. Supports includes: shops, variants, related_items, blueprints, shops.items.',
        summary: 'In-Game Food Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Burger')),
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
        description: 'Retrieve a specific vehicle weapon by name or UUID. Alias for /api/items/{identifier} scoped to vehicle weapons. Supports includes: shops, variants, related_items, blueprints, shops.items.',
        summary: 'In-Game Vehicle Weapon Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Greatsword')),
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
        description: 'Retrieve a specific vehicle component by name or UUID. Alias for /api/items/{identifier} scoped to vehicle items. Supports includes: shops, variants, related_items, blueprints, shops.items.',
        summary: 'In-Game Vehicle Item Detail',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Shield')),
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
        description: 'Retrieve a specific item by UUID, slug, name, or class name (case-insensitive). Always includes manufacturer, game version, description data, entity tags, commodities, and variant group data. Supports includes: shops, variants, related_items, blueprints, shops.items. Vehicle-type items (NOITEM_Vehicle) automatically redirect to GET /api/vehicles/{uuid}.',
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
                    description: 'Item name, slug, class name, or UUID',
                    type: 'string',
                    example: 'Arrow',
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

            if ($itemData === null && ! $isUuid) {
                $itemData = $baseQuery()
                    ->whereHas('item', fn (Builder $itemQuery) => $itemQuery->where('slug', $identifier))
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
            new OA\Parameter(name: 'filter[variants]', description: 'When false, excludes variant items and returns only base items. When true or omitted, returns all items.', in: 'query', schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'filter[category]', description: 'Item category to scope results. Accepts: weapons, weapon-attachments, clothes, armor, food, medical, mining-modifiers, fps-items, vehicle-weapons, vehicle-items, vehicle-flair-items, vehicle-components.', in: 'query', schema: new OA\Schema(type: 'string', example: 'weapons')),
            new OA\Parameter(name: 'filter[type]', description: 'Exact match on item type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'WeaponPersonal')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Exact match on item sub-type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Barrel')),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'KnightBridge Arms')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Same as filter[manufacturer]. Accepts comma-separated values for OR matching.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Anvil Aerospace')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on item class name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'MGA_Assault')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Arrow')),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Armor). (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'FPS.Armor.Torso')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0–12).', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
            new OA\Parameter(name: 'filter[grade]', description: 'Exact item grade (1–7, mapped to A–G).', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
            new OA\Parameter(name: 'filter[class]', description: 'Exact match on item class. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Military')),
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
        description: 'Returns available filter facet values for in-game items, grouped by field with occurrence counts. Applying other filters narrows the facet results. Use these values as filter[*] parameters on GET /api/items. Scoped to the default item category unless filter[category] is specified.',
        summary: 'In-Game Item Filters',
        tags: ['In-Game', 'Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[variants]', description: 'When false, facets are computed excluding variant items. When true or omitted, all items are included.', in: 'query', schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(name: 'filter[category]', description: 'Item category to scope facets. Accepts: weapons, weapon-attachments, clothes, armor, food, medical, mining-modifiers, fps-items, vehicle-weapons, vehicle-items, vehicle-flair-items, vehicle-components.', in: 'query', schema: new OA\Schema(type: 'string', example: 'weapons')),
            new OA\Parameter(name: 'filter[type]', description: 'Narrow facets to items matching this type.', in: 'query', schema: new OA\Schema(type: 'string', example: 'WeaponPersonal')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Narrow facets to items matching this sub-type.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Barrel')),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Narrow facets to items from this manufacturer.', in: 'query', schema: new OA\Schema(type: 'string', example: 'KnightBridge Arms')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Same as filter[manufacturer].', in: 'query', schema: new OA\Schema(type: 'string', example: 'Anvil Aerospace')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Narrow facets to items with matching class name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'MGA_Assault')),
            new OA\Parameter(name: 'filter[name]', description: 'Narrow facets to items with matching name.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Arrow')),
            new OA\Parameter(name: 'filter[classification]', description: 'Narrow facets to items with matching classification.', in: 'query', schema: new OA\Schema(type: 'string', example: 'FPS.Armor.Torso')),
            new OA\Parameter(name: 'filter[size]', description: 'Narrow facets to items with this size.', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
            new OA\Parameter(name: 'filter[grade]', description: 'Narrow facets to items with this grade.', in: 'query', schema: new OA\Schema(type: 'number', example: 3)),
            new OA\Parameter(name: 'filter[class]', description: 'Narrow facets to items with this class.', in: 'query', schema: new OA\Schema(type: 'string', example: 'Military')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filter facets for in-game items, grouped by field with counts.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'type', description: 'Item types (e.g. WeaponPersonal, Cooler, Shield)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'sub_type', description: 'Item sub-types (e.g. Barrel, Default, Optic)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'classification', description: 'Item classifications (e.g. FPS.Armor.Torso, Ship.Cooler)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'size', description: 'Item sizes (0–12)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'grade', description: 'Item grades (1–7, mapped A–G)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'class', description: 'Item classes (Civilian, Competition, Industrial, Military, Stealth)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'manufacturer', description: 'Manufacturer names', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
                    'labelResolver' => [ItemTypeLabel::class, 'resolve'],
                ],
                'sub_type' => [
                    'expr' => 'game_item_data.sub_type',
                    'cast' => null,
                ],
                'classification' => [
                    'expr' => 'game_item_data.classification',
                    'cast' => null,
                    'labelResolver' => [ItemClassificationLabel::class, 'resolve'],
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
