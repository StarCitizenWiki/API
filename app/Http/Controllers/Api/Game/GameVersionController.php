<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
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

#[CacheTag('gameversions')]
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
        operationId: 'listGameVersions',
        description: 'Returns paginated game versions sorted by release date (newest first by default). Useful for discovering available game versions for version-scoped API queries.',
        summary: 'List Game Versions',
        tags: ['Game Versions'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[code]', description: 'Filter by exact version code. Example: `4.8.0-LIVE.11825000`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[channel]', description: 'Filter by release channel (live, ptu, eptu), lowercase. Example: `live`', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[is_default]', description: 'Filter by default status. Example: `1`', in: 'query', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'sort', description: 'Sort by field. Prefix with - for descending. Supported: code, channel, released_at.', in: 'query', schema: new OA\Schema(type: 'string', example: '-released_at')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of Game Versions',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/game_version')),
                        new OA\Property(property: 'links', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_links')]),
                        new OA\Property(property: 'meta', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_meta')]),
                    ],
                    type: 'object'
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
        operationId: 'getDefaultGameVersion',
        description: 'Returns the current default game version. This is the version used by default in version-scoped API queries (see the version query parameter on other endpoints).',
        summary: 'Get Default Game Version',
        tags: ['Game Versions'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The default game version',
                content: new OA\JsonContent(
                    examples: [
                        new OA\Examples(
                            example: 'default_game_version',
                            summary: 'Current default version',
                            value: ['data' => ['code' => '4.8.0-LIVE.11825000', 'channel' => 'live', 'is_default' => true]],
                        ),
                    ],
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/game_version'),
                    ],
                    type: 'object',
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No default version found.',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response'),
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

    #[OA\Get(
        path: '/api/game-versions/{identifier}',
        operationId: 'getGameVersion',
        description: 'Retrieve a specific game version by its code (case-insensitive). Game versions are used to scope version-aware data endpoints via the `version` query parameter.',
        summary: 'Get Game Version',
        tags: ['Game Versions'],
        parameters: [
            new OA\Parameter(name: 'identifier', description: 'Game version code (case-insensitive).', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '4.8.0-LIVE.11825000')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A game version',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/game_version'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Game version not found.',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response'),
            ),
        ]
    )]
    public function show(string $identifier): GameVersionResource
    {
        $version = GameVersion::findByCode($identifier, fail: true);

        return new GameVersionResource($version);
    }
}
