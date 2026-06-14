<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Attributes\CacheTag;
use App\Http\Controllers\Controller;
// use App\Http\Filters\ImageTagFilter;
use App\Http\Requests\Api\Game\SearchRequest;
use App\Http\Resources\Rsi\CommLink\Image\ImageResource;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CacheTag('comm-links')]
class ImageController extends Controller
{
    #[OA\Get(
        path: '/api/comm-link-images',
        operationId: 'listCommLinkImages',
        description: 'List available comm-link images with pagination. Each image includes its associated Comm-Links (with channel, category, series), tags, duplicates, and base image when available.',
        summary: 'Comm-Link Images',
        tags: ['Comm-Links', 'Images'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/page'),
            new OA\Parameter(ref: '#/components/parameters/page_number'),
            new OA\Parameter(ref: '#/components/parameters/page_size'),
            new OA\Parameter(
                name: 'filter[tags]',
                description: 'Partial match on tag name. WARNING: Images have no tags currently.',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'concept')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of Comm-Link Images',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/comm_link_image')),
                        new OA\Property(property: 'links', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_links')]),
                        new OA\Property(property: 'meta', allOf: [new OA\Schema(ref: '#/components/schemas/pagination_meta')]),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->with([
                'metadata',
                'commLinks' => fn ($q) => $q->with(['channel', 'category', 'series']),
                'tags',
                'duplicates',
                'baseImage',
            ])
            ->orderByDesc('created_at')
            ->whereNull('base_image_id')
            ->jsonPaginate()
            ->appends(request()->query());

        return ImageResource::collection($query);
    }

    #[OA\Get(
        path: '/api/comm-link-images/{image}',
        operationId: 'getCommLinkImage',
        description: 'Retrieve a single comm-link image with its associated Comm-Links (with channel, category, series), tags, duplicates, and base image when available.',
        summary: 'Comm-Link Image Detail',
        tags: ['Comm-Links', 'Images'],
        parameters: [
            new OA\Parameter(
                name: 'image',
                description: 'Internal database ID of the image',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 44216),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular Comm-Link Image',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/comm_link_image'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Comm-Link image not found.',
                content: new OA\JsonContent(ref: '#/components/schemas/not_found_error_response'),
            ),
        ]
    )]
    public function show(int $image): ImageResource
    {
        $model = Image::query()
            ->with([
                'metadata',
                'commLinks' => fn ($q) => $q->with(['channel', 'category', 'series']),
                'tags',
                'duplicates',
                'baseImage',
            ])
            ->find($image);

        if ($model === null) {
            throw new NotFoundHttpException('Comm-Link image not found.');
        }

        return new ImageResource($model);
    }

    #[OA\Get(
        path: '/api/comm-link-images/random',
        operationId: 'getRandomCommLinkImage',
        description: 'Retrieve random comm-link images (minimum 250 KB), optionally filtered by tag name. Only images without a base image are included.',
        summary: 'Comm-Link Images Random',
        tags: ['Comm-Links', 'Images'],
        parameters: [
            new OA\Parameter(
                name: 'limit',
                description: 'Number of random images to return',
                in: 'query',
                schema: new OA\Schema(type: 'integer', maximum: 100, example: 5)
            ),
            new OA\Parameter(
                name: 'filter[tags]',
                description: 'Partial match on tag name',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'concept')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Retrieve a random Comm-Link Image. Limit parameter sets the number of random images',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/comm_link_image')),
                    ],
                    type: 'object'
                )
            ),
        ]
    )]
    public function random(Request $request): AnonymousResourceCollection
    {
        $limit = $request->has('limit') ? min($request->get('limit'), 100) : 1;

        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters(
                AllowedFilter::callback('tags', static function (Builder $query, mixed $value): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $query->whereHas('tags', static function (Builder $q) use ($value): void {
                        $q->whereLike('comm_link_image_tags.name', "%{$value}%");
                    });
                }),
            )
            ->with(['metadata'])
            ->whereRelation('metadata', 'size', '>=', 250 * 1024)
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return ImageResource::collection($query);
    }

    #[OA\Post(
        path: '/api/comm-link-images/search',
        operationId: 'searchCommLinkImages',
        description: 'Search comm-link images by filename with optional tag filtering. Only images without a base image and with non-zero file size are returned.',
        summary: 'Comm-Link Image Search by filename',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['query'],
                        properties: [
                            new OA\Property(
                                property: 'query',
                                description: 'Search term matched against image filenames (partial match)',
                                type: 'string',
                                maxLength: 255,
                                minLength: 1,
                                example: 'Starshipbridge'
                            ),
                        ],
                        type: 'object',
                    ),
                    example: '{"query": "Starshipbridge"}',
                ),
            ]
        ),
        tags: ['Comm-Links', 'Images', 'Search'],
        parameters: [
            new OA\Parameter(
                name: 'filter[tags]',
                description: 'Partial match on tag name',
                in: 'query',
                schema: new OA\Schema(type: 'string', example: 'concept')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Search for a Comm-Link Image by its filename.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/comm_link_image')),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error. The query field is required and must be a string between 1 and 255 characters.',
                content: new OA\JsonContent(ref: '#/components/schemas/validation_error_response'),
            ),
        ]
    )]
    public function search(SearchRequest $request): AnonymousResourceCollection
    {
        $query = QueryBuilder::for(Image::class, $request)
            ->allowedFilters(
                AllowedFilter::callback('tags', static function (Builder $query, mixed $value): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }

                    $query->whereHas('tags', static function (Builder $q) use ($value): void {
                        $q->whereLike('comm_link_image_tags.name', "%{$value}%");
                    });
                }),
            )
            ->with(['metadata'])
            ->whereNull('base_image_id')
            ->whereLike('src', "%{$request->validated('query')}%")
            ->whereRelation('metadata', 'size', '>', 0)
            ->limit(100)
            ->orderByDesc('created_at')
            ->get();

        return ImageResource::collection($query);
    }
}
