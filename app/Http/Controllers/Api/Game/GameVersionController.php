<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\Game\GameVersionResource;
use App\Models\Game\GameVersion;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameVersionController extends Controller
{
    /**
     * Build base query with filters and sorts for game versions.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(GameVersion::class, $request)
            ->allowedFilters(AllowedFilter::exact('code'), AllowedFilter::exact('channel'), AllowedFilter::exact('is_default'))
            ->allowedSorts('code', 'channel', 'released_at', AllowedSort::field('released_at', 'released_at'))
            ->defaultSort('-released_at');
    }

    #[OA\Get(
        path: '/api/game-versions',
        description: 'Returns paginated game versions sorted by release date (newest first by default). Useful for discovering available game versions for version-scoped API queries.',
        summary: 'List Game Versions',
        tags: ['In-Game', 'Game Version'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[code]', description: 'Filter by exact version code.', in: 'query', schema: new OA\Schema(type: 'string', example: '4.7.0-LIVE.11518367')),
            new OA\Parameter(name: 'filter[channel]', description: 'Filter by release channel (live, ptu, eptu).', in: 'query', schema: new OA\Schema(type: 'string', example: 'live')),
            new OA\Parameter(name: 'filter[is_default]', description: 'Filter by default status (1 or 0).', in: 'query', schema: new OA\Schema(type: 'integer', example: 1)),
            new OA\Parameter(name: 'sort', description: 'Sort by field. Prefix with - for descending. Supported: code, channel, released_at.', in: 'query', schema: new OA\Schema(type: 'string', example: '-released_at')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Game Versions',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/game_version'
                    )
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request)
            ->jsonPaginate()
            ->appends($request->query());

        return GameVersionResource::collection($query)->additional([
            'meta' => [
                'processed_at' => now()->toDateTimeString(),
                'valid_relations' => [],
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/game-versions/default',
        description: 'Returns the current default game version. This is the version used by default in version-scoped API queries (see the version query parameter on other endpoints).',
        summary: 'Get Default Game Version',
        tags: ['In-Game', 'Game Version'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The default game version',
                content: new OA\JsonContent(ref: '#/components/schemas/game_version')
            ),
            new OA\Response(
                response: 404,
                description: 'No default version found'
            ),
        ]
    )]
    public function default(): GameVersionResource
    {
        try {
            $version = GameVersion::where('is_default', true)->firstOrFail();
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No default game version found.');
        }

        return new GameVersionResource($version);
    }
}
