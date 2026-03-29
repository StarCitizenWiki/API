<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Api\Game\Concerns\FiltersJsonColumns;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\BlueprintIndexRequest;
use App\Http\Resources\Game\Blueprint\BlueprintResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\ResourceType;
use Illuminate\Database\Eloquent\Builder;
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
        description: 'Returns paginated blueprints for the requested or default game version.',
        summary: 'List Game Blueprints',
        tags: ['In-Game', 'Blueprints'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: craft_time_seconds, ingredient_count.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '-craft_time_seconds')
            ),
            new OA\Parameter(name: 'filter[query]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[output.uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'filter[output.name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[output.class]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[output.type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[default]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(
                name: 'filter[ingredient]',
                description: 'Matches ingredient resource type by name, key, or UUID before filtering blueprints.',
                in: 'query',
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(name: 'filter[ingredient.uuid]', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
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
    public function index(BlueprintIndexRequest $request): AnonymousResourceCollection
    {
        $blueprints = $this->buildIndexQuery($request)
            ->with(['blueprint', 'gameVersion'])
            ->defaultSort('key')
            ->jsonPaginate()
            ->appends($request->query());

        return BlueprintResource::collection($blueprints);
    }

    #[OA\Get(
        path: '/api/blueprints/{blueprint}',
        description: 'Returns blueprint detail for the requested or default game version, including raw blueprint tiers.',
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
            ->with(['blueprint', 'gameVersion', 'outputItem'])
            ->first();

        if ($blueprintData === null) {
            throw new NotFoundHttpException('No Blueprint found for the requested game version.');
        }

        return new BlueprintResource($blueprintData);
    }

    /**
     * Build base query with allowed filters for blueprint listing.
     */
    private function buildIndexQuery(BlueprintIndexRequest $request): QueryBuilder
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
                $this->applyJsonFilter($query, 'output.type', $value, 'text');
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

                $matchingIngredientUuids = ResourceType::query()
                    ->matchingLookup($value)
                    ->pluck('uuid')
                    ->all();

                if ($matchingIngredientUuids === []) {
                    $query->whereRaw('0 = 1');

                    return;
                }

                $query->consumesAnyResourceTypes($matchingIngredientUuids);
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
        ];
    }

    private function ingredientCountSortExpression(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return <<<'SQL'
(
    SELECT COUNT(*)
    FROM json_tree(game_blueprint_data.data, '$.tiers[0].requirements')
    WHERE json_tree.key = 'kind'
      AND json_tree.value IN ('resource', 'item')
)
SQL;
        }

        return <<<'SQL'
jsonb_array_length(
    jsonb_path_query_array(
        game_blueprint_data.data,
        '$.tiers[0].requirements.**.kind ? (@ == "resource" || @ == "item")'
    )
)
SQL;
    }
}
