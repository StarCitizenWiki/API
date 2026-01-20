<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Http\Controllers\Controller;
// use App\Http\Filters\ImageTagFilter;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Rsi\CommLink\Image\ImageResource;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ImageController extends Controller
{
    #[OA\Get(
        path: '/api/comm-link-images',
        description: 'List available comm-link images with pagination.',
        summary: 'Comm-Link Images',
        tags: ['Comm-Links', 'RSI-Website', 'Images'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            //            new OA\Parameter(name: 'filter[tags]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Comm-Link Images',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->with(['commLinks'])
            ->orderByDesc('created_at')
            ->whereNull('base_image_id')
            ->jsonPaginate()
            ->appends(request()->query());

        return ImageResource::collection($query);
    }

    #[OA\Get(
        path: '/api/comm-link-images/{image}',
        description: 'Retrieve a single comm-link image with related metadata.',
        summary: 'Comm-Link Image Detail',
        tags: ['Comm-Links', 'RSI-Website', 'Images'],
        parameters: [
            new OA\Parameter(
                name: 'image',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular Comm-Link Image',
                content: new OA\JsonContent(ref: '#/components/schemas/comm_link_image')
            ),
            new OA\Response(
                response: 404,
                description: 'Comm-Link image not found.',
            ),
        ]
    )]
    public function show(int $image): ImageResource
    {
        $model = Image::query()
            ->with(['commLinks', 'duplicates', 'baseImage'])
            ->find($image);

        if ($model === null) {
            throw new NotFoundHttpException('Comm-Link image not found.');
        }

        return new ImageResource($model);
    }

    #[OA\Get(
        path: '/api/comm-link-images/random',
        description: 'Retrieve random comm-link images, optionally filtered by tag.',
        summary: 'Comm-Link Images Random',
        tags: ['Comm-Links', 'RSI-Website', 'Images'],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', maximum: 100)),
            new OA\Parameter(name: 'filter[tags]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retrieve a random Comm-Link Image. Limit parameter sets the number of random images',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image')
                )
            ),
        ]
    )]
    public function random(Request $request): AnonymousResourceCollection
    {
        $limit = $request->has('limit') ? min($request->get('limit'), 100) : 1;

        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('tags', 'tags.name'),
            ])
            ->whereRelation('metadata', 'size', '>=', 250 * 1024)
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return ImageResource::collection($query);
    }

    #[OA\Post(
        path: '/api/comm-link-images/search',
        description: 'Search comm-link images by filename with optional tag filtering.',
        summary: 'Comm-Link Image Search by filename',
        tags: ['Comm-Links', 'RSI-Website', 'Images', 'Search'],
        parameters: [
            new OA\Parameter(name: 'filter[tags]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search for a Comm-Link Image by its filename.',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image')
                )
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('tags', 'tags.name'),
            ])
            ->whereNull('base_image_id')
            ->whereRaw('src ILIKE ?', [sprintf('%%%s%%', $request->validated('query'))])
            ->whereRelation('metadata', 'size', '>', 0)
            ->limit(100)
            ->orderByDesc('created_at')
            ->get();

        return ImageResource::collection($query);
    }
}
