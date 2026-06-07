<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Faction\FactionIndexResource;
use App\Http\Resources\Game\Faction\FactionResource;
use App\Models\Game\Faction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('factions')]
class FactionController extends Controller
{
    #[OA\Get(
        path: '/api/factions',
        operationId: 'listFactions',
        description: 'Returns paginated factions sorted by name by default. Factions hidden from the Delphi app are excluded.',
        summary: 'List Factions',
        tags: ['Factions'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[faction_type]', description: 'Category of faction. Example: `Lawful`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[has_reputation]', description: 'When true, only factions with a reputation system are returned. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[lawful]', description: 'When true, only lawful factions are returned. Example: `true`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_npc]', description: 'When true, only NPC-controlled factions are returned. Example: `false`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[hide_in_delphi_app]', description: 'When true, only factions hidden from the Delphi app are returned. Note: the index endpoint excludes hidden factions by default. Example: `false`', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[query]', description: 'Search factions by name or description. Example: `ArcCorp`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: name, faction_type.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: '-name'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of factions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/faction_index')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object'
                ),
            ),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $factions = QueryBuilder::for(Faction::class, $request)
            ->allowedFilters(...$this->allowedFilters())
            ->allowedSorts('name', 'faction_type')
            ->defaultSort('name')
            ->where('hide_in_delphi_app', false)
            ->jsonPaginate()
            ->appends($request->query());

        return FactionIndexResource::collection($factions)
            ->additional(['meta' => ['valid_relations' => []]]);
    }

    #[OA\Get(
        path: '/api/factions/{faction}',
        operationId: 'getFaction',
        description: 'Returns full details for a single faction, including reputation ladder with standings when the faction has a reputation system. Factions hidden from the Delphi app are excluded.',
        summary: 'Get Faction Detail',
        tags: ['Factions'],
        parameters: [
            new OA\Parameter(
                name: 'faction',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Faction UUID.',
                    type: 'string',
                    format: 'uuid',
                    example: '4e429470-4d4e-4c2b-a4ac-4de42ada16e0',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Faction detail',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/faction'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Faction not found', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ],
    )]
    public function show(Request $request, string $faction): FactionResource
    {
        $factionModel = Faction::query()
            ->where('uuid', $faction)
            ->where('hide_in_delphi_app', false)
            ->with([
                'reputationRef.factionScope.standings',
            ])
            ->first();

        if ($factionModel === null) {
            throw new NotFoundHttpException('No Faction found with the specified UUID.');
        }

        return new FactionResource($factionModel)->setValidIncludes([]);
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::exact('faction_type'),
            AllowedFilter::exact('has_reputation'),
            AllowedFilter::exact('lawful'),
            AllowedFilter::exact('is_npc'),
            AllowedFilter::exact('hide_in_delphi_app'),
            AllowedFilter::callback('query', static function (Builder $query, mixed $value): void {
                if (! is_string($value) || $value === '') {
                    return;
                }

                $query->where(static function (Builder $q) use ($value): void {
                    $like = DB::connection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                    $q->where('name', $like, "%{$value}%")->orWhere('description', $like, "%{$value}%");
                });
            }),
        ];
    }
}
