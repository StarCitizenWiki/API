<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\StarCitizen\Starmap\Starsystem;

use App\Http\Controllers\Api\AbstractApiController as ApiController;
use App\Http\Requests\StarCitizen\Starmap\StarsystemRequest;
use App\Models\StarCitizen\Starmap\Starsystem\Starsystem;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemLinkTransformer;
use App\Transformers\Api\V1\StarCitizen\Starmap\StarsystemTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class StarsystemController extends ApiController
{
    /**
     * StarsystemController constructor.
     *
     * @param Request               $request
     * @param StarsystemTransformer $transformer
     */
    public function __construct(Request $request, StarsystemTransformer $transformer)
    {
        $this->transformer = $transformer;
        parent::__construct($request);
    }

    #[OA\Get(
        path: '/api/starmap/starsystems',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'starsystem_includes',
                    collectionFormat: 'csv',
                    enum: [
                        'celestial_objects',
                        'jumppoints',
                    ]
                ),
                allowReserved: true
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
            )
        ]
    )]
    public function index(Request $request): Response
    {
        if ($request->has('transformer') && $request->get('transformer', null) === 'link') {
            $this->transformer = new StarsystemLinkTransformer();
        }

        return $this->getResponse(Starsystem::query()->orderBy('name'));
    }

    #[OA\Get(
        path: '/api/starmap/starsystem/{code}',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/limit'),
            new OA\Parameter(
                name: 'include',
                in: 'query',
                schema: new OA\Schema(
                    schema: 'starsystem_includes',
                    collectionFormat: 'csv',
                    enum: [
                        'celestial_objects',
                        'jumppoints',
                    ]
                ),
                allowReserved: true
            ),
            new OA\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    schema: 'starsystem_code',
                    description: 'Starsystem Code, e.g. SOL',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/starsystem',
                response: 200,
                description: 'A singular Starsystem',
            ),
            new OA\Response(
                response: 404,
                description: 'No System with specified Code found.',
            )
        ]
    )]
    public function show(Request $request): Response
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

        try {
            /** @var Starsystem $starsystem */
            $starsystem = Starsystem::query()
                ->where('code', $code)
                ->orWhere('cig_id', $code)
                ->orWhere('name', 'LIKE', "%$code%")
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $code)], 404);
        }

        return $this->getResponse($starsystem);
    }

//    #[OA\Post(
//        path: '/api/starmap/starsystems/search',
//        requestBody: new OA\RequestBody(
//            description: 'Starsystem name',
//            required: true,
//            content: [
//                new OA\MediaType(
//                    mediaType: 'application/json',
//                    schema: new OA\Schema(
//                        schema: 'query',
//                        type: 'json',
//                    ),
//                    example: '{"query": "SOL"}',
//                )
//            ]
//        ),
//        tags: ['Starmap', 'RSI-Website'],
//        responses: [
//            new OA\Response(
//                response: 200,
//                description: 'List of systems matching the query',
//                content: new OA\JsonContent(
//                    type: 'array',
//                    items: new OA\Items(ref: '#/components/schemas/starsystem')
//                )
//            ),
//            new OA\Response(
//                response: 404,
//                description: 'No System found.',
//            )
//        ],
//    )]
    public function search(Request $request): Response
    {
        $rules = (new StarsystemRequest())->rules();

        $request->validate($rules);

        $query = urldecode($this->request->get('query', ''));
        $queryBuilder = Starsystem::query()->where('name', 'like', "%{$query}%");

        if ($queryBuilder->count() === 0) {
            return new Response(['code' => 404, 'message' => sprintf(static::NOT_FOUND_STRING, $query)], 404);
        }

        return $this->getResponse($queryBuilder);
    }
}
