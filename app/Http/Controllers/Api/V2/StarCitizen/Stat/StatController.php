<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\StarCitizen\Stat;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Stat\StatResource;
use App\Models\StarCitizen\Stat\Stat;
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

class StatController extends AbstractApiV2Controller
{
    #[OA\Get(
        path: '/api/v2/stats/latest',
        tags: ['Stats', 'RSI-Website'],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/stat_v2',
                response: 200,
                description: 'List of stats'
            )
        ]
    )]
    public function latest(): AbstractBaseResource
    {
        $stat = QueryBuilder::for(Stat::class)->orderByDesc('created_at')->first();

        return new StatResource($stat);
    }

    #[OA\Get(
        path: '/api/v2/stats',
        tags: ['Stats', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of stats',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/stat_v2')
                )
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        return StatResource::collection(QueryBuilder::for(Stat::class)->orderByDesc('created_at'));
    }
}
