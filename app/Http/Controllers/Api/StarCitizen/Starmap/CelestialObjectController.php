<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Http\Filters\SortByRelation;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\AbstractBaseResource;
use App\Http\Resources\StarCitizen\Starmap\CelestialObjectResource;
use App\Models\StarCitizen\Starmap\CelestialObject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CelestialObjectController extends Controller
{
    /**
     * Build base query with filters and sorts for celestial objects.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        $query = QueryBuilder::for(CelestialObject::class, $request)
            ->allowedIncludes(CelestialObjectResource::validIncludes())
            ->allowedFilters([
                AllowedFilter::exact('starsystem', 'starsystem.name'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('designation'),
                AllowedFilter::exact('type'),
            ])
            ->allowedSorts([
                AllowedSort::field('id', 'cig_id'),
                AllowedSort::custom('starsystem', new SortByRelation, 'starsystem.name'),
                'name',
                'designation',
                'type',
                'fairchanceact',
                'habitable',
                'latitude',
                'longitude',
                'sensor_population',
                'sensor_economy',
                'sensor_danger',
            ]);

        $includes = $request->get('include', '');

        if (str_contains($includes, 'starsystem')) {
            $query->with(['starsystem']);
        }

        if (str_contains($includes, 'jumppoints')) {
            $query->with(['jumppointEntry', 'jumppointExit']);
        }

        return $query;
    }

    #[OA\Get(
        path: '/api/celestial-objects',
        description: 'Returns paginated celestial objects with optional relationships.',
        summary: 'Starmap Celestial Objects Overview',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[starsystem]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on celestial object name', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[designation]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string')),
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
        $query = $this->buildBaseQuery($request)
            ->jsonPaginate()
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
            $query = QueryBuilder::for(CelestialObject::class, $request)
                ->allowedIncludes(CelestialObjectResource::validIncludes());

            $includes = $request->get('include', '');

            if (str_contains($includes, 'starsystem')) {
                $query->with(['starsystem']);
            }

            if (str_contains($includes, 'jumppoints')) {
                $query->with(['jumppointEntry', 'jumppointExit']);
            }

            $starsystem = $query->where('code', $code)
                ->orWhere('cig_id', $code)
                ->when(
                    strlen($code) > 3,
                    fn ($q) => $q->orWhere('name', 'LIKE', "{$code}%")
                )
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new NotFoundHttpException('No Celestial Object with specified Code or Name found.');
        }

        return new CelestialObjectResource($starsystem);
    }

    #[OA\Post(
        path: '/api/celestial-objects/search',
        description: 'Deprecated. Use GET /api/celestial-objects?filter[name]={value} for name search. This endpoint will be removed in a future version.',
        summary: 'Celestial Object Search (Deprecated)',
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
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
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
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|\Illuminate\Http\JsonResponse
    {
        $query = mb_strtoupper($request->validated('query'));

        $objects = $this->buildBaseQuery($request)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('code', $query)
                    ->orWhere('cig_id', $query)
                    ->when(
                        strlen($query) > 3,
                        fn ($q) => $q->orWhere('name', 'LIKE', "{$query}%")
                    );
            })
            ->jsonPaginate()
            ->appends(request()->query());

        return CelestialObjectResource::collection($objects)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }
}
