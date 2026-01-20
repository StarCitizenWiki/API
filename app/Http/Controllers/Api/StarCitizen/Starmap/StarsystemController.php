<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\StarCitizen\Starmap\StarsystemResource;
use App\Models\StarCitizen\Starmap\Starsystem;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StarsystemController extends Controller
{
    /**
     * Build base query with filters and sorts for starsystems.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Starsystem::class, $request)
            ->allowedIncludes(StarsystemResource::validIncludes())
            ->allowedFilters([
                AllowedFilter::exact('affiliation', 'affiliation.name'),
                AllowedFilter::exact('code'),
                AllowedFilter::partial('name'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('size', 'aggregated_size'),
            ])
            ->allowedSorts([
                'name',
                'code',
                'status',
                'type',
                'aggregated_size',
                'aggregated_population',
                'aggregated_economy',
                'aggregated_danger',
            ]);
    }

    #[OA\Get(
        path: '/api/starsystems',
        description: 'Returns paginated starsystems, optionally including related resources.',
        summary: 'Starmap Starsystems Overview',
        tags: ['Starmap', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[affiliation]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[code]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[name]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[status]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
            new OA\Parameter(name: 'sort', in: 'query', schema: new OA\Schema(type: 'string')),
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
        $collection = $this->buildBaseQuery($request)
            ->jsonPaginate()
            ->appends(request()->query());

        if ($request->has('include') && str_contains($request->input('include'), 'jumppoints')) {
            $collection->load('jumppoints.entry', 'jumppoints.exit');
        }

        return StarsystemResource::collection($collection);
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

        $query = QueryBuilder::for(Starsystem::class, $request)
            ->where('code', $code)
            ->orWhere('name', 'LIKE', "%$code%");

        if (is_numeric($code)) {
            $query->orWhere('cig_id', (int) $code);
        }

        /** @var Starsystem $starsystem */
        $starsystem = $query
            ->allowedIncludes(StarsystemResource::validIncludes())
            ->firstOrFail();

        if ($starsystem->relationLoaded('jumppoints')) {
            $starsystem->load('jumppoints.entry', 'jumppoints.exit');
        }

        return new StarsystemResource($starsystem);
    }

    #[OA\Post(
        path: '/api/starsystems/search',
        description: 'Deprecated. Use GET /api/starsystems?filter[name]={value} for name search. This endpoint will be removed in a future version.',
        summary: 'Starsystem Search (Deprecated)',
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
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
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
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|\Illuminate\Http\JsonResponse
    {
        $query = mb_strtoupper($request->validated('query'));

        $builder = $this->buildBaseQuery($request);

        $builder->where(function (Builder $b) use ($query) {
            $b->where('code', $query)
                ->orWhere('name', 'LIKE', "%$query%");

            if (is_numeric($query)) {
                $b->orWhere('cig_id', (int) $query);
            }
        });

        $starsystems = $builder
            ->jsonPaginate()
            ->appends(request()->query());

        return StarsystemResource::collection($starsystems)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }

    #[OA\Get(
        path: '/api/starsystems/filters',
        description: 'Return all available filter values for starsystems.',
        summary: 'Starsystem Filters',
        tags: ['Starmap', 'RSI-Website'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Available filters for starsystems.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'filters',
                            properties: [
                                new OA\Property(property: 'affiliation', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'status', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'type', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                                new OA\Property(property: 'size', type: 'array', items: new OA\Items(ref: '#/components/schemas/filter_value')),
                            ],
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function filters(): JsonResponse
    {
        $filters = FilterCache::rememberForever(
            FilterCache::NAMESPACE_STARSYSTEMS,
            FilterCache::starsystemsKey(),
            static function (): array {
                $baseQuery = (new Starsystem)->newQueryWithoutRelationships()->toBase();

                $facets = [
                    'affiliation' => [
                        'expr' => 'starmap_affiliations.name',
                        'join' => static fn ($q) => $q
                            ->leftJoin('starmap_starsystem_affiliation', 'starmap_starsystems.id', '=', 'starmap_starsystem_affiliation.starsystem_id')
                            ->leftJoin('starmap_affiliations', 'starmap_starsystem_affiliation.affiliation_id', '=', 'starmap_affiliations.id'),
                        'cast' => null,
                    ],
                    'status' => [
                        'expr' => 'starmap_starsystems.status',
                        'cast' => null,
                    ],
                    'type' => [
                        'expr' => 'starmap_starsystems.type',
                        'cast' => null,
                    ],
                    'size' => [
                        'expr' => 'starmap_starsystems.aggregated_size',
                        'cast' => static fn ($value) => $value === null ? null : (float) $value,
                    ],
                ];

                $out = [];

                foreach ($facets as $key => $facet) {
                    $expr = $facet['expr'];

                    $q = clone $baseQuery;

                    if (isset($facet['join'])) {
                        ($facet['join'])($q);
                    }

                    $rows = $q
                        ->select([
                            DB::raw("{$expr} as value"),
                            DB::raw('count(*) as count'),
                        ])
                        ->groupByRaw($expr)
                        ->orderByRaw("{$expr} IS NULL, {$expr}")
                        ->get();

                    $out[$key] = FilterValues::fromRows($rows, $facet['cast'] ?? null);
                }

                return $out;
            }
        );

        return response()->json([
            'filters' => $filters,
        ]);
    }
}
