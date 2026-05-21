<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Http\Resources\StarCitizen\StatResource;
use App\Models\StarCitizen\Stat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

#[CacheTag('stats')]
class StatController extends Controller
{
    #[OA\Get(
        path: '/api/stats/latest',
        operationId: 'getLatestStats',
        description: 'Get the most recent Star Citizen crowdfunding statistics snapshot, including funds raised (USD), fan count, and fleet size.',
        summary: 'Latest Crowdfunding Statistics',
        tags: ['Stats'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Latest statistics',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/stat'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No statistics found.',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response'),
            ),
        ]
    )]
    public function latest(Request $request): StatResource
    {
        $stat = QueryBuilder::for(Stat::class, $request)->orderByDesc('created_at')->first();

        return new StatResource($stat);
    }

    #[OA\Get(
        path: '/api/stats',
        operationId: 'listStats',
        description: 'Get paginated historical Star Citizen crowdfunding statistics, ordered by most recent first. Supports page-based pagination.',
        summary: 'Paginated Historical Statistics',
        tags: ['Stats'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of stats',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/stat')),
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
        $query = QueryBuilder::for(Stat::class, $request)
            ->orderByDesc('created_at')
            ->jsonPaginate()
            ->appends(request()->query());

        return StatResource::collection($query);
    }
}
