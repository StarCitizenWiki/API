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
        description: 'Returns paginated missions for the requested or default game version, excluding unreleased and work-in-progress by default.',
        summary: 'List Game Missions',
        tags: ['In-Game', 'Missions'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/include'),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: title, mission_giver, rank_index, reward_min, reward_max, time_to_complete_minutes.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'title')
            ),
            new OA\Parameter(name: 'filter[mission_giver]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[faction]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[star_system]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[illegal]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[shareable]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[once_only]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[available_in_prison]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_combat]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_defend_objective]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[rank_index]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[has_prerequisites]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[min_enemies]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[max_enemies]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[reward_min]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[reward_max]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[title]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[description]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[query]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[include_unreleased]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[reward_scope]', in: 'query', description: 'Mission category scope. Values: Bounty Hunter, Hauling, Security, Assassination, Mining, Salvage, Investigation, Recovery, Other', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[has_blueprints]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[reputation_scope]', in: 'query', description: 'Reputation reward scope from ReputationGained data. Values: FactionReputation, Hauling, Affinity, Security, Wikelo, ShipCombat_HeadHunters, BountyHunter_BountyHuntersGuild, Assassination, HiredMuscle, BountyHunter', schema: new OA\Schema(type: 'string')),
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
        $includeUnreleased = filter_var(
            $request->input('filter.include_unreleased', false),
            FILTER_VALIDATE_BOOLEAN,
        );

        $missions = $this->buildIndexQuery($request, $includeUnreleased)
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

        return MissionIndexResource::collection($missions);
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
                    description: 'Mission UUID',
                    type: 'string',
                    format: 'uuid',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(ref: '#/components/parameters/include'),
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
            ->first();

        if ($missionModel === null) {
            throw new NotFoundHttpException('No Mission found with the specified UUID.');
        }

        $missionData = MissionData::query()
            ->forRequestedOrDefaultVersion($versionCode)
            ->where('mission_id', $missionModel->id)
            ->where('not_for_release', false)
            ->where('work_in_progress', false)
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

        return new MissionResource($missionData);
    }

    #[OA\Get(
        path: '/api/missions/filters',
        description: 'Returns available filter facets for missions, scoped to the requested or default game version. Excludes unreleased and WIP missions by default.',
        summary: 'Get Mission Filter Options',
        tags: ['In-Game', 'Missions'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(name: 'filter[include_unreleased]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[mission_giver]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[faction]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[star_system]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[illegal]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[shareable]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_combat]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[has_defend_objective]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[rank_index]', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'filter[has_prerequisites]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[reward_scope]', in: 'query', description: 'Mission category scope. Values: Bounty Hunter, Hauling, Security, Assassination, Mining, Salvage, Investigation, Recovery, Other', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[has_blueprints]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[reputation_scope]', in: 'query', description: 'Reputation reward scope from ReputationGained data', schema: new OA\Schema(type: 'string')),
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
        $includeUnreleased = filter_var(
            $request->input('filter.include_unreleased', false),
            FILTER_VALIDATE_BOOLEAN,
        );

        $resolver = function () use ($request, $versionCode, $includeUnreleased): array {
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
                $q = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                    ->allowedFilters(...$this->allowedFilters($includeUnreleased));

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

            $factionQuery = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                ->allowedFilters(...$this->allowedFilters($includeUnreleased))
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

            $starSystemQuery = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                ->allowedFilters(...$this->allowedFilters($includeUnreleased))
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

            $starSystemRows = $starSystemQuery
                ->select([
                    DB::raw("regexp_replace(sld.system, ' System$', '') as value"),
                    DB::raw('count(distinct game_mission_data.id) as count'),
                ])
                ->whereNotNull('sld.system')
                ->groupByRaw("regexp_replace(sld.system, ' System$', '')")
                ->orderByRaw("regexp_replace(sld.system, ' System$', '')")
                ->get();

            $out['star_system'] = FilterValues::fromRows($starSystemRows);

            $prereqQuery = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                ->allowedFilters(...$this->allowedFilters($includeUnreleased));

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

            $scopeQuery = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                ->allowedFilters(...$this->allowedFilters($includeUnreleased));

            $scopeRows = $scopeQuery
                ->select([
                    DB::raw('game_mission_data.reward_scope as value'),
                    DB::raw('count(*) as count'),
                ])
                ->groupByRaw('game_mission_data.reward_scope')
                ->orderByRaw('game_mission_data.reward_scope IS NULL, game_mission_data.reward_scope')
                ->get();

            $out['reward_scope'] = FilterValues::fromRows($scopeRows);

            $blueprintQuery = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                ->allowedFilters(...$this->allowedFilters($includeUnreleased));

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

            $blueprintNameQuery = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                ->allowedFilters(...$this->allowedFilters($includeUnreleased))
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

            $repScopeQuery = $this->buildFiltersBaseQuery($request, $versionCode, $includeUnreleased)
                ->allowedFilters(...$this->allowedFilters($includeUnreleased));

            $repScopeRows = $repScopeQuery
                ->select([
                    DB::raw("elem->>'Scope' as value"),
                    DB::raw('count(distinct game_mission_data.id) as count'),
                ])
                ->fromRaw('game_mission_data, jsonb_array_elements(game_mission_data.data->\'ReputationGained\') elem')
                ->whereNotNull('game_mission_data.data')
                ->groupByRaw("elem->>'Scope'")
                ->orderByRaw("elem->>'Scope'")
                ->get();

            $out['reputation_scope'] = FilterValues::fromRows($repScopeRows);

            return $out;
        };

        if (FilterCache::hasEffectiveFilters($request->input('filter', []), ['include_unreleased'])) {
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

    private function buildIndexQuery(Request $request, bool $includeUnreleased = false): QueryBuilder
    {
        return QueryBuilder::for(MissionData::class, $request)
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->excludeUnreleased(! $includeUnreleased)
            ->allowedFilters(...$this->allowedFilters($includeUnreleased))
            ->allowedSorts(...$this->allowedSorts());
    }

    private function buildFiltersBaseQuery(Request $request, string $versionCode, bool $includeUnreleased = false): QueryBuilder
    {
        return QueryBuilder::for(MissionData::class, $request)
            ->forRequestedOrDefaultVersion($versionCode)
            ->excludeUnreleased(! $includeUnreleased);
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(bool $includeUnreleased = false): array
    {
        $versionCode = $this->gameVersionCode();

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
            AllowedFilter::partial('title'),
            AllowedFilter::partial('description'),
            AllowedFilter::callback('query', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->where(static function (Builder $q) use ($value): void {
                    $normalized = mb_strtolower($value);
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$normalized}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$normalized}%"])
                        ->orWhereRaw('LOWER(debug_name) LIKE ?', ["%{$normalized}%"]);
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
            AllowedFilter::callback('reputation_scope', static function (Builder $query, mixed $value): void {
                $values = is_array($value) ? $value : [$value];
                $placeholders = implode(', ', array_fill(0, count($values), '?'));

                $query->whereRaw(
                    "EXISTS (SELECT 1 FROM jsonb_array_elements(game_mission_data.data->'ReputationGained') elem WHERE elem->>'Scope' IN ({$placeholders}))",
                    $values,
                );
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
            AllowedSort::callback('max_players_per_instance', static function (Builder $query, bool $descending): void {
                $direction = $descending ? 'desc' : 'asc';
                $query->orderByRaw("(data->>'MaxPlayersPerInstance')::numeric {$direction}");
            }),
            AllowedSort::callback('reputation_amount', static function (Builder $query, bool $descending): void {
                $direction = $descending ? 'desc' : 'asc';
                $query->orderByRaw("(data->'ReputationGained'->0->>'Amount')::numeric {$direction} nulls last");
            }),
        ];
    }
}
