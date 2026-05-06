<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\Mission\MissionIndexResource;
use App\Http\Resources\Game\Mission\MissionResource;
use App\Models\Game\Faction;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
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

class MissionController extends Controller
{
    use ResolvesGameVersion;

    #[OA\Get(
        path: '/api/missions',
        description: 'Returns paginated missions for the requested or default game version. Results are grouped by title when no filters or sorts are active. Includes mission, game version, faction, and blueprint relationships.',
        summary: 'List Game Missions',
        tags: ['In-Game', 'Missions'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: title, mission_giver, rank_index, reward_min, reward_max, time_to_complete_minutes, max_players_per_instance, reputation_amount.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'title')
            ),
            new OA\Parameter(name: 'filter[mission_giver]', description: 'Exact match on the mission giver NPC name. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Citizens for Prosperity')),
            new OA\Parameter(name: 'filter[faction]', description: 'Filter by faction name. Accepts comma-separated values. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'ArcCorp')),
            new OA\Parameter(name: 'filter[star_system]', description: 'Filter by star system name. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[illegal]', description: 'Filter for missions marked as illegal', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[shareable]', description: 'Filter for shareable missions', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[once_only]', description: 'Filter for one-time-only missions', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[available_in_prison]', description: 'Filter for missions available while in prison', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_combat]', description: 'Filter for missions involving combat encounters', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_defend_objective]', description: 'Filter for missions with a defend objective', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[rank_index]', description: 'Filter by mission difficulty rank', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[has_prerequisites]', description: 'Filter for missions that have prerequisite requirements', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[min_enemies]', description: 'Minimum enemy count threshold', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[max_enemies]', description: 'Maximum enemy count threshold', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[reward_min]', description: 'Minimum reward in aUEC', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[reward_max]', description: 'Maximum reward in aUEC', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[title]', description: 'Partial match on mission title', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[description]', description: 'Partial match on mission description', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search across title, description, and debug name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[reward_scope]', description: 'Mission category scope. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Bounty Hunter')),
            new OA\Parameter(name: 'filter[has_blueprints]', description: 'Filter for missions that reward blueprints', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[reputation_scope]', description: 'Reputation reward scope from ReputationGained data. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'FactionReputation')),
            new OA\Parameter(name: 'filter[blueprint_name]', description: 'Filter by crafted item name from mission blueprint rewards. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'S-38 Pistol')),
            new OA\Parameter(name: 'filter[location]', description: 'Filter by starmap location UUID', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of missions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/mission_index')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $hasActiveFiltersOrSorts = $request->filled('filter') || $request->filled('sort');
        $grouped = ! $hasActiveFiltersOrSorts;

        $missions = $this->buildIndexQuery($request, $grouped)
            ->with(['mission', 'gameVersion', 'faction', 'blueprints.blueprint'])
            ->withCount('prerequisiteGroups')
            ->defaultSort('title')
            ->jsonPaginate()
            ->appends($request->query());

        $factionUuids = $missions->getCollection()
            ->pluck('data')
            ->filter()
            ->map(static fn ($data): ?array => $data->get('ReputationGained'))
            ->filter()
            ->flatten(1)
            ->filter(static fn (mixed $entry): bool => is_array($entry) && str_contains($entry['Faction'] ?? '', 'UNINITIALIZED'))
            ->map(static fn (array $entry): ?string => $entry['FactionUUID'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($factionUuids !== []) {
            MissionIndexResource::setFactionNameCache(
                Faction::query()->whereIn('uuid', $factionUuids)->pluck('name', 'uuid')->all(),
            );
        }

        return MissionIndexResource::collection($missions)
            ->additional(['meta' => ['valid_relations' => []]]);
    }

    #[OA\Get(
        path: '/api/missions/{mission}',
        description: 'Returns full details for a single mission, including chain relationships and associated items.',
        summary: 'Get Mission Detail',
        tags: ['In-Game', 'Missions'],
        parameters: [
            new OA\Parameter(
                name: 'mission',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Mission slug or UUID',
                    type: 'string',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Mission detail',
                content: new OA\JsonContent(ref: '#/components/schemas/mission')
            ),
            new OA\Response(response: 404, description: 'Mission not found'),
        ]
    )]
    public function show(Request $request, string $mission): MissionResource
    {
        $versionCode = $this->gameVersionCode();

        $missionModel = Mission::query()
            ->when(Str::isUuid($mission), fn (Builder $q) => $q->where('uuid', $mission))
            ->unless(Str::isUuid($mission), fn (Builder $q) => $q->where('slug', $mission))
            ->first();

        if ($missionModel === null) {
            throw new NotFoundHttpException('No Mission found with the specified UUID.');
        }

        $missionData = MissionData::query()
            ->forRequestedOrDefaultVersion($versionCode)
            ->where('mission_id', $missionModel->id)
            ->with([
                'mission',
                'gameVersion',
                'faction',
                'faction.reputationRef.factionScope.standings',
                'starmapLocations.location',
                'blueprints.blueprint',
                'prerequisiteGroups.missions.linkedMissionData.mission',
                'prerequisiteGroups.tags',
                'unlockGroups.missions.linkedMissionData.mission',
                'rewardItems.item',
            ])
            ->first();

        if ($missionData === null) {
            throw new NotFoundHttpException('No Mission found for the requested game version.');
        }

        return (new MissionResource($missionData))
            ->setValidIncludes([]);
    }

    #[OA\Get(
        path: '/api/missions/filters',
        description: 'Returns available filter facets for missions, scoped to the requested or default game version.',
        summary: 'Get Mission Filter Options',
        tags: ['In-Game', 'Missions'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[include_unreleased]', description: 'Include unreleased and work-in-progress missions', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mission_giver]', description: 'Exact match on the mission giver NPC name. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[faction]', description: 'Filter by faction name. Accepts comma-separated values. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[star_system]', description: 'Filter by star system name. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[illegal]', description: 'Filter for missions marked as illegal', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[shareable]', description: 'Filter for shareable missions', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[once_only]', description: 'Filter for one-time-only missions', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[available_in_prison]', description: 'Filter for missions available while in prison', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_combat]', description: 'Filter for missions involving combat encounters', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_defend_objective]', description: 'Filter for missions with a defend objective', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[rank_index]', description: 'Filter by mission difficulty rank', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[has_prerequisites]', description: 'Filter for missions that have prerequisite requirements', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[min_enemies]', description: 'Minimum enemy count threshold', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[max_enemies]', description: 'Maximum enemy count threshold', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[reward_min]', description: 'Minimum reward in aUEC', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[reward_max]', description: 'Maximum reward in aUEC', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[title]', description: 'Partial match on mission title', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[description]', description: 'Partial match on mission description', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', description: 'Search across title, description, and debug name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[reward_scope]', description: 'Mission category scope. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[has_blueprints]', description: 'Filter for missions that reward blueprints', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[blueprint_name]', description: 'Filter by crafted item name from mission blueprint rewards. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[reputation_scope]', description: 'Reputation reward scope from ReputationGained data. (see GET /api/missions/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[location]', description: 'Filter by starmap location UUID', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Filter facets for missions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'mission_giver', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'star_system', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'faction', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'has_combat', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'has_defend_objective', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'rank_index', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'illegal', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'shareable', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'has_prerequisites', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'reward_scope', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'has_blueprints', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'reputation_scope', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'blueprint_name', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
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
        $versionCode = $this->gameVersionCode() ?? $this->gameVersion()->code;

        $resolver = function () use ($request, $versionCode): array {
            $out = [];

            $simpleFacets = [
                'mission_giver' => [
                    'expr' => 'game_mission_data.mission_giver',
                ],
                'has_combat' => [
                    'expr' => 'game_mission_data.has_combat',
                    'cast' => static fn ($value): ?bool => $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                ],
                'has_defend_objective' => [
                    'expr' => 'game_mission_data.has_defend_objective',
                    'cast' => static fn ($value): ?bool => $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
                ],
                'rank_index' => [
                    'expr' => 'game_mission_data.rank_index',
                    'cast' => static fn ($value) => $value === null ? null : (int) $value,
                ],
                'illegal' => [
                    'expr' => 'game_mission_data.illegal',
                ],
                'shareable' => [
                    'expr' => 'game_mission_data.shareable',
                ],
            ];

            foreach ($simpleFacets as $key => $facet) {
                $q = $this->buildFiltersBaseQuery($request, $versionCode)
                    ->allowedFilters(...$this->allowedFilters());

                $expr = $facet['expr'];

                $rows = $q
                    ->select([
                        DB::raw("{$expr} as value"),
                        DB::raw('count(*) as count'),
                    ])
                    ->groupByRaw($expr)
                    ->orderByRaw("{$expr} IS NULL, {$expr}")
                    ->get();

                $out[$key] = FilterValues::fromRows($rows, $facet['cast'] ?? null);
            }

            $factionQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters())
                ->leftJoin('game_factions', 'game_mission_data.faction_id', '=', 'game_factions.id');

            $factionRows = $factionQuery
                ->select([
                    DB::raw('game_factions.name as value'),
                    DB::raw('count(*) as count'),
                ])
                ->groupByRaw('game_factions.name')
                ->orderByRaw('game_factions.name IS NULL, game_factions.name')
                ->get();

            $out['faction'] = FilterValues::fromRows($factionRows);

            $starSystemQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters())
                ->join(
                    'game_mission_data_starmap_location as mdsl',
                    'game_mission_data.id',
                    '=',
                    'mdsl.mission_data_id',
                )
                ->join(
                    'game_starmap_location_data as sld',
                    'mdsl.starmap_location_data_id',
                    '=',
                    'sld.id',
                );

            $systemExpr = $this->stripSystemSuffix('sld.system');
            $starSystemRows = $starSystemQuery
                ->select([
                    DB::raw("{$systemExpr} as value"),
                    DB::raw('count(distinct game_mission_data.id) as count'),
                ])
                ->whereNotNull('sld.system')
                ->groupByRaw($systemExpr)
                ->orderByRaw($systemExpr)
                ->get();

            $out['star_system'] = FilterValues::fromRows($starSystemRows);

            $prereqQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters());

            $prereqRows = $prereqQuery
                ->select([
                    DB::raw('EXISTS (
                        SELECT 1 FROM game_mission_data_prerequisite_groups pg
                        WHERE pg.mission_data_id = game_mission_data.id
                    ) as value'),
                    DB::raw('count(*) as count'),
                ])
                ->groupByRaw('EXISTS (
                    SELECT 1 FROM game_mission_data_prerequisite_groups pg
                    WHERE pg.mission_data_id = game_mission_data.id
                )')
                ->get();

            $out['has_prerequisites'] = FilterValues::fromRows($prereqRows, static fn ($value) => $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));

            $scopeQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters());

            $scopeRows = $scopeQuery
                ->select([
                    DB::raw('game_mission_data.reward_scope as value'),
                    DB::raw('count(*) as count'),
                ])
                ->groupByRaw('game_mission_data.reward_scope')
                ->orderByRaw('game_mission_data.reward_scope IS NULL, game_mission_data.reward_scope')
                ->get();

            $out['reward_scope'] = FilterValues::fromRows($scopeRows);

            $blueprintQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters());

            $blueprintRows = $blueprintQuery
                ->select([
                    DB::raw('EXISTS (
                        SELECT 1 FROM game_mission_data_blueprint mb
                        WHERE mb.mission_data_id = game_mission_data.id
                    ) as value'),
                    DB::raw('count(*) as count'),
                ])
                ->groupByRaw('EXISTS (
                    SELECT 1 FROM game_mission_data_blueprint mb
                    WHERE mb.mission_data_id = game_mission_data.id
                )')
                ->get();

            $out['has_blueprints'] = FilterValues::fromRows($blueprintRows, static fn ($value) => $value === null ? null : filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE));

            $blueprintNameQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters())
                ->join('game_mission_data_blueprint as mdb', 'game_mission_data.id', '=', 'mdb.mission_data_id')
                ->join('game_blueprint_data as bd', 'mdb.blueprint_data_id', '=', 'bd.id');

            $blueprintNameRows = $blueprintNameQuery
                ->select([
                    DB::raw('bd.output_name as value'),
                    DB::raw('count(distinct game_mission_data.id) as count'),
                ])
                ->whereNotNull('bd.output_name')
                ->groupByRaw('bd.output_name')
                ->orderByRaw('bd.output_name')
                ->get();

            $out['blueprint_name'] = FilterValues::fromRows($blueprintNameRows);

            $repScopeQuery = $this->buildFiltersBaseQuery($request, $versionCode)
                ->allowedFilters(...$this->allowedFilters());

            $scopeExpr = $this->reputationScopeExpression();
            $scopeFrom = $this->reputationScopeFromExpression();
            $repScopeRows = $repScopeQuery
                ->select([
                    DB::raw("{$scopeExpr} as value"),
                    DB::raw('count(distinct game_mission_data.id) as count'),
                ])
                ->fromRaw($scopeFrom)
                ->whereNotNull('game_mission_data.data')
                ->groupByRaw($scopeExpr)
                ->orderByRaw($scopeExpr)
                ->get();

            $out['reputation_scope'] = FilterValues::fromRows($repScopeRows);

            return $out;
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []))) {
            $filters = $resolver();
        } else {
            $filters = FilterCache::rememberForever(
                FilterCache::NAMESPACE_MISSIONS,
                FilterCache::missionsKey($versionCode),
                $resolver,
            );
        }

        return response()->json([
            'filters' => $filters,
        ]);
    }

    private function buildIndexQuery(Request $request, bool $grouped = true): QueryBuilder
    {
        return QueryBuilder::for(MissionData::class, $request)
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->when($grouped, fn (Builder $q) => $q->groupByTitle($this->gameVersion()->id)->withGroupedAggregates())
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts(...$this->allowedSorts());
    }

    private function buildFiltersBaseQuery(Request $request, string $versionCode): QueryBuilder
    {
        return QueryBuilder::for(MissionData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode);
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::exact('mission_giver'),
            AllowedFilter::callback('faction', static function (Builder $query, mixed $value): void {
                $values = is_array($value) ? $value : [$value];

                $query->whereHas('faction', static function (Builder $q) use ($values): void {
                    $q->whereIn('name', $values);
                });
            }),
            AllowedFilter::callback('star_system', static function (Builder $query, mixed $value): void {
                $query->whereHas('starmapLocations', static function (Builder $q) use ($value): void {
                    $q->where('system', $value)
                        ->orWhere('system', $value.' System');
                });
            }),
            AllowedFilter::exact('illegal'),
            AllowedFilter::exact('shareable'),
            AllowedFilter::exact('once_only'),
            AllowedFilter::exact('available_in_prison'),
            AllowedFilter::exact('has_combat'),
            AllowedFilter::exact('has_defend_objective'),
            AllowedFilter::exact('rank_index'),
            AllowedFilter::callback('has_prerequisites', static function (Builder $query, mixed $value): void {
                $has = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                if ($has) {
                    $query->whereHas('prerequisiteGroups');
                } else {
                    $query->whereDoesntHave('prerequisiteGroups');
                }
            }),
            AllowedFilter::callback('has_blueprints', static function (Builder $query, mixed $value): void {
                $has = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                if ($has) {
                    $query->whereHas('blueprints');
                } else {
                    $query->whereDoesntHave('blueprints');
                }
            }),
            AllowedFilter::callback('blueprint_name', static function (Builder $query, mixed $value): void {
                $query->whereHas('blueprints', static function (Builder $q) use ($value): void {
                    $q->where('game_blueprint_data.output_name', $value);
                });
            }),
            AllowedFilter::callback('min_enemies', static function (Builder $query, mixed $value): void {
                if (! is_numeric($value)) {
                    return;
                }

                $query->where('enemy_count_min', '>=', (int) $value);
            }),
            AllowedFilter::callback('max_enemies', static function (Builder $query, mixed $value): void {
                if (! is_numeric($value)) {
                    return;
                }

                $query->where('enemy_count_max', '<=', (int) $value);
            }),
            AllowedFilter::callback('reward_min', static function (Builder $query, mixed $value): void {
                if (! is_numeric($value)) {
                    return;
                }

                $query->where('reward_min', '>=', (int) $value);
            }),
            AllowedFilter::callback('reward_max', static function (Builder $query, mixed $value): void {
                if (! is_numeric($value)) {
                    return;
                }

                $query->where('reward_max', '<=', (int) $value);
            }),
            AllowedFilter::callback('title', static function (Builder $query, mixed $value): void {
                $query->whereRaw('LOWER(game_mission_data.title) LIKE ?', [sprintf('%%%s%%', mb_strtolower((string) $value))]);
            }),
            AllowedFilter::callback('description', static function (Builder $query, mixed $value): void {
                $query->whereRaw('LOWER(game_mission_data.description) LIKE ?', [sprintf('%%%s%%', mb_strtolower((string) $value))]);
            }),
            AllowedFilter::callback('query', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->where(static function (Builder $q) use ($value): void {
                    $normalized = mb_strtolower($value);
                    $q->whereRaw('LOWER(game_mission_data.title) LIKE ?', ["%{$normalized}%"])
                        ->orWhereRaw('LOWER(game_mission_data.description) LIKE ?', ["%{$normalized}%"])
                        ->orWhereRaw('LOWER(game_mission_data.debug_name) LIKE ?', ["%{$normalized}%"]);
                });
            }),
            AllowedFilter::callback('reward_scope', static function (Builder $query, mixed $value): void {
                $values = is_array($value) ? $value : [$value];
                $query->whereIn('game_mission_data.reward_scope', $values);
            }),
            AllowedFilter::callback('location', static function (Builder $query, mixed $value): void {
                $query->whereHas('starmapLocations', static function (Builder $q) use ($value): void {
                    $q->whereHas('location', static function (Builder $lq) use ($value): void {
                        $lq->where('uuid', $value);
                    });
                });
            }),
            AllowedFilter::callback('reputation_scope', function (Builder $query, mixed $value): void {
                $values = is_array($value) ? $value : [$value];
                $placeholders = implode(', ', array_fill(0, count($values), '?'));

                if (DB::connection()->getDriverName() === 'sqlite') {
                    $query->whereRaw(
                        "EXISTS (SELECT 1 FROM json_each(game_mission_data.data, '$.ReputationGained') elem WHERE json_extract(elem.value, '$.Scope') IN ({$placeholders}))",
                        $values,
                    );
                } else {
                    $query->whereRaw(
                        "EXISTS (SELECT 1 FROM jsonb_array_elements(game_mission_data.data->'ReputationGained') elem WHERE elem->>'Scope' IN ({$placeholders}))",
                        $values,
                    );
                }
            }),
        ];
    }

    /**
     * @return array<int, string|AllowedSort>
     */
    private function allowedSorts(): array
    {
        return [
            'title',
            'mission_giver',
            'rank_index',
            'reward_min',
            'reward_max',
            'time_to_complete_minutes',
            AllowedSort::callback('max_players_per_instance', function (Builder $query, bool $descending): void {
                $direction = $descending ? 'desc' : 'asc';

                if (DB::connection()->getDriverName() === 'sqlite') {
                    $query->orderByRaw("CAST(json_extract(data, '$.MaxPlayersPerInstance') AS REAL) {$direction}");
                } else {
                    $query->orderByRaw("(data->>'MaxPlayersPerInstance')::numeric {$direction}");
                }
            }),
            AllowedSort::callback('reputation_amount', function (Builder $query, bool $descending): void {
                $direction = $descending ? 'desc' : 'asc';

                if (DB::connection()->getDriverName() === 'sqlite') {
                    $query->orderByRaw("CAST(json_extract(data, '$.ReputationGained[0].Amount') AS REAL) IS NULL, CAST(json_extract(data, '$.ReputationGained[0].Amount') AS REAL) {$direction}");
                } else {
                    $query->orderByRaw("(data->'ReputationGained'->0->>'Amount')::numeric {$direction} nulls last");
                }
            }),
        ];
    }

    private function stripSystemSuffix(string $column): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "REPLACE({$column}, ' System', '')";
        }

        return "regexp_replace({$column}, ' System$', '')";
    }

    private function reputationScopeExpression(): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "json_extract(elem.value, '$.Scope')";
        }

        return "elem->>'Scope'";
    }

    private function reputationScopeFromExpression(): string
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return "game_mission_data, json_each(game_mission_data.data, '$.ReputationGained') elem";
        }

        return "game_mission_data, jsonb_array_elements(game_mission_data.data->'ReputationGained') elem";
    }
}
