<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Stat;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Models\StarCitizen\Stat\Stat;
use App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class StatController extends ApiController
{
    /**
     * StatsAPIController constructor.
     *
     * @param Request         $request
     * @param StatTransformer $transformer
     */
    public function __construct(Request $request, StatTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/stats/latest',
        tags: ['Stats', 'RSI-Website'],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/stat',
                response: 200,
                description: 'List of stats'
            )
        ]
    )]
    public function latest(): Response
    {
        $stat = Stat::query()->orderByDesc('created_at')->first();

        return $this->getResponse($stat);
    }

    #[OA\Get(
        path: '/api/stats',
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
                    items: new OA\Items(ref: '#/components/schemas/stat')
                )
            )
        ]
    )]
    public function index(): Response
    {
        $stats = Stat::query()->orderByDesc('created_at');

        return $this->getResponse($stats);
    }
}
