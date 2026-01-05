<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Game\Manufacturer\ManufacturerLinkResource;
use App\Http\Resources\Game\Manufacturer\ManufacturerResource;
use App\Models\Game\Manufacturer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ManufacturerController extends Controller
{
    #[OA\Get(
        path: '/api/manufacturers',
        description: 'Returns paginated manufacturers grouped by name with optional pagination.',
        summary: 'In-Game Manufacturers Overview',
        tags: ['In-Game', 'Manufacturers'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Manufacturers',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        ref: '#/components/schemas/manufacturer_link'
                    )
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Manufacturer::class, $request)
            ->select(['name'])
            ->selectRaw("MIN(NULLIF(code, '')) AS code")
            ->selectRaw("MIN(NULLIF(uuid, '')) AS uuid")
            ->where('name', '<>', '')
            ->groupBy('name')
            ->orderBy('name')
            ->jsonPaginate()
            ->appends($request->query());

        return ManufacturerLinkResource::collection($query);
    }

    #[OA\Get(
        path: '/api/manufacturers/{manufacturer}',
        description: 'Retrieve a manufacturer by name, UUID, or code together with its products.',
        summary: 'In-Game Manufacturer Detail',
        tags: ['In-Game', 'Manufacturers'],
        parameters: [
            new OA\Parameter(
                name: 'manufacturer',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Manufacturer name, uuid, or code',
                    type: 'string',
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A Manufacturer and its products',
                content: new OA\JsonContent(ref: '#/components/schemas/manufacturer')
            ),
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
        description: 'Search manufacturers by name, UUID, or code with optional pagination.',
        summary: 'In-Game Manufacturer Search',
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
        tags: ['In-Game', 'Manufacturers', 'Search'],
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
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/manufacturer_link')
                )
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = $request->validated('query');

        $manufacturers = QueryBuilder::for(Manufacturer::class)
            ->where('name', 'like', "%{$query}%")
            ->orWhere('uuid', $query)
            ->orWhere('name', 'LIKE', sprintf('%%%s%%', $query))
            ->orWhere('code', 'LIKE', sprintf('%%%s%%', $query))
            ->groupBy('name')
            ->jsonPaginate()
            ->appends($request->query());

        return ManufacturerLinkResource::collection($manufacturers);
    }
}
