<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\StarCitizen\Starmap\StarsystemResource;
use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;

class StarsystemController extends Controller
{
    #[OA\Get(
        path: '/api/starsystems',
        description: 'Returns paginated starsystems, optionally including related resources.',
        summary: 'Starmap Starsystems Overview',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(
                name: 'include',
                description: 'Include additional relationships (affiliation, celestialObjects).',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                explode: false,
                allowReserved: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Starsystems',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/starsystem')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Starsystem::class, $request)
            ->allowedIncludes([])
            ->paginate()
            ->appends(request()->query());

        return StarsystemResource::collection($query);
    }

    #[OA\Get(
        path: '/api/starsystems/{code}',
        description: 'Retrieve a starsystem by code or identifier, with optional includes.',
        summary: 'Starsystem Detail',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Starsystem code or identifier (e.g. SOL)',
                    type: 'string',
                ),
            ),
            new OA\Parameter(
                name: 'include',
                description: 'Include additional relationships (affiliation, celestialObjects).',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                explode: false,
                allowReserved: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Starsystem',
                content: new OA\JsonContent(ref: '#/components/schemas/starsystem')
            ),
            new OA\Response(
                response: 404,
                description: 'No Starsystem with specified code found.'
            ),
        ]
    )]
    public function show(Request $request): StarsystemResource
    {
        ['code' => $code] = Validator::validate(
            [
                'code' => $request->code,
            ],
            [
                'code' => 'required|string|min:1|max:255',
            ]
        );

        $code = mb_strtoupper(urldecode($code));

        /** @var Starsystem $starsystem */
        $starsystem = QueryBuilder::for(Starsystem::class, $request)
            ->where('code', $code)
            ->orWhere('cig_id', $code)
            ->orWhere('name', 'LIKE', "%$code%")
            ->allowedIncludes(StarsystemResource::validIncludes())
            ->firstOrFail();

        return new StarsystemResource($starsystem);
    }

    #[OA\Post(
        path: '/api/starsystems/search',
        description: 'Search for starsystems by code, cig_id, or name.',
        summary: 'Starsystem Search',
        requestBody: new OA\RequestBody(
            description: 'Partial starsystem code or name to search for',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: 'query', type: 'string'),
                        ],
                        type: 'object',
                    ),
                    example: '{"query": "Sol"}',
                ),
            ],
        ),
        tags: ['Starmap', 'RSI-Website', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of matching Starsystems',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/starsystem')
                )
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = mb_strtoupper($request->validated('query'));

        $starsystems = QueryBuilder::for(Starsystem::class)
            ->where('code', $query)
            ->orWhere('cig_id', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->paginate()
            ->appends(request()->query());

        return StarsystemResource::collection($starsystems);
    }
}
