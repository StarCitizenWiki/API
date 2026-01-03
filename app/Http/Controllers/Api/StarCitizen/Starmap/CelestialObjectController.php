<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Starmap\CelestialObjectResource;
use App\Models\StarCitizen\Starmap\CelestialObject\CelestialObject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CelestialObjectController extends Controller
{
    #[OA\Get(
        path: '/api/celestial-objects',
        description: 'Returns paginated celestial objects with optional relationships.',
        summary: 'Starmap Celestial Objects Overview',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(
                name: 'include',
                description: 'Include additional relationships (affiliation, starsystem).',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                explode: false,
                allowReserved: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Celestial Objects',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/celestial_object')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(CelestialObject::class, $request)
            ->allowedIncludes([])
            ->paginate()
            ->appends(request()->query());

        return CelestialObjectResource::collection($query);
    }

    #[OA\Get(
        path: '/api/celestial-objects/{code}',
        description: 'Retrieve a celestial object by code or identifier, optionally including relations.',
        summary: 'Celestial Object Detail',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(
                name: 'code',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Celestial Object code or identifier',
                    type: 'string',
                ),
            ),
            new OA\Parameter(
                name: 'include',
                description: 'Include additional relationships (affiliation, starsystem).',
                in: 'query',
                schema: new OA\Schema(type: 'string'),
                explode: false,
                allowReserved: true,
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Celestial Object',
                content: new OA\JsonContent(ref: '#/components/schemas/celestial_object')
            ),
            new OA\Response(
                response: 404,
                description: 'No Celestial Object with specified code or name found.'
            ),
        ]
    )]
    public function show(Request $request): AbstractBaseResource
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
            /** @var CelestialObject $starsystem */
            $starsystem = QueryBuilder::for(CelestialObject::class, $request)
                ->where('code', $code)
                ->orWhere('cig_id', $code)
                ->orWhere('name', 'LIKE', "%$code%")
                ->allowedIncludes(CelestialObjectResource::validIncludes())
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Celestial Object with specified Code or Name found.');
        }

        return new CelestialObjectResource($starsystem);
    }

    #[OA\Post(
        path: '/api/celestial-objects/search',
        description: 'Search celestial objects by code, cig_id, or name.',
        summary: 'Celestial Object Search',
        requestBody: new OA\RequestBody(
            description: 'Partial celestial object code or name to search for',
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
                    example: '{"query": "Pleiades"}',
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
                description: 'List of matching Celestial Objects',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/celestial_object')
                )
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = mb_strtoupper($request->validated('query'));

        $objects = QueryBuilder::for(CelestialObject::class)
            ->where('code', $query)
            ->orWhere('cig_id', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->paginate()
            ->appends(request()->query());

        return CelestialObjectResource::collection($objects);
    }
}
