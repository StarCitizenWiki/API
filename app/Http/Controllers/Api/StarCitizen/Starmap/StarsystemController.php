<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\StarCitizen\Starmap;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\StarCitizen\Starmap\StarsystemResource;
use App\Models\StarCitizen\Starmap\Starsystem;
use App\Support\Filters\FilterCache;
use App\Support\Filters\FilterValues;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
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
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[affiliation]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[status]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[type]', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'filter[size]', in: 'query', schema: new OA\Schema(type: 'number')),
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
            ->allowedFilters([
                AllowedFilter::exact('affiliation', 'affiliation.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('size', 'aggregated_size'),
            ])
            ->jsonPaginate()
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
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = mb_strtoupper($request->validated('query'));

        $starsystems = QueryBuilder::for(Starsystem::class)
            ->where('code', $query)
            ->orWhere('cig_id', $query)
            ->orWhere('name', 'LIKE', "%$query%")
            ->allowedFilters([
                AllowedFilter::exact('affiliation', 'affiliation.name'),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('size', 'aggregated_size'),
            ])
            ->jsonPaginate()
            ->appends(request()->query());

        return StarsystemResource::collection($starsystems);
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

                $affiliationRows = (clone $baseQuery)
                    ->leftJoin('starmap_starsystem_affiliation', 'starmap_starsystems.id', '=', 'starmap_starsystem_affiliation.starsystem_id')
                    ->leftJoin('starmap_affiliations', 'starmap_starsystem_affiliation.affiliation_id', '=', 'starmap_affiliations.id')
                    ->selectRaw('starmap_affiliations.name as value, count(*) as count')
                    ->groupBy('starmap_affiliations.name')
                    ->orderByRaw('starmap_affiliations.name IS NULL, starmap_affiliations.name')
                    ->get();

                $statusRows = (clone $baseQuery)
                    ->selectRaw('starmap_starsystems.status as value, count(*) as count')
                    ->groupBy('starmap_starsystems.status')
                    ->orderByRaw('starmap_starsystems.status IS NULL, starmap_starsystems.status')
                    ->get();

                $typeRows = (clone $baseQuery)
                    ->selectRaw('starmap_starsystems.type as value, count(*) as count')
                    ->groupBy('starmap_starsystems.type')
                    ->orderByRaw('starmap_starsystems.type IS NULL, starmap_starsystems.type')
                    ->get();

                $sizeRows = (clone $baseQuery)
                    ->selectRaw('starmap_starsystems.aggregated_size as value, count(*) as count')
                    ->groupBy('starmap_starsystems.aggregated_size')
                    ->orderByRaw('starmap_starsystems.aggregated_size IS NULL, starmap_starsystems.aggregated_size')
                    ->get();

                return [
                    'affiliation' => FilterValues::fromRows($affiliationRows),
                    'status' => FilterValues::fromRows($statusRows),
                    'type' => FilterValues::fromRows($typeRows),
                    'size' => FilterValues::fromRows($sizeRows, static fn ($value) => $value === null ? null : (float) $value),
                ];
            }
        );

        return response()->json([
            'filters' => $filters,
        ]);
    }
}
