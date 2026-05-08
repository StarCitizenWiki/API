<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Http\Resources\Game\VersionChangelogResource;
use App\Http\Resources\Game\VersionDiffResource;
use App\Models\Game\GameVersion;
use App\Models\Game\VersionDiff;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

#[CacheTag('gameversions')]
class VersionChangelogController extends Controller
{
    #[OA\Get(
        path: '/api/game-versions/{version}/changelog',
        description: 'Returns the changelog summary between the specified version and its predecessor.',
        summary: 'Get Version Changelog',
        tags: ['In-Game', 'Game Version'],
        parameters: [
            new OA\Parameter(name: 'version', description: 'Version code', in: 'path', schema: new OA\Schema(type: 'string', example: '4.7.0-LIVE.11518367')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Version changelog summary',
                content: new OA\JsonContent(ref: '#/components/schemas/version_changelog')
            ),
            new OA\Response(response: 404, description: 'Version not found or no previous version'),
        ]
    )]
    public function show(string $version): VersionChangelogResource
    {
        $gameVersion = GameVersion::findByCode($version, fail: true);
        $previousVersion = $gameVersion->findPreviousVersion();
        abort_if($previousVersion === null, 404, 'No previous version found.');

        $changeCounts = VersionDiff::getChangeCounts($previousVersion->id, $gameVersion->id);

        return new VersionChangelogResource(
            [
                'from_version' => $previousVersion,
                'to_version' => $gameVersion,
            ],
            $changeCounts,
        );
    }

    #[OA\Get(
        path: '/api/game-versions/{version}/changelog/changes',
        description: 'Returns paginated diff entries for a version changelog. Filter by entity_type and change_type.',
        summary: 'Get Version Changelog Changes',
        tags: ['In-Game', 'Game Version'],
        parameters: [
            new OA\Parameter(name: 'version', description: 'Version code', in: 'path', schema: new OA\Schema(type: 'string', example: '4.7.0-LIVE.11518367')),
            new OA\Parameter(name: 'filter[entity_type]', description: 'Filter by entity type (item, vehicle)', in: 'query', schema: new OA\Schema(type: 'string', example: 'item')),
            new OA\Parameter(name: 'filter[change_type]', description: 'Filter by change type (added, removed, modified)', in: 'query', schema: new OA\Schema(type: 'string', example: 'modified')),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of changes',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/version_diff_entry')
                )
            ),
            new OA\Response(response: 404, description: 'Version not found or no previous version'),
        ]
    )]
    public function changes(string $version): AnonymousResourceCollection
    {
        $gameVersion = GameVersion::findByCode($version, fail: true);
        $previousVersion = $gameVersion->findPreviousVersion();
        abort_if($previousVersion === null, 404, 'No previous version found.');

        $query = QueryBuilder::for(VersionDiff::class)
            ->allowedFilters(AllowedFilter::exact('entity_type'), AllowedFilter::exact('change_type'))
            ->where('from_version_id', $previousVersion->id)
            ->where('to_version_id', $gameVersion->id)
            ->defaultSort('entity_type', 'change_type');

        return VersionDiffResource::collection(
            $query->jsonPaginate()->appends(request()->query())
        );
    }
}
