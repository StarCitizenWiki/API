<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\Faction\FactionIndexResource;
use App\Http\Resources\Game\Faction\FactionResource;
use App\Models\Game\Faction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FactionController extends Controller
{
    #[OA\Get(
        path: '/api/factions',
        description: 'Returns paginated factions with optional filtering.',
        summary: 'List Factions',
        tags: ['In-Game', 'Factions'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[faction_type]', in: 'query', schema: new OA\Schema(type: 'string', example: 'Lawful')),
            new OA\Parameter(name: 'filter[has_reputation]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[lawful]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[is_npc]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[hide_in_delphi_app]', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'filter[query]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(
                name: 'sort',
                description: 'Sort field. Prefix with "-" for descending. Supported: name, faction_type.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'name'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of factions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/faction_index'),
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

        return FactionIndexResource::collection($factions);
    }

    #[OA\Get(
        path: '/api/factions/{faction}',
        description: 'Returns full details for a single faction, including reputation ladder when included.',
        summary: 'Get Faction Detail',
        tags: ['In-Game', 'Factions'],
        parameters: [
            new OA\Parameter(
                name: 'faction',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Faction UUID',
                    type: 'string',
                    format: 'uuid',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/include'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Faction detail',
                content: new OA\JsonContent(ref: '#/components/schemas/faction'),
            ),
            new OA\Response(response: 404, description: 'Faction not found'),
        ],
    )]
    public function show(Request $request, string $faction): FactionResource
    {
        $factionModel = Faction::query()
            ->where('uuid', $faction)
            ->where('hide_in_delphi_app', false)
            ->with([
                'reputationRef.scope.standings',
            ])
            ->first();

        if ($factionModel === null) {
            throw new NotFoundHttpException('No Faction found with the specified UUID.');
        }

        return new FactionResource($factionModel);
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
                    $normalized = mb_strtolower($value);
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$normalized}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$normalized}%"]);
                });
            }),
        ];
    }
}
