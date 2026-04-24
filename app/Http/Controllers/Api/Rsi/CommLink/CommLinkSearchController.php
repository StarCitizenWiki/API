<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use App\Http\Requests\Rsi\CommLink\SimilarSearchRequest;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Http\Resources\Rsi\CommLink\Image\ImageHashResource;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash as ImageHashModel;
use App\Services\ImageHash\PdqHasher;
use App\Services\Parser\CommLink\Image as ImageParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;
use RuntimeException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CommLinkSearchController extends Controller
{
    #[OA\Post(
        path: '/api/comm-links/search',
        description: 'Deprecated. Use GET /api/comm-links?filter[title]={value} for title search. This endpoint will be removed in a future version.',
        summary: 'Comm-Link Search (Deprecated)',
        requestBody: new OA\RequestBody(
            description: 'At least one of keyword or query is required.',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(
                                property: 'keyword',
                                description: 'Search term for partial title match (min 3 characters)',
                                type: 'string',
                                maxLength: 255,
                                minLength: 3,
                                example: 'Banu Merchantman'
                            ),
                            new OA\Property(
                                property: 'query',
                                description: 'Search term for partial title match or numeric CIG ID (min 1 character)',
                                type: 'string',
                                maxLength: 255,
                                minLength: 1,
                                example: 'This Week in Star Citizen'
                            ),
                        ],
                        type: 'object',
                    ),
                    example: '{"query": "Banu Merchantman"}',
                ),
            ]
        ),
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
            new OA\Parameter(ref: '#/components/parameters/comm_link_includes'),
            new OA\Parameter(name: 'filter[channel]', description: 'Exact match on channel name (see GET /api/comm-links/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Engineering')),
            new OA\Parameter(name: 'filter[series]', description: 'Exact match on series name (see GET /api/comm-links/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'Around the Verse')),
            new OA\Parameter(name: 'filter[category]', description: 'Exact match on category name (see GET /api/comm-links/filters for valid values)', in: 'query', schema: new OA\Schema(type: 'string', example: 'General')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular Comm-Link',
                content: new OA\JsonContent(ref: '#/components/schemas/comm_link')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error. At least one of keyword or query is required.',
            ),
        ],
        deprecated: true,
    )]
    public function searchByTitle(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $request->validate((new CommLinkSearchRequest)->rules());

        $query = (string) ($request->input('keyword') ?? $request->input('query'));

        $commLinks = QueryBuilder::for(CommLink::class)
            ->where(function (Builder $builder) use ($query) {
                $builder->whereLike('title', "%{$query}%");

                if (is_numeric($query)) {
                    $builder->orWhere('cig_id', (int) $query);
                }
            })
            ->allowedIncludes(...CommLinkResource::validIncludes())
            ->allowedFilters(AllowedFilter::exact('category', 'category.name'), AllowedFilter::exact('series', 'series.name'), AllowedFilter::exact('channel', 'channel.name')

            )
            ->jsonPaginate()
            ->appends(request()->query());

        return CommLinkResource::collection($commLinks)->additional([
            'meta' => ['deprecated' => true],
        ])->response()->header('Deprecated', 'true');
    }

    #[OA\Post(
        path: '/api/comm-links/reverse-image-link-search',
        description: 'Return comm-links that reference the same RSI-hosted image URL.',
        summary: 'Comm-Link Reverse Image Link Search',
        requestBody: new OA\RequestBody(
            description: 'URL to an image hosted on (media.)robertsspaceindustries.com',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['url'],
                        properties: [
                            new OA\Property(
                                property: 'url',
                                description: 'Full URL to an image hosted on robertsspaceindustries.com or media.robertsspaceindustries.com',
                                type: 'string',
                                format: 'uri',
                                example: 'https://robertsspaceindustries.com/i/cc75a45005a236c6e015dfc2782a2f55ed1e84a2/ADdPNihJzmPbNuTnFsH1DqUeqBRpXdSXVVtgJTyDDgscGKrzJuoFjResiiucPBBDeyrBscqRyZz4qxNsSbWvqUwdG/alien-week-2022-front.webp'
                            ),
                        ],
                        type: 'object',
                    ),
                    example: '{"url": "https://robertsspaceindustries.com/i/cc75a45005a236c6e015dfc2782a2f55ed1e84a2/ADdPNihJzmPbNuTnFsH1DqUeqBRpXdSXVVtgJTyDDgscGKrzJuoFjResiiucPBBDeyrBscqRyZz4qxNsSbWvqUwdG/alien-week-2022-front.webp"}',
                ),
            ]
        ),
        tags: ['Comm-Links', 'RSI-Website', 'Search'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Comm-Links that use that image',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link found.',
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error. The url field is required and must be a valid URL on robertsspaceindustries.com.',
            ),
        ],
    )]
    public function reverseImageLinkSearch(ReverseImageLinkSearchRequest $request): AnonymousResourceCollection
    {
        $image = Image::query();

        $dir = $this->getDirHashFromImageUrl($request->get('url', ''));
        if ($dir === 'i') {
            $path = parse_url(
                ImageParser::cleanImgSource($request->get('url')),
                PHP_URL_PATH
            );
            $parts = explode('/', $path);
            array_pop($parts);
            $path = implode('/', $parts);

            $image->where('src', 'LIKE', $path.'%');
        } else {
            $image->where('dir', $dir);
        }

        /** @var Image $image */
        $image = $image->firstOr(
            ['*'],
            function () {
                return [];
            }
        );

        if ($image instanceof Image) {
            $commLinks = $image->commLinks()->get();

            return CommLinkResource::collection($commLinks);
        }

        return CommLinkResource::collection([]);
    }

    #[OA\Post(
        path: '/api/comm-links/reverse-image-search',
        description: 'Search comm-links by uploading an image and specifying a similarity threshold. Requires the GD PHP extension.',
        summary: 'Comm-Link Reverse Image Search',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'multipart/form-data' => new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        required: ['image'],
                        properties: [
                            new OA\Property(
                                property: 'image',
                                description: 'The image to reverse-search (max 5 MB)',
                                type: 'string',
                                format: 'binary',
                            ),
                            new OA\Property(
                                property: 'similarity',
                                description: 'Similarity threshold percentage (1-100). Defaults to 75.',
                                type: 'integer',
                                maximum: 100,
                                minimum: 1,
                                example: 80,
                            ),
                        ],
                        type: 'object',
                    ),
                ),
            ]
        ),
        tags: ['Comm-Links', 'RSI-Website', 'Search'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of similar images with associated Comm-Links',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link found.',
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error. The image field is required and must be a valid image file (max 5 MB).',
            ),
            new OA\Response(
                response: 501,
                description: 'The required GD PHP extension is not loaded on the server.',
            ),
        ],
    )]
    public function reverseImageSearch(ReverseImageSearchRequest $request, PdqHasher $hasher): AnonymousResourceCollection
    {
        $this->checkExtensionsLoaded();

        try {
            $hashResult = $hasher->hashContents($request->imageContents());
        } catch (RuntimeException $exception) {
            throw new HttpException(422, $exception->getMessage(), $exception);
        }

        $data = ImageHashModel::similarImagesForHash(
            $hashResult->toBitString(),
            $request->similarity()
        );

        return ImageHashResource::collection($data);
    }

    #[OA\Get(
        path: '/api/comm-link-images/{image}/similar',
        description: 'Find Comm-Link images similar to an existing RSI-hosted image.',
        summary: 'Comm-Link Reverse Image Similar Search',
        security: [
            ['sanctum' => []],
        ],
        tags: ['Comm-Links', 'RSI-Website', 'Search'],
        parameters: [
            new OA\Parameter(
                name: 'image',
                description: 'Internal database ID of the image',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 44216),
            ),
            new OA\Parameter(
                name: 'similarity',
                description: 'Similarity threshold percentage (1-100). Defaults to 50.',
                in: 'query',
                required: false,
                schema: new OA\Schema(
                    type: 'integer',
                    maximum: 100,
                    minimum: 1,
                    example: 80
                ),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of similar Comm-Link images',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_image')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Comm-Link image not found.',
            ),
        ],
    )]
    public function similarSearch(SimilarSearchRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();

        /** @var Image $image */
        $image = Image::query()->findOrFail($data['image']);

        $similarity = $data['similarity'] ?? 50;

        return ImageHashResource::collection($image->similarImages($similarity, 50));
    }

    /**
     * Returns the RSI directory hash of an image url
     *
     * @param  string  $url  The RSI Media URl
     * @return string The directory hash of the image
     */
    private function getDirHashFromImageUrl(string $url): string
    {
        return ImageParser::getDirHash(
            parse_url(
                ImageParser::cleanImgSource($url),
                PHP_URL_PATH
            )
        );
    }

    /**
     * Checks if either GD or Imagick is loaded
     *
     * @throws HttpException
     */
    private function checkExtensionsLoaded(): void
    {
        if (! extension_loaded('gd')) {
            app('Log')::error('Required extension "GD" not available.');

            throw new HttpException(501, 'Required extension "GD" not available.');
        }
    }
}
