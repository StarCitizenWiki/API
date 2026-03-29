<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Game\ResourceTypeIndexRequest;
use App\Http\Resources\Game\Blueprint\BlueprintResource;
use App\Http\Resources\Game\Concerns\ResolvesGameVersion;
use App\Http\Resources\Game\ResourceType\ResourceTypeLinkResource;
use App\Models\Game\BlueprintData;
use App\Models\Game\ResourceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ResourceTypeController extends Controller
{
    use ResolvesGameVersion;

    #[OA\Get(
        path: '/api/resource-types',
        description: 'Returns paginated game resource types, optionally filtered to only those consumed by blueprints in the requested or default game version.',
        summary: 'List Game Resource Types',
        tags: ['In-Game', 'Resource Types'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
            new OA\Parameter(
                name: 'filter[used]',
                description: 'When true, only resource types used by blueprint ingredients in the requested or default game version are returned.',
                in: 'query',
                schema: new OA\Schema(type: 'boolean')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of resource types',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/resource_type_link')
                )
            ),
        ]
    )]
    public function index(ResourceTypeIndexRequest $request): AnonymousResourceCollection
    {
        $resourceTypes = $this->buildIndexQuery($request)
            ->with('refinedVersion')
            ->orderBy('key')
            ->jsonPaginate()
            ->appends($request->query());

        return ResourceTypeLinkResource::collection($resourceTypes);
    }

    #[OA\Get(
        path: '/api/resource-types/{resourceType}/blueprints',
        description: 'Returns paginated blueprints that consume the given resource type in the requested or default game version.',
        summary: 'Lookup Blueprints By Resource Type',
        tags: ['In-Game', 'Resource Types', 'Blueprints'],
        parameters: [
            new OA\Parameter(
                name: 'resourceType',
                in: 'path',
                required: true,
                schema: new OA\Schema(
                    description: 'Resource type UUID',
                    type: 'string',
                ),
            ),
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(ref: '#/components/parameters/version'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of blueprints that consume the resource type',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/blueprint')
                )
            ),
        ]
    )]
    public function lookup(ResourceType $resourceType): AnonymousResourceCollection
    {
        $blueprints = QueryBuilder::for(BlueprintData::class, request())
            ->forRequestedOrDefaultVersion($this->gameVersionCode())
            ->consumesResourceType($resourceType->uuid)
            ->with(['blueprint', 'gameVersion'])
            ->orderBy('key')
            ->jsonPaginate()
            ->appends(request()->query());

        return BlueprintResource::collection($blueprints);
    }

    /**
     * Build base query with allowed filters for resource type listing.
     */
    private function buildIndexQuery(ResourceTypeIndexRequest $request): QueryBuilder
    {
        return QueryBuilder::for(ResourceType::class, $request)
            ->allowedFilters(...$this->allowedFilters());
    }

    /**
     * @return array<int, AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::callback('used', function (Builder $query, mixed $value): void {
                if ($value !== true) {
                    return;
                }

                $usedResourceTypeUuids = BlueprintData::query()
                    ->forRequestedOrDefaultVersion($this->gameVersionCode())
                    ->get(['ingredient_resource_type_uuids'])
                    ->pluck('ingredient_resource_type_uuids')
                    ->flatten()
                    ->filter(static fn (mixed $uuid): bool => is_string($uuid) && $uuid !== '')
                    ->unique()
                    ->values()
                    ->all();

                if ($usedResourceTypeUuids === []) {
                    $query->whereRaw('0 = 1');

                    return;
                }

                $query->whereIn('uuid', $usedResourceTypeUuids);
            }),
        ];
    }
}
