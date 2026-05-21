<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerResource;
use App\Models\Game\Manufacturer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('manufacturers')]
class ManufacturerController extends Controller
{
    /**
     * Build base query with groupBy for manufacturers.
     */
    private function buildBaseQuery(Request $request): QueryBuilder
    {
        return QueryBuilder::for(Manufacturer::class, $request)
            ->select(['name'])
            ->selectRaw("MIN(NULLIF(code, '')) AS code")
            ->selectRaw("MIN(NULLIF(uuid::text, ''))::uuid AS uuid")
            ->where('name', '<>', '')
            ->allowedFilters(AllowedFilter::partial('name'))
            ->groupBy('name')
            ->orderBy('name');
    }

    #[OA\Get(
        path: '/api/manufacturers',
        operationId: 'listManufacturers',
        description: 'Returns paginated manufacturers grouped by name with optional pagination.',
        summary: 'In-Game Manufacturers Overview',
        tags: ['Manufacturers'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(name: 'filter[name]', description: 'Partial match on manufacturer name', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Manufacturers',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/manufacturer_link')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->buildBaseQuery($request)
            ->jsonPaginate()
            ->appends($request->query());

        return ManufacturerLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/manufacturers/{manufacturer}',
        operationId: 'getManufacturer',
        description: 'Retrieve a manufacturer by name, UUID, or code together with its products.',
        summary: 'In-Game Manufacturer Detail',
        tags: ['Manufacturers'],
        parameters: [
            new OA\Parameter(
                name: 'manufacturer',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Manufacturer name, uuid, or code',
                    type: 'string',
                    example: 'Anvil Aerospace',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Manufacturer and its products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/manufacturer'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 404, description: 'Manufacturer not found.', content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response')),
        ]
    )]
    public function show(Request $request, string $manufacturer): ManufacturerResource
    {
        $identifier = $this->cleanQueryName($manufacturer);

        $isUuid = Str::isUuid($identifier);

        try {
            if ($isUuid) {
                $manufacturer = QueryBuilder::for(Manufacturer::class, $request)
                    ->where('uuid', $identifier)
                    ->firstOrFail();
            } else {
                $manufacturer = QueryBuilder::for(Manufacturer::class, $request)
                    ->orWhere('name', 'LIKE', sprintf('%%%s%%', $identifier))
                    ->orWhere('code', 'LIKE', sprintf('%%%s%%', $identifier))
                    ->firstOrFail();
            }
        } catch (ModelNotFoundException) {
            throw new NotFoundHttpException('No Manufacturer with specified UUID or Name found.');
        }

        return new ManufacturerResource($manufacturer);
    }

    #[OA\Post(
        path: '/api/manufacturers/search',
        operationId: 'searchManufacturersDeprecated',
        description: 'Deprecated. Use GET /api/manufacturers?filter[name]={value} for name search. This endpoint will be removed in a future version.',
        summary: 'In-Game Manufacturer Search (Deprecated)',
        requestBody: new OA\RequestBody(
            description: 'Manufacturer name, uuid, or code',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(type: 'object'),
                    example: '{"query": "Anvil"}',
                ),
            ]
        ),
        tags: ['Manufacturers', 'Search'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A List of matching Manufacturers',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/manufacturer_link')),
                        new OA\Property(property: 'links', ref: '#/components/schemas/pagination_links'),
                        new OA\Property(property: 'meta', ref: '#/components/schemas/pagination_meta'),
                    ],
                    type: 'object'
                )
            ),
        ],
        deprecated: true
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $query = $request->validated('query');
        $isUuid = Str::isUuid($query);
        $normalizedSearch = mb_strtolower($query);

        $manufacturers = QueryBuilder::for(Manufacturer::class)
            ->select(['name'])
            ->selectRaw("MIN(NULLIF(code, '')) AS code")
            ->selectRaw("MIN(NULLIF(uuid::text, ''))::uuid AS uuid")
            ->where(function (Builder $q) use ($query, $isUuid, $normalizedSearch) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$normalizedSearch}%"])
                    ->orWhereRaw('LOWER(code) LIKE ?', ["%{$normalizedSearch}%"]);

                if ($isUuid) {
                    $q->orWhere('uuid', $query);
                }
            })
            ->groupBy('name')
            ->orderBy('name')
            ->jsonPaginate()
            ->appends($request->query());

        return ManufacturerLinkResource::collection($manufacturers)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }
}
