<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen;

use App\Http\Controllers\Controller;
use App\Http\Resources\StarCitizen\StatResource;
use App\Models\StarCitizen\Stat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

class StatController extends Controller
{
    #[OA\Get(
        path: '/api/v2/stats/latest',
        tags: ['Stats', 'RSI-Website'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Latest statistics',
                content: new OA\JsonContent(ref: '#/components/schemas/stat_v2')
            ),
        ]
    )]
    public function latest(Request $request): StatResource
    {
        $stat = QueryBuilder::for(Stat::class, $request)->orderByDesc('created_at')->first();

        return new StatResource($stat);
    }

    #[OA\Get(
        path: '/api/v2/stats',
        tags: ['Stats', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of stats',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/stat_v2')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Stat::class, $request)
            ->orderByDesc('created_at')
            ->paginate()
            ->appends(request()->query());

        return StatResource::collection($query);
    }
}
