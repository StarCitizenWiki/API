<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Blueprint\BlueprintResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\Commodity\Commodity;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use App\Support\Filters\ItemTypeLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlueprintController extends Controller
{
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
        description: 'Returns paginated crafting blueprints, including ingredients, crafted items, and dismantle returns. Results are scoped to the requested or default game version.',
        summary: 'List Game Blueprints',
        tags: ['In-Game', 'Blueprints'],
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
            new OA\Parameter(name: 'filter[query]', description: 'Search blueprints by crafted item name', in: 'query', schema: new OA\Schema(type: 'string', example: 'Distiller')),
            new OA\Parameter(name: 'filter[output.uuid]', description: 'Filter by crafted item UUID (see GET /api/items)', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[output.name]', description: 'Filter by crafted item name', in: 'query', schema: new OA\Schema(type: 'string', example: 'P4-AR Magazine (40 cap)')),
            new OA\Parameter(name: 'filter[output.class]', description: 'Filter by crafted item class', in: 'query', schema: new OA\Schema(type: 'string', example: 'ksar_smg_energy_01_gold01')),
            new OA\Parameter(name: 'filter[output.type]', description: 'Filter by crafted item type (see GET /api/blueprints/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'WeaponPersonal')),
            new OA\Parameter(name: 'filter[default]', description: 'Filter by default availability (true/false)', in: 'query', schema: new OA\Schema(type: 'boolean', example: true)),
            new OA\Parameter(
                name: 'filter[ingredient]',
                description: 'Matches ingredient resource type by name, key, or UUID before filtering blueprints.',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(name: 'filter[ingredient.uuid]', description: 'Filter by ingredient commodity UUID. Accepts comma-separated values (see GET /api/commodities)', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid', example: 'a11394c9-ad7c-404d-9209-3a5e57bb4aa4')),
            new OA\Parameter(name: 'filter[resource.uuid]', description: 'Filter by resource commodity UUID, matching ingredients and dismantle returns. Accepts comma-separated values (see GET /api/commodities)', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid', example: '9b47bacf-8efa-42e2-8d84-dee64983a00a')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of blueprints',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/blueprint')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $blueprints = $this->buildIndexQuery($request)
            ->with(['blueprint', 'gameVersion', 'dismantleReturns', 'ingredients'])
            ->withCount('missions')
            ->defaultSort('key')
            ->jsonPaginate()
            ->appends($request->query());

        return BlueprintResource::collection($blueprints);
    }

    #[OA\Get(
        path: '/api/blueprints/{blueprint}',
        description: 'Returns full detail for a single crafting blueprint, including ingredients, crafted item, dismantle returns, and associated missions. Scoped to the requested or default game version.',
        summary: 'Get Game Blueprint Detail',
        tags: ['In-Game', 'Blueprints'],
        parameters: [
            new OA\Parameter(
                name: 'blueprint',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Blueprint UUID',
                    type: 'string',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Blueprint detail',
                content: new OA\JsonContent(ref: '#/components/schemas/blueprint')
            ),
            new OA\Response(
                response: 404,
                description: 'Blueprint not found for the requested game version'
            ),
        ]
    )]
    public function show(Blueprint $blueprint): BlueprintResource
    {
        $blueprintData = BlueprintData::query()
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->where('blueprint_id', $blueprint->id)
            ->with(['blueprint', 'gameVersion', 'outputItem', 'dismantleReturns', 'ingredients', 'missions.mission'])
            ->withCount('missions')
            ->first();

        if ($blueprintData === null) {
            throw new NotFoundHttpException('No Blueprint found for the requested game version.');
        }

        return new BlueprintResource($blueprintData);
    }

    #[OA\Get(
        path: '/api/blueprints/filters',
        description: 'Returns available filter facets for blueprints (crafted item types, ingredient and resource UUIDs), optionally scoped to the requested or default game version.',
        summary: 'Get Blueprint Filter Options',
        tags: ['In-Game', 'Blueprints'],
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
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/filter_value')
                                ),
                                new OA\Property(
                                    property: 'ingredient.uuid',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/filter_value')
                                ),
                                new OA\Property(
                                    property: 'resource.uuid',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/filter_value')
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function filters(Request $request): JsonResponse
    {
        $versionCode = $this->gameVersionCode() ?? $this->gameVersion()->code;

        $resolver = function () use ($request, $versionCode): array {
            $out = [];

            $typeExpr = $this->jsonExpression('Output.Type');

            $typeRows = QueryBuilder::for(BlueprintData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
                ->select([
                    DB::raw("{$typeExpr} as value"),
                    DB::raw('count(*) as count'),
                ])
                ->groupByRaw($typeExpr)
                ->orderByRaw($typeExpr)
                ->get();

            $out['output.type'] = FilterValues::fromRows(
                $typeRows,
                labelResolver: [ItemTypeLabel::class, 'resolve'],
            );

            $ingredientRows = QueryBuilder::for(BlueprintData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
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

            $out['ingredient.uuid'] = FilterValues::fromRows($ingredientRows);

            $combined = [];
            foreach ($ingredientRows as $row) {
                $combined[$row->value] = [
                    'value' => $row->value,
                    'label' => $row->label,
                    'count' => (int) $row->count,
                ];
            }

            $dismantleRows = QueryBuilder::for(BlueprintData::class, $request)
                ->forRequestedOrDefaultVersion($versionCode)
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

            $out['resource.uuid'] = FilterValues::fromRows(
                collect($combined)->map(static fn (array $entry): object => (object) $entry)
            );

            return $out;
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []))) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                FilterCache::NAMESPACE_BLUEPRINTS,
                FilterCache::blueprintsKey($versionCode),
                $resolver
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
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
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::scope('query', 'searchOutput'),
            AllowedFilter::callback('output.uuid', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->forOutputItemUuid($value);
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
                $resourceTypeUuids = match (true) {
                    is_array($value) => array_values(array_unique(array_filter(
                        array_map(static fn (mixed $entry): string => trim((string) $entry), $value),
                        static fn (string $entry): bool => $entry !== '',
                    ))),
                    is_string($value) => array_values(array_unique(array_filter(
                        array_map(static fn (string $entry): string => trim($entry), explode(',', $value)),
                        static fn (string $entry): bool => $entry !== '',
                    ))),
                    default => [],
                };

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
                $resourceTypeUuids = match (true) {
                    is_array($value) => array_values(array_unique(array_filter(
                        array_map(static fn (mixed $entry): string => trim((string) $entry), $value),
                        static fn (string $entry): bool => $entry !== '',
                    ))),
                    is_string($value) => array_values(array_unique(array_filter(
                        array_map(static fn (string $entry): string => trim($entry), explode(',', $value)),
                        static fn (string $entry): bool => $entry !== '',
                    ))),
                    default => [],
                };

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
            AllowedSort::callback('ingredient_count', function (Builder $query, bool $descending): void {
                $direction = $descending ? 'desc' : 'asc';

                $query->orderByRaw($this->ingredientCountSortExpression().' '.$direction.' nulls last');
            }),
            AllowedSort::callback('unlocking_missions_count', function (Builder $query, bool $descending): void {
                $direction = $descending ? 'desc' : 'asc';

                $query->orderBy('missions_count', $direction);
            }),
        ];
    }

    private function ingredientCountSortExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return <<<'SQL'
(
    SELECT COUNT(*)
    FROM json_tree(game_blueprint_data.data, '$.Tiers[0].Requirements')
    WHERE json_tree.key = 'Kind'
      AND json_tree.value IN ('resource', 'item')
)
SQL;
        }

        return <<<'SQL'
jsonb_array_length(
    jsonb_path_query_array(
        game_blueprint_data.data,
        '$.Tiers[0].Requirements.**.Kind ? (@ == "resource" || @ == "item")'
    )
)
SQL;
    }
}
