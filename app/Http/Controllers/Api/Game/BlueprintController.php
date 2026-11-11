<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
use App\Exceptions\NotFoundInVersionException;
use App\Http\Controllers\Api\Concerns\ComputesFacets;
use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Blueprint\BlueprintResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Rules\UuidFilterValue;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterInput;
use App\Support\Filters\FilterValues;
use App\Support\Filters\ItemFilterLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('blueprints')]
class BlueprintController extends Controller
{
    use ComputesFacets;
    use FiltersJsonColumns;
    use ResolvesGameVersion;

    protected function getJsonTableName(): string
    {
        return 'game_blueprint_data';
    }

    protected function getJsonColumnName(): string
    {
        return 'data';
    }

    #[OA\Get(
        path: '/api/blueprints',
        operationId: 'listBlueprints',
        description: 'Returns paginated crafting blueprints, including ingredients, crafted items, and dismantle returns. Results are scoped to the requested or default game version.',
        summary: 'List Game Blueprints',
        tags: ['Blueprints'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: craft_time_seconds, ingredient_count, unlocking_missions_count.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '-craft_time_seconds')
            ),
            new OA\Parameter(name: 'filter[query]', description: 'Search blueprints by crafted item name. Example: `Shotgun`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[output.uuid]', description: 'Filter by crafted item UUID. Accepts comma-separated values (see GET /api/items). Example: `02d4cd2e-fa98-4086-aee1-6b2dfce8ea27`', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[output.name]', description: 'Filter by crafted item name. Example: `P4-AR Magazine (40 cap)`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[output.class]', description: 'Filter by crafted item class. Example: `ksar_smg_energy_01_gold01`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[output.type]', description: 'Filter by crafted item type (see GET /api/blueprints/filters for valid values). Example: `WeaponPersonal`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[default]', description: 'Filter by default availability (true/false). Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(
                name: 'filter[ingredient]',
                description: 'Matches ingredient resource type by name, key, or UUID before filtering blueprints. Example: `Aluminum`',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(name: 'filter[ingredient.uuid]', description: 'Filter by ingredient commodity UUID. Accepts comma-separated values (see GET /api/commodities). Example: `fde0cd65-8827-4b23-804d-cc8845dfa7ac`', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[resource.uuid]', description: 'Filter by resource commodity UUID, matching ingredients and dismantle returns. Accepts comma-separated values (see GET /api/commodities). Example: `fde0cd65-8827-4b23-804d-cc8845dfa7ac`', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of blueprints',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/blueprint')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 422, description: 'Invalid query parameters: unknown game version code or invalid filter value.', content: new OA\JsonContent(ref: '#/components/schemas/validation_error_response')),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        FilterInput::validate($request, $this->filterInputRules());

        $blueprints = $this->buildIndexQuery($request)
            ->with(['blueprint', 'gameVersion', 'dismantleReturns', 'ingredients.rawVersions'])
            ->defaultSort('key')
            ->jsonPaginate()
            ->appends($request->query());

        return BlueprintResource::collection($blueprints);
    }

    #[OA\Get(
        path: '/api/blueprints/{blueprint}',
        operationId: 'getBlueprint',
        description: 'Returns full detail for a single crafting blueprint, including ingredients, crafted item, dismantle returns, and associated missions. Scoped to the requested or default game version.',
        summary: 'Get Game Blueprint Detail',
        tags: ['Blueprints'],
        parameters: [
            new OA\Parameter(
                name: 'blueprint',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Blueprint UUID or slug',
                    type: 'string',
                    example: '280f47b7-8434-410c-b854-380768fdccec',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Blueprint detail',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/blueprint'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Blueprint not found, or no data for the requested game version',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_in_version_error_response'),
            ),
            new OA\Response(response: 422, description: 'Unknown game version code.', content: new OA\JsonContent(ref: '#/components/schemas/validation_error_response')),
        ]
    )]
    public function show(Request $request, string $blueprint): BlueprintResource
    {
        $blueprintModel = Blueprint::query()
            ->when(Str::isUuid($blueprint), fn (Builder $q) => $q->where('uuid', $blueprint))
            ->unless(Str::isUuid($blueprint), fn (Builder $q) => $q->where('slug', $blueprint))
            ->first();

        if ($blueprintModel === null) {
            abort(404, 'No blueprint found with the given identifier.');
        }

        $blueprintData = BlueprintData::query()
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->where('blueprint_id', $blueprintModel->id)
            ->with(['blueprint', 'gameVersion', 'dismantleReturns', 'ingredients.rawVersions', 'missions.mission'])
            ->first();

        if ($blueprintData === null) {
            $availableVersions = $blueprintModel->versionsWithData();

            if ($availableVersions !== []) {
                $resolvedVersion = $this->gameVersion()->code;

                throw new NotFoundInVersionException(
                    $this->gameVersionCode() ?? $resolvedVersion,
                    $availableVersions,
                    sprintf('No Blueprint data found for game version "%s".', $resolvedVersion),
                );
            }

            throw new NotFoundHttpException('No Blueprint found for the requested game version.');
        }

        return new BlueprintResource($blueprintData);
    }

    #[OA\Get(
        path: '/api/blueprints/filters',
        operationId: 'listBlueprintFilters',
        description: 'Returns available filter facets for blueprints (crafted item types, ingredient and resource UUIDs), optionally scoped to the requested or default game version.',
        summary: 'Get Blueprint Filter Options',
        tags: ['Blueprints'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Filter facets for blueprints',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(
                                    property: 'output.type',
                                    description: 'Crafted item types (e.g. WeaponPersonal, Char_Armor_Torso, WeaponAttachment)',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/filter_value')
                                ),
                                new OA\Property(
                                    property: 'ingredient.uuid',
                                    description: 'Ingredient commodity UUIDs with names and counts',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/filter_value')
                                ),
                                new OA\Property(
                                    property: 'resource.uuid',
                                    description: 'Combined ingredient and dismantle return resource UUIDs with names and counts',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/filter_value')
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Invalid query parameters: unknown game version code or invalid filter value.', content: new OA\JsonContent(ref: '#/components/schemas/validation_error_response')),
        ]
    )]
    public function filters(Request $request): JsonResponse
    {
        FilterInput::validate($request, $this->filterInputRules());

        return $this->computeFacetsResponse($request);
    }

    protected function facetModelClass(): string
    {
        return BlueprintData::class;
    }

    protected function facetBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(BlueprintData::class, $request)
            ->forRequestedOrDefaultVersion($this->gameVersionCode() ?? $this->gameVersion()->code)
            ->allowedFilters(...$this->allowedFilters());
    }

    protected function facetDefinitions(Request $request): array
    {
        $typeExpr = $this->jsonExpression('Output.Type');

        return [
            'output.type' => [
                'expr' => $typeExpr,
                'order_by' => $typeExpr,
                'labelResolver' => [ItemFilterLabel::class, 'resolveType'],
            ],
            'ingredient.uuid' => [
                'expr' => 'ic.uuid',
                'label_expr' => 'ic.name',
                'group_by' => 'ic.uuid, ic.name',
                'order_by' => 'ic.name',
                'join' => static fn ($q) => $q
                    ->join('game_blueprint_data_ingredients as bdi', 'game_blueprint_data.id', '=', 'bdi.blueprint_data_id')
                    ->join('game_commodities as ic', 'bdi.resource_type_id', '=', 'ic.id'),
            ],
        ];
    }

    protected function extraFacets(Request $request): array
    {
        $baseQuery = $this->facetBaseQuery($request);

        $ingredientRows = (clone $baseQuery)
            ->join('game_blueprint_data_ingredients as bdi', 'game_blueprint_data.id', '=', 'bdi.blueprint_data_id')
            ->join('game_commodities as ic', 'bdi.resource_type_id', '=', 'ic.id')
            ->select([
                DB::raw('ic.uuid as value'),
                DB::raw('ic.name as label'),
                DB::raw('count(*) as count'),
            ])
            ->groupByRaw('ic.uuid, ic.name')
            ->orderByRaw('ic.name')
            ->get();

        $combined = [];
        foreach ($ingredientRows as $row) {
            $combined[$row->value] = [
                'value' => $row->value,
                'label' => $row->label,
                'count' => (int) $row->count,
            ];
        }

        $dismantleRows = (clone $baseQuery)
            ->join('game_blueprint_data_dismantle_returns as bddr', 'game_blueprint_data.id', '=', 'bddr.blueprint_data_id')
            ->join('game_commodities as dc', 'bddr.resource_type_id', '=', 'dc.id')
            ->select([
                DB::raw('dc.uuid as value'),
                DB::raw('dc.name as label'),
                DB::raw('count(*) as count'),
            ])
            ->groupByRaw('dc.uuid, dc.name')
            ->orderByRaw('dc.name')
            ->get();

        foreach ($dismantleRows as $row) {
            if (isset($combined[$row->value])) {
                $combined[$row->value]['count'] += (int) $row->count;
            } else {
                $combined[$row->value] = [
                    'value' => $row->value,
                    'label' => $row->label,
                    'count' => (int) $row->count,
                ];
            }
        }

        usort($combined, static fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        return [
            'resource.uuid' => FilterValues::fromRows(
                collect($combined)->map(static fn (array $entry): object => (object) $entry)
            ),
        ];
    }

    protected function facetCacheNamespace(): string
    {
        return FilterCache::NAMESPACE_BLUEPRINTS;
    }

    protected function facetCacheKey(Request $request): string
    {
        return FilterCache::blueprintsKey($this->gameVersionCode() ?? $this->gameVersion()->code);
    }

    /**
     * Build base query with allowed filters for blueprint listing.
     */
    private function buildIndexQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(BlueprintData::class, $request)
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(...$this->allowedSorts());
    }

    /**
     * @return array<int, AllowedFilter>
     */
    /**
     * @return array<string, array<int, mixed>>
     */
    private function filterInputRules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.output.uuid' => ['sometimes', new UuidFilterValue],
        ];
    }

    /**
     * Normalize a uuid filter value into a de-duplicated list of trimmed, non-empty entries.
     *
     * @return array<int, string>
     */
    private static function normalizeUuidList(mixed $value): array
    {
        $entries = match (true) {
            is_array($value) => array_map(static fn (mixed $entry): string => trim((string) $entry), $value),
            is_string($value) => array_map(static fn (string $entry): string => trim($entry), explode(',', $value)),
            default => [],
        };

        return array_values(array_unique(array_filter($entries, static fn (string $entry): bool => $entry !== '')));
    }

    private function allowedFilters(): array
    {
        return [
            AllowedFilter::scope('query', 'searchOutput'),
            AllowedFilter::callback('output.uuid', static function (Builder $query, mixed $value): void {
                $outputItemUuids = self::normalizeUuidList($value);

                if ($outputItemUuids === []) {
                    return;
                }

                $query->whereIn('output_item_uuid', $outputItemUuids);
            }),
            AllowedFilter::callback('output.name', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->forOutputName($value);
            }),
            AllowedFilter::callback('output.class', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->forOutputClass($value);
            }),
            AllowedFilter::callback('output.type', function (Builder $query, mixed $value): void {
                $this->applyJsonFilter($query, 'Output.Type', $value, 'text');
            }),
            AllowedFilter::callback('default', static function (Builder $query, mixed $value): void {
                $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($normalized === null) {
                    return;
                }

                $query->where('is_available_by_default', $normalized);
            }),
            AllowedFilter::callback('ingredient.uuid', static function (Builder $query, mixed $value): void {
                $resourceTypeUuids = self::normalizeUuidList($value);

                if ($resourceTypeUuids === []) {
                    return;
                }

                foreach ($resourceTypeUuids as $resourceTypeUuid) {
                    $query->consumesResourceType($resourceTypeUuid);
                }
            }),
            AllowedFilter::callback('ingredient', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $matchingIngredientUuids = Commodity::query()
                    ->matchingLookup($value)
                    ->pluck('uuid')
                    ->all();

                if ($matchingIngredientUuids === []) {
                    $query->whereRaw('0 = 1');

                    return;
                }

                $query->consumesAnyResourceTypes($matchingIngredientUuids);
            }),
            AllowedFilter::callback('resource.uuid', static function (Builder $query, mixed $value): void {
                $resourceTypeUuids = self::normalizeUuidList($value);

                if ($resourceTypeUuids === []) {
                    return;
                }

                $query->where(static function (Builder $q) use ($resourceTypeUuids): void {
                    foreach ($resourceTypeUuids as $resourceTypeUuid) {
                        $q->orWhere(static function (Builder $subQ) use ($resourceTypeUuid): void {
                            $subQ->consumesResourceType($resourceTypeUuid)
                                ->orWhere->dismantleReturnsResourceType($resourceTypeUuid);
                        });
                    }
                });
            }),
        ];
    }

    /**
     * @return array<int, string|AllowedSort>
     */
    private function allowedSorts(): array
    {
        return [
            'craft_time_seconds',
            'unlocking_missions_count',
            AllowedSort::callback('ingredient_count', function (Builder $query, bool $descending): void {
                $query->orderByRaw(
                    "jsonb_array_length(jsonb_path_query_array(game_blueprint_data.data, '$.Tiers[0].Requirements.**.Kind ? (@ == \"resource\" || @ == \"item\")')) "
                    .($descending ? 'desc' : 'asc').' nulls last'
                );
            }),
        ];
    }
}
