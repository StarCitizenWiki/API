<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Api\Concerns\ComputesFacets;
use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Filters\ItemVariantsFilter;
use App\Http\Filters\NonEmptyExactFilter;
use App\Http\Filters\SortByRelation;
use App\Http\Includes\CustomEagerLoadInclude;
use App\Http\Includes\IncludeDefinition;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Item\ItemResource;
use App\Models\Game\ItemData;
use App\Support\Filters\FilterCache;
use App\Support\Filters\ItemFilterLabel;
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
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('items')]
class ItemController extends Controller
{
    use ComputesFacets;
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
     * @return array<int, IncludeDefinition>
     */
    private function includeDefinitions(): array
    {
        return [
            IncludeDefinition::custom('shops', new CustomEagerLoadInclude),
            IncludeDefinition::custom('shops.items', new CustomEagerLoadInclude),
            IncludeDefinition::custom('variants', new CustomEagerLoadInclude($this->variantIncludes())),
            IncludeDefinition::custom('related_items', new CustomEagerLoadInclude($this->relatedItemIncludes())),
            IncludeDefinition::custom('blueprints', new CustomEagerLoadInclude),
            IncludeDefinition::custom('vehicles', new CustomEagerLoadInclude($this->vehicleIncludes())),
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function defaultShowIncludes(): array
    {
        return [
            'entityTags' => fn ($query) => $query->select($this->entityTagColumns()),
            'item' => fn ($query) => $query
                ->select($this->fullItemColumns())
                ->withExists('vehicle'),
            'gameVersion' => fn ($query) => $query->select($this->gameVersionColumns()),
            'variantGroupItem' => fn ($query) => $query->select($this->variantGroupItemColumns()),
            'baseVariant' => fn ($query) => $query->select($this->itemDataLinkColumns()),
            'baseVariant.item' => fn ($query) => $query->select($this->itemIdentityColumns()),
            'baseVariant.manufacturer' => fn ($query) => $query->select($this->manufacturerColumns()),
            'baseVariant.gameVersion' => fn ($query) => $query->select($this->gameVersionColumns()),
            'manufacturer' => fn ($query) => $query->select($this->manufacturerColumns()),
            'descriptionData',
            'commodities' => fn ($query) => $query->select($this->commodityColumns()),
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function variantIncludes(): array
    {
        return [
            'variants' => fn ($query) => $query->select($this->itemDataLinkColumns()),
            'variants.item' => fn ($query) => $query->select($this->itemIdentityColumns()),
            'variants.manufacturer' => fn ($query) => $query->select($this->manufacturerColumns()),
            'variants.gameVersion' => fn ($query) => $query->select($this->gameVersionColumns()),
            'variants.baseVariant' => fn ($query) => $query->select($this->itemDataLinkColumns()),
            'variants.baseVariant.item' => fn ($query) => $query->select($this->itemIdentityColumns()),
            'variants.variantGroupItem' => fn ($query) => $query->select($this->variantGroupItemColumns()),
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function relatedItemIncludes(): array
    {
        return [
            'variantGroupItem.variantGroup' => fn ($query) => $query->select($this->variantGroupColumns()),
            'variantGroupItem.variantGroup.items' => fn ($query) => $query->select($this->variantGroupItemColumns()),
            'variantGroupItem.variantGroup.items.itemData' => fn ($query) => $query->select($this->itemDataLinkColumns()),
            'variantGroupItem.variantGroup.items.itemData.item' => fn ($query) => $query->select($this->itemIdentityColumns()),
            'variantGroupItem.variantGroup.items.itemData.manufacturer' => fn ($query) => $query->select($this->manufacturerColumns()),
            'setItems' => fn ($query) => $query->select($this->setItemDataColumns()),
            'setItems.item' => fn ($query) => $query->select($this->itemIdentityColumns()),
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    private function vehicleIncludes(): array
    {
        return [
            'installedOnVehicles' => fn ($query) => $query->select($this->vehicleDataLinkColumns()),
            'installedOnVehicles.vehicle' => fn ($query) => $query->select($this->vehicleIdentityColumns()),
            'installedOnVehicles.manufacturer' => fn ($query) => $query->select($this->manufacturerColumns()),
            'installedOnVehicles.gameVersion' => fn ($query) => $query->select($this->gameVersionColumns()),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function itemDataLinkColumns(): array
    {
        return [
            'game_item_data.id',
            'game_item_data.item_id',
            'game_item_data.game_version_id',
            'game_item_data.manufacturer_id',
            'game_item_data.name',
            'game_item_data.class_name',
            'game_item_data.type',
            'game_item_data.sub_type',
            'game_item_data.classification',
            'game_item_data.size',
            'game_item_data.grade',
            'game_item_data.class',
            'game_item_data.base_id',
            'game_item_data.rarity',
            'game_item_data.updated_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function setItemDataColumns(): array
    {
        return [
            'game_item_data.id',
            'game_item_data.item_id',
            'game_item_data.name',
            'game_item_data.class_name',
            'game_item_data.type',
            'game_item_data.sub_type',
            'game_item_data.classification',
            'game_item_data.size',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function vehicleDataLinkColumns(): array
    {
        return [
            'game_vehicle_data.id',
            'game_vehicle_data.vehicle_id',
            'game_vehicle_data.manufacturer_id',
            'game_vehicle_data.game_version_id',
            'game_vehicle_data.class_name',
            'game_vehicle_data.name',
            'game_vehicle_data.display_name',
            'game_vehicle_data.career',
            'game_vehicle_data.role',
            'game_vehicle_data.size',
            'game_vehicle_data.is_vehicle',
            'game_vehicle_data.is_gravlev',
            'game_vehicle_data.is_spaceship',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function fullItemColumns(): array
    {
        return [
            'game_items.id',
            'game_items.uuid',
            'game_items.slug',
            'game_items.translation',
            'game_items.images',
            'game_items.updated_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function itemIdentityColumns(): array
    {
        return [
            'game_items.id',
            'game_items.uuid',
            'game_items.slug',
            'game_items.updated_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function vehicleIdentityColumns(): array
    {
        return [
            'game_vehicles.id',
            'game_vehicles.uuid',
            'game_vehicles.slug',
            'game_vehicles.updated_at',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function manufacturerColumns(): array
    {
        return [
            'game_manufacturers.id',
            'game_manufacturers.name',
            'game_manufacturers.code',
            'game_manufacturers.uuid',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function gameVersionColumns(): array
    {
        return [
            'game_versions.id',
            'game_versions.code',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function variantGroupItemColumns(): array
    {
        return [
            'game_item_variant_group_items.id',
            'game_item_variant_group_items.variant_group_id',
            'game_item_variant_group_items.item_data_id',
            'game_item_variant_group_items.variant_name',
            'game_item_variant_group_items.sort_order',
            'game_item_variant_group_items.is_base',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function variantGroupColumns(): array
    {
        return [
            'game_item_variant_groups.id',
            'game_item_variant_groups.game_version_id',
            'game_item_variant_groups.set_name',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function entityTagColumns(): array
    {
        return [
            'game_entity_tags.id',
            'game_entity_tags.uuid',
            'game_entity_tags.name',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function commodityColumns(): array
    {
        return [
            'game_commodities.id',
            'game_commodities.uuid',
            'game_commodities.name',
            'game_commodities.slug',
        ];
    }

    /**
     * Get allowed includes derived from include definitions.
     */
    private function allowedIncludes(): array
    {
        return IncludeDefinition::toSpatieIncludes($this->includeDefinitions());
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
            ->when(
                ! $request->filled('filter.include_irrelevant'),
                fn ($q) => $q->where('game_item_data.is_player_relevant', true),
            )
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
                    AllowedSort::callback('rarity', function (Builder $query, bool $descending): Builder {
                        $direction = $descending ? 'desc' : 'asc';

                        return $query->orderByRaw("game_item_data.rarity {$direction} nulls last");
                    }),
                    AllowedSort::callback('Mass', function (Builder $query, bool $descending): Builder {
                        $direction = $descending ? 'desc' : 'asc';

                        return $query->orderByRaw("game_item_data.mass {$direction} nulls last");
                    }),
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
            AllowedFilter::callback('class_name', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->whereLike('game_item_data.class_name', "%{$value}%");
            }),
            AllowedFilter::callback('name', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->whereLike('game_item_data.name', "%{$value}%");
            }),
            AllowedFilter::callback('classification', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->whereLike('game_item_data.classification', "%{$value}%");
            }),
            AllowedFilter::exact('size'),
            AllowedFilter::exact('grade'),
            AllowedFilter::exact('class'),
            AllowedFilter::callback('include_irrelevant', static function (Builder $query): void {
                // noop
            }),
            AllowedFilter::custom('rarity', new NonEmptyExactFilter, 'game_item_data.rarity'),
            AllowedFilter::callback('event_source', static function (Builder $query, mixed $value): void {
                $sources = self::normalizeFilterTags($value);
                if ($sources === []) {
                    return;
                }

                $query->where(static function (Builder $q) use ($sources): void {
                    foreach ($sources as $source) {
                        $q->orWhereJsonContains('event_source', $source);
                    }
                });
            }),
            AllowedFilter::callback('is_lootable', static function (Builder $query, mixed $value): void {
                $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($normalized === null) {
                    return;
                }

                $query->where('is_lootable', $normalized);
            }),
            AllowedFilter::callback('is_craftable', static function (Builder $query, mixed $value): void {
                $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($normalized === null) {
                    return;
                }

                $query->where('is_craftable', $normalized);
            }),

            // filter[tags]: AND on RequiredTags -> item must have ALL provided tags
            AllowedFilter::callback('tags', static function (Builder $query, mixed $value): void {
                $tags = self::normalizeFilterTags($value);
                if ($tags === []) {
                    return;
                }

                foreach ($tags as $tag) {
                    $query->whereJsonContains('data->stdItem->RequiredTags', $tag);
                }
            }),

            // filter[port_tags]: OR match for ports with port_tags but no required_tags.
            // Mode 1: bespoke items whose RequiredTags match any port tag.
            // Mode 2: universal items whose Tags overlap any port tag (paint/fuel system).
            // Items with no RequiredTags and no Tag overlap are excluded.
            AllowedFilter::callback('port_tags', static function (Builder $query, mixed $value): void {
                $tags = self::normalizeFilterTags($value);
                if ($tags === []) {
                    return;
                }

                $query->where(static function (Builder $q) use ($tags): void {
                    self::whereAnyRequiredTagMatches($q, $tags);
                    $q->orWhere(static function (Builder $inner) use ($tags): void {
                        $inner->where('has_required_tags', false)
                            ->where('is_bespoke', false);
                        self::whereAnyTagMatches($inner, $tags);
                    });
                });
            }),

            // filter[vehicle]: scope items to a specific vehicle.
            // 1: universal (no RequiredTags AND not bespoke)
            // 2: Bespoke items whose RequiredTags match any vehicle identity tag
            // 3: Bespoke items whose bespoke_vehicle_tags match any vehicle identity tag
            AllowedFilter::callback('vehicle', static function (Builder $query, mixed $value): void {
                $tags = self::normalizeFilterTags($value);
                if ($tags === []) {
                    return;
                }

                $query->where(static function (Builder $q) use ($tags): void {
                    // 1: Universal items
                    $q->where(static function (Builder $inner): void {
                        $inner->where('has_required_tags', false)
                            ->where('is_bespoke', false);
                    });

                    // 2: Bespoke items matching via RequiredTags
                    self::whereAnyRequiredTagMatches($q, $tags);

                    // 3: Bespoke items matching via bespoke_vehicle_tags
                    foreach ($tags as $tag) {
                        $q->orWhereJsonContains('bespoke_vehicle_tags', $tag);
                    }
                });
            }),
            AllowedFilter::custom('variants', new ItemVariantsFilter),
            AllowedFilter::callback('query', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->where(static function (Builder $q) use ($value): void {
                    $pattern = "%{$value}%";

                    $q->whereLike('game_item_data.name', $pattern)
                        ->orWhereLike('game_item_data.class_name', $pattern);
                });
            }),
        ];
    }

    #[OA\Get(
        path: '/api/weapons',
        operationId: 'listWeapons',
        description: 'Alias for /api/items scoped to FPS weapons (WeaponPersonal type). Results are scoped to the requested or default game version. Returns weapon items with manufacturer, game version, and description data.',
        summary: 'In-Game Weapons Overview',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Klaus & Werner`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on item class name. Example: `behr_sniper_ballistic_01`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Arrow`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search items by name or class name. Example: `Arrow`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0-12). Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', description: 'Exact item grade (1-7, mapped to A-G). Example: `1`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Weapons', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/weapon-attachments',
        operationId: 'listWeaponAttachments',
        description: 'Alias for /api/items scoped to weapon attachments (WeaponAttachment type, excluding magazines and missiles). Results are scoped to the requested or default game version. Returns attachment items with manufacturer, game version, and description data.',
        summary: 'In-Game Weapon Attachments Overview',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `ArmaMod`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Quell Suppressor2`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search items by name or class name. Example: `Quell Suppressor2`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0-12). Example: `1`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Weapon Attachments', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/clothes',
        operationId: 'listClothes',
        description: 'Alias for /api/items scoped to clothing (FPS.Clothing.* classification). Results are scoped to the requested or default game version. Returns clothing items with manufacturer, game version, and description data.',
        summary: 'In-Game Clothes Overview',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Fiore`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Jacket`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Clothing). (see GET /api/items/filters for valid values). Example: `FPS.Clothing.Torso`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search items by name or class name. Example: `Jacket`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Clothes', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/armor',
        operationId: 'listArmor',
        description: 'Alias for /api/items scoped to armor (FPS.Armor.* classification). Results are scoped to the requested or default game version. Returns armor items with manufacturer, game version, and description data.',
        summary: 'In-Game Armor Overview',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Clark Defense Systems`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Core`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Armor). (see GET /api/items/filters for valid values). Example: `FPS.Armor.Torso`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search items by name or class name. Example: `Core`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Armor', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/food',
        operationId: 'listFood',
        description: 'Alias for /api/items scoped to food and drink (Food, Bottle, Drink types). Results are scoped to the requested or default game version. Returns consumable items with manufacturer, game version, and description data.',
        summary: 'In-Game Food Overview',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Consumable`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Burger`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search items by name or class name. Example: `Burger`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Food Items', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-weapons',
        operationId: 'listVehicleWeapons',
        description: 'Alias for /api/items scoped to vehicle weapons (WeaponGun type). Results are scoped to the requested or default game version. Returns ship weapon items with manufacturer, game version, and description data.',
        summary: 'In-Game Vehicle Weapons Overview',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `KnightBridge Arms`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Cannon`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search items by name or class name. Example: `Cannon`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0-12). Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Vehicle Weapons', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-items',
        operationId: 'listVehicleItems',
        description: 'Alias for /api/items scoped to vehicle components (coolers, shields, power plants, quantum drives, thrusters, etc.). Results are scoped to the requested or default game version. Returns component items with manufacturer, game version, and description data.',
        summary: 'In-Game Vehicle Items Overview',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Aegis Dynamics`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Shield`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Exact match on item type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Cooler`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Exact match on item sub-type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Default`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search items by name or class name. Example: `Shield`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of Vehicle Items', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')), new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'), new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta')], type: 'object')),
        ]
    )]
    #[OA\Get(
        path: '/api/items',
        operationId: 'listItems',
        description: 'Returns paginated in-game items for the requested category and game version. Always includes manufacturer, game version, and description data. Crafting blueprints are loaded automatically. Supports filtering by type, classification, manufacturer, size, grade, and more. Available includes: shops, variants, related_items, blueprints, vehicles, shops.items. Supports 150+ JSON field sorts. (see GET /api/items/filters for valid filter values)',
        summary: 'In-Game Item Overview',
        tags: ['Items'],
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
            new OA\Parameter(name: 'filter[variants]', description: 'When false, excludes variant items (base_id IS NOT NULL) and returns only base items. When true or omitted, returns all items including variants.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[category]', description: 'Item category to scope results. Accepts: weapons, weapon-attachments, clothes, armor, food, medical, mining-modifiers, fps-items, vehicle-weapons, vehicle-items, vehicle-flair-items, vehicle-components. Example: `weapons`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Exact match on item type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `WeaponPersonal`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Exact match on item sub-type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Barrel`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `KnightBridge Arms`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Same as filter[manufacturer]. Accepts comma-separated values for OR matching. Example: `Anvil Aerospace`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on item class name. Example: `Controller_Comms`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Arrow`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'filter[query]',
                description: 'Search items by name or class name. Example: `helmet`',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Armor). (see GET /api/items/filters for valid values). Example: `FPS.Armor.Torso`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0-12). Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', description: 'Exact item grade (1-7, mapped to A-G). Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[class]', description: 'Exact match on item class. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Military`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[rarity]', description: 'Item rarity. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Rare`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[event_source]', description: 'Event or reward source label. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `IAE`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[tags]', description: 'Filter by stdItem.RequiredTags array values. Use when a port has required_tags - matches items whose RequiredTags contain ALL specified values. Accepts comma-separated tags for AND matching. Example: `MISC_Fury_Miru`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[port_tags]', description: 'Filter items by RequiredTags compatibility with a port\'s tags. Accepts comma-separated port tag values. Returns items where any of their RequiredTags appear in the provided tags, OR items with no RequiredTags but whose Tags overlap with the provided tags (e.g. older paint system). Items with no RequiredTags and no overlapping Tags are excluded. Pass the port_tags value from a vehicle hardpoint port. Example: `flight_ready,Ship_Dock_Refuel`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[vehicle]', description: 'Scope items to a specific vehicle. Accepts one or more vehicle identity tags (from the vehicle\'s port_tags field). Returns: (1) universal items with no RequiredTags and not bespoke, (2) bespoke items whose RequiredTags match any provided tag, (3) bespoke items whose bespoke_vehicle_tags match any provided tag. Use on vehicle pages to show only items equippable on that vehicle. Example: `AEGS_Avenger_Base`', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Items',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'item_search_page',
                            summary: 'Search items by text',
                            value: [
                                'data' => [
                                    ['uuid' => '00000000-0000-0000-0000-000000000000', 'name' => 'Flight Helmet', 'slug' => 'flight-helmet'],
                                ],
                                'links' => ['first' => 'https://api.star-citizen.wiki/api/items?page[number]=1', 'last' => null, 'prev' => null, 'next' => null],
                                'meta' => ['current_page' => 1, 'per_page' => 30, 'total' => 1],
                            ],
                        ),
                    ],
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object',
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

        ItemResource::preloadLocationData(
            $items->getCollection()
                ->pluck('uex_prices')
                ->flatten(1)
                ->pluck('starmap_location_data_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray()
        );

        return ItemResource::collection($items)
            ->additional(['meta' => ['valid_relations' => IncludeDefinition::toNames($this->includeDefinitions())]]);
    }

    #[OA\Get(
        path: '/api/weapons/{identifier}',
        operationId: 'getWeapon',
        description: 'Retrieve a specific FPS weapon by name or UUID. Results are scoped to the requested or default game version. Alias for /api/items/{identifier} scoped to weapons. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items.',
        summary: 'In-Game Weapon Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Scourge Railgun')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Weapon', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/game_item')], type: 'object')),
            new OA\Response(response: 404, description: 'Weapon not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/weapon-attachments/{identifier}',
        operationId: 'getWeaponAttachment',
        description: 'Retrieve a specific weapon attachment by name or UUID. Results are scoped to the requested or default game version. Alias for /api/items/{identifier} scoped to weapon attachments. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items.',
        summary: 'In-Game Weapon Attachment Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Quell Suppressor2')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Weapon Attachment', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/game_item')], type: 'object')),
            new OA\Response(response: 404, description: 'Weapon attachment not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/clothes/{identifier}',
        operationId: 'getClothingItem',
        description: 'Retrieve a specific clothing item by name or UUID. Results are scoped to the requested or default game version. Alias for /api/items/{identifier} scoped to clothing. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items.',
        summary: 'In-Game Clothing Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Burgundy Paisley Bandana')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Clothing Item', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/game_item')], type: 'object')),
            new OA\Response(response: 404, description: 'Clothing item not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/armor/{identifier}',
        operationId: 'getArmor',
        description: 'Retrieve a specific armor item by name or UUID. Results are scoped to the requested or default game version. Alias for /api/items/{identifier} scoped to armor. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items.',
        summary: 'In-Game Armor Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Ace Interceptor Helmet')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'An Armor Item', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/game_item')], type: 'object')),
            new OA\Response(response: 404, description: 'Armor item not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/food/{identifier}',
        operationId: 'getFood',
        description: 'Retrieve a specific food or drink item by name or UUID. Results are scoped to the requested or default game version. Alias for /api/items/{identifier} scoped to food. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items.',
        summary: 'In-Game Food Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Whamburger')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Food Item', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/game_item')], type: 'object')),
            new OA\Response(response: 404, description: 'Food item not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-weapons/{identifier}',
        operationId: 'getVehicleWeapon',
        description: 'Retrieve a specific vehicle weapon by name or UUID. Results are scoped to the requested or default game version. Alias for /api/items/{identifier} scoped to vehicle weapons. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items.',
        summary: 'In-Game Vehicle Weapon Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Predator Scattergun')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Vehicle Weapon', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/game_item')], type: 'object')),
            new OA\Response(response: 404, description: 'Vehicle weapon not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/vehicle-items/{identifier}',
        operationId: 'getVehicleItem',
        description: 'Retrieve a specific vehicle component by name or UUID. Results are scoped to the requested or default game version. Alias for /api/items/{identifier} scoped to vehicle items. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items.',
        summary: 'In-Game Vehicle Item Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(name: 'identifier', in: 'path', required: true, schema: new OA\Schema(description: 'Item name or UUID', type: 'string', example: 'Frost-Star')),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(response: 200, description: 'A Vehicle Item', content: new OA\JsonContent(properties: [new OA\Property(property: 'data', ref: '#/components/schemas/game_item')], type: 'object')),
            new OA\Response(response: 404, description: 'Vehicle item not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    #[OA\Get(
        path: '/api/items/{identifier}',
        operationId: 'getItem',
        description: 'Retrieve a specific item by UUID, slug, name, or class name (case-insensitive). Results are scoped to the requested or default game version. Always includes manufacturer, game version, description data, entity tags, commodities, and variant group data. Supports includes: shops, variants, related_items, blueprints, vehicles, shops.items. Items with a matching vehicle record automatically redirect to GET /api/vehicles/{uuid}.',
        summary: 'In-Game Item Detail',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(
                name: 'include',
                description: 'Comma-separated relationships to include. Available: blueprints (full crafting blueprint data including ingredients, missions, tiers), variants (item variants), related_items (related items from variant groups and sets), vehicles (vehicles this item is installed on), shops (shop availability data), shops.items (shop items).',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'blueprints')
            ),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'identifier',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Item name, slug, class name, or UUID',
                    type: 'string',
                    example: 'Scourge Railgun',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'An Item',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/game_item'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Item not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    public function show(Request $request, string $identifier): ItemResource|RedirectResponse
    {
        $original = $identifier;
        $versionCode = $this->gameVersionCode();
        $identifier = $this->cleanQueryName($identifier);
        $isUuid = Str::isUuid($identifier);

        try {
            $underscored = str_replace(' ', '_', $identifier);

            $baseQuery = fn () => QueryBuilder::for(ItemData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->allowedIncludes(...$this->allowedIncludes())
                ->with($this->defaultShowIncludes());

            $itemData = $baseQuery()
                ->when(
                    $isUuid,
                    fn (Builder $q) => $q->whereHas('item', fn (Builder $itemQuery) => $itemQuery->where('uuid', $identifier)),
                    fn (Builder $q) => $q->whereHas('item', fn (Builder $itemQuery) => $itemQuery->where('slug', Str::slug($identifier))),
                )
                ->first();

            if ($itemData === null && ! $isUuid) {
                $itemData = $baseQuery()
                    ->where(function (Builder $q) use ($identifier, $original, $underscored) {
                        $q->where('game_item_data.name', $identifier)
                            ->orWhere('game_item_data.class_name', $underscored)
                            ->orWhere('game_item_data.class_name', $original);
                    })
                    ->first();
            }

            if ($itemData === null) {
                throw new ModelNotFoundException;
            }

            $includeInput = $request->input('include', '');
            $includeBlueprint = collect(is_array($includeInput) ? $includeInput : explode(',', (string) $includeInput))
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

        if ((bool) ($itemData->item->vehicle_exists ?? false)) {
            $url = sprintf('/api/vehicles/%s', $itemData->item->uuid);
            $qs = $request->server->get('QUERY_STRING');

            return redirect($qs !== null && $qs !== '' ? $url.'?'.$qs : $url);
        }

        ItemResource::preloadLocationData(
            collect($itemData->uex_prices ?? [])
                ->pluck('starmap_location_data_id')
                ->filter()
                ->unique()
                ->values()
                ->toArray()
        );

        return new ItemResource($itemData)
            ->setValidIncludes(IncludeDefinition::toNames($this->includeDefinitions()));
    }

    #[OA\Post(
        path: '/api/items/search',
        operationId: 'searchItemsDeprecated',
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
        tags: ['Items', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(ref: '#/components/parameters/sort'),
            new OA\Parameter(name: 'filter[variants]', description: 'When false, excludes variant items and returns only base items. When true or omitted, returns all items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[category]', description: 'Item category to scope results. Accepts: weapons, weapon-attachments, clothes, armor, food, medical, mining-modifiers, fps-items, vehicle-weapons, vehicle-items, vehicle-flair-items, vehicle-components. Example: `weapons`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Exact match on item type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `WeaponPersonal`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Exact match on item sub-type. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Barrel`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Manufacturer name or code. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `KnightBridge Arms`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Same as filter[manufacturer]. Accepts comma-separated values for OR matching. Example: `Anvil Aerospace`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Partial match on item class name. Example: `MGA_Assault`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on item display name. Example: `Arrow`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', description: 'Partial match on item classification (dot-notation, e.g. FPS.Armor). (see GET /api/items/filters for valid values). Example: `FPS.Armor.Torso`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Exact item size (0-12). Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', description: 'Exact item grade (1-7, mapped to A-G). Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[class]', description: 'Exact match on item class. Accepts comma-separated values for OR matching. (see GET /api/items/filters for valid values). Example: `Military`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Items',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_item')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object'
                )
            ),
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $toSearch = $request->validated('query');
        $isUuid = Str::isUuid($toSearch);

        $pattern = "%{$toSearch}%";

        $query = $this->buildBaseQuery($request)
            ->where(function (Builder $query) use ($toSearch, $isUuid, $pattern) {
                $query->whereLike('name', $pattern)
                    ->orWhereRaw('LOWER(type) = LOWER(?)', [$toSearch])
                    ->orWhereRaw('LOWER(sub_type) = LOWER(?)', [$toSearch]);

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
        operationId: 'listItemFilters',
        description: 'Returns available filter facet values for in-game items, grouped by field with occurrence counts. Applying other filters narrows the facet results. Use these values as filter[*] parameters on GET /api/items. Scoped to the default item category unless filter[category] is specified.',
        summary: 'In-Game Item Filters',
        tags: ['Items'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[variants]', description: 'When false, facets are computed excluding variant items. When true or omitted, all items are included.', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[category]', description: 'Item category to scope facets. Accepts: weapons, weapon-attachments, clothes, armor, food, medical, mining-modifiers, fps-items, vehicle-weapons, vehicle-items, vehicle-flair-items, vehicle-components. Example: `weapons`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', description: 'Narrow facets to items matching this type. Example: `WeaponPersonal`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[sub_type]', description: 'Narrow facets to items matching this sub-type. Example: `Barrel`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer]', description: 'Narrow facets to items from this manufacturer. Example: `KnightBridge Arms`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[manufacturer.name]', description: 'Same as filter[manufacturer]. Example: `Anvil Aerospace`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[class_name]', description: 'Narrow facets to items with matching class name. Example: `MGA_Assault`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Narrow facets to items with matching name. Example: `Arrow`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Narrow facets to items matching name or class name. Example: `Arrow`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[classification]', description: 'Narrow facets to items with matching classification. Example: `FPS.Armor.Torso`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', description: 'Narrow facets to items with this size. Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[grade]', description: 'Narrow facets to items with this grade. Example: `3`', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'filter[class]', description: 'Narrow facets to items with this class. Example: `Military`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[rarity]', description: 'Narrow facets to items with this rarity. Example: `Rare`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[event_source]', description: 'Narrow facets to items with this event or reward source. Example: `IAE`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_irrelevant]', description: 'When set to true, includes items flagged as not player-relevant (test, placeholder, dev items). Default shows only relevant items.', in: 'query', schema: new OA\Schema(type: 'boolean')),
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
                                new OA\Property(property: 'size', description: 'Item sizes (0-12)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'grade', description: 'Item grades (1-7, mapped A-G)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'class', description: 'Item classes (Civilian, Competition, Industrial, Military, Stealth)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'manufacturer', description: 'Manufacturer names', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'rarity', description: 'Item rarity levels (Common, Uncommon, Rare, Epic, Legendary)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'event_source', description: 'Event or reward source labels (count-less values)', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
        return $this->computeFacetsResponse($request);
    }

    protected function facetModelClass(): string
    {
        return ItemData::class;
    }

    private function resolveCategory(Request $request): string
    {
        $category = $request->input('filter.category');

        if (! is_string($category) || $category === '') {
            $category = $request->route()->defaults['category'] ?? 'items';
        } else {
            $category = trim($category);
        }

        return $category;
    }

    protected function facetBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(ItemData::class, $request)
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->forCategory($this->resolveCategory($request))
            ->playerRelevant()
            ->allowedFilters(...$this->allowedFilters());
    }

    protected function facetDefinitions(Request $request): array
    {
        return [
            'type' => [
                'expr' => 'game_item_data.type',
                'labelResolver' => [ItemFilterLabel::class, 'resolveType'],
            ],
            'sub_type' => [
                'expr' => 'game_item_data.sub_type',
                'labelResolver' => [ItemFilterLabel::class, 'resolveSubType'],
            ],
            'classification' => [
                'expr' => 'game_item_data.classification',
                'labelResolver' => [ItemFilterLabel::class, 'resolveClassification'],
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
            ],
            'rarity' => [
                'expr' => 'game_item_data.rarity',
            ],
            'manufacturer' => [
                'expr' => 'game_manufacturers.name',
                'join' => static fn ($q) => $q->leftJoin('game_manufacturers', 'game_item_data.manufacturer_id', '=', 'game_manufacturers.id'),
            ],
        ];
    }

    protected function ignoredFacetFilters(): array
    {
        return ['category'];
    }

    protected function extraFacets(Request $request): array
    {
        return [
            'event_source' => $this->eventSourceFilterValues(clone $this->facetBaseQuery($request)),
        ];
    }

    protected function facetCacheNamespace(): string
    {
        return FilterCache::NAMESPACE_ITEMS;
    }

    protected function facetCacheKey(Request $request): string
    {
        return FilterCache::itemsKey($this->gameVersionCode(), $this->resolveCategory($request));
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function eventSourceFilterValues(QueryBuilder $query): array
    {
        $tableExpression = 'LATERAL jsonb_array_elements_text(game_item_data.event_source) AS event_source_values(value)';

        $valueExpression = 'event_source_values.value';

        return $query
            ->join(DB::raw($tableExpression), DB::raw('1'), '=', DB::raw('1'))
            ->selectRaw("{$valueExpression} as value")
            ->whereRaw("{$valueExpression} IS NOT NULL")
            ->whereRaw("{$valueExpression} <> ''")
            ->distinct()
            ->orderBy('value')
            ->get()
            ->pluck('value')
            ->filter(static fn (mixed $value): bool => is_string($value) && trim($value) !== '')
            ->map(static fn (string $value): array => [
                'value' => $value,
                'label' => $value,
            ])
            ->values()
            ->all();
    }

    /**
     * Normalize a filter value to a non-empty array of tag strings.
     *
     * @return list<string>
     */
    private static function normalizeFilterTags(mixed $value): array
    {
        $tags = is_array($value) ? $value : [$value];

        return array_map(static function (mixed $item): ?string {
            if (! is_scalar($item)) {
                return null;
            }

            $tag = trim((string) $item);

            return $tag === '' ? null : $tag;
        }, $tags)
                |> (static fn ($x) => array_filter($x, static fn (?string $item): bool => $item !== null))
                |> array_values(...);
    }

    /**
     * Add an OR constraint that matches items whose RequiredTags contain any of the given tags.
     *
     * @param  list<string>  $tags
     */
    private static function whereAnyRequiredTagMatches(Builder $query, array $tags): void
    {
        $query->orWhere(static function (Builder $q) use ($tags): void {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('data->stdItem->RequiredTags', $tag);
            }
        });
    }

    /**
     * Add an AND constraint that matches items whose Tags overlap with any of the given tags.
     *
     * @param  list<string>  $tags
     */
    private static function whereAnyTagMatches(Builder $query, array $tags): void
    {
        $query->where(static function (Builder $q) use ($tags): void {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('data->stdItem->Tags', $tag);
            }
        });
    }
}
