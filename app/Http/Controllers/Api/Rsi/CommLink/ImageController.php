<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Filters\ImageTagFilter;
use App\Http\Requests\Rsi\CommLink\Image\ImageSearchRequest;
use App\Http\Resources\Rsi\CommLink\Image\ImageResource;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ImageController extends Controller
{
    #[OA\Get(
        path: '/api/v2/comm-link-images',
        tags: ['Comm-Links', 'RSI-Website', 'Images'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(name: 'filter[tags]', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Comm-Link Images',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image_v2')
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters([
                AllowedFilter::custom('tags', new ImageTagFilter),
            ])
            ->orderByDesc('id')
            ->paginate()
            ->appends(request()->query());

        return ImageResource::collection($query);
    }

    #[OA\Get(
        path: '/api/v2/comm-link-images/random',
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
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image_v2')
                )
            ),
        ]
    )]
    public function random(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('tags', 'tags.name'),
            ])
            ->whereRelation('metadata', 'size', '>=', 250 * 1024)
            ->inRandomOrder()
            ->limit($request->has('limit') ? min($request->get('limit'), 100) : 1)
            ->get();

        return ImageResource::collection($query);
    }

    #[OA\Post(
        path: '/api/v2/comm-link-images/search',
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
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image_v2')
                )
            ),
        ]
    )]
    public function search(ImageSearchRequest $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('tags', 'tags.name'),
            ])
            ->whereNull('base_image_id')
            ->whereRaw('LOWER(src) LIKE ?', [sprintf('%%%s%%', strtolower($request->get('query')))])
            ->whereRelation('metadata', 'size', '>', 0)
            ->limit(100)
            ->orderByDesc('created_at')
            ->get();

        return ImageResource::collection($query);
    }
}
