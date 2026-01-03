<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Rsi\CommLink;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Http\Resources\Rsi\CommLink\Image\ImageHashResource;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash as ImageHashModel;
use App\Services\ImageHash\PdqHasher;
use App\Services\Parser\CommLink\Image as ImageParser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CommLinkSearchController extends Controller
{
    #[OA\Post(
        path: '/api/v2/comm-links/search',
        requestBody: new OA\RequestBody(
            description: '(Partial) Comm-Link Title or ID',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                    ),
                    example: '{"query": "Banu Merchantman"}',
                ),
            ]
        ),
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'A singular Comm-Link',
                content: new OA\JsonContent(ref: '#/components/schemas/comm_link_v2')
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link with found.',
            ),
        ],
    )]
    public function searchByTitle(Request $request): AnonymousResourceCollection
    {
        $request->validate((new CommLinkSearchRequest)->rules());

        $query = $request->get('keyword') ?? $request->get('query');

        $commLinks = QueryBuilder::for(CommLink::class)
            ->where('title', 'LIKE', sprintf('%%%s%%', $query))
            ->orWhere('cig_id', 'LIKE', "%{$query}%")
            ->limit(100)
            ->allowedIncludes(CommLinkResource::validIncludes())
            ->allowedFilters([
                AllowedFilter::exact('category', 'category.name'),
                AllowedFilter::exact('series', 'series.name'),
                AllowedFilter::exact('channel', 'channel.name'),
            ])
            ->get();

        return CommLinkResource::collection($commLinks);
    }

    #[OA\Post(
        path: '/api/v2/comm-links/reverse-image-link-search',
        requestBody: new OA\RequestBody(
            description: 'Url to an image hosted on (media.)robertsspaceindustries.com',
            required: true,
            content: [
                'url' => new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
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
                    items: new OA\Items(ref: '#/components/schemas/comm_link_link_v2')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link found.',
            ),
        ],
    )]
    public function reverseImageLinkSearch(Request $request): AnonymousResourceCollection
    {
        $request->validate((new ReverseImageLinkSearchRequest)->rules());

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

        return CommLinkResource::collection(optional($image)->commLinks);
    }

    #[OA\Post(
        path: '/api/v2/comm-links/reverse-image-search',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                'image' => new OA\MediaType(
                    mediaType: 'application/octet-stream',
                    schema: new OA\Schema(
                        description: 'The image to reverse-search',
                        type: 'string',
                        format: 'binary',
                    ),
                ),
            ]
        ),
        tags: ['Comm-Links', 'RSI-Website', 'Search'],
        parameters: [
            new OA\Parameter(
                name: 'similarity',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    type: 'integer',
                    maximum: 100,
                    minimum: 1,
                )
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of Comm-Links that use that image',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/comm_link_link_v2')
                )
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link found.',
            ),
        ],
    )]
    public function reverseImageSearch(Request $request, PdqHasher $hasher): AnonymousResourceCollection
    {
        $this->checkExtensionsLoaded();

        $request->validate((new ReverseImageSearchRequest)->rules());

        try {
            $hashResult = $hasher->hashContents($request->file('image')->get());
        } catch (\RuntimeException $exception) {
            throw new HttpException(422, $exception->getMessage(), $exception);
        }

        $data = ImageHashModel::similarImagesForHash(
            $hashResult->toBitString(),
            (int) $request->get('similarity')
        );

        return ImageHashResource::collection($data);
    }

    public function similarSearch(Request $request)
    {
        ['image' => $image, 'similarity' => $similarity] = Validator::validate(
            [
                'image' => $request->image,
                'similarity' => $request->similarity,
            ],
            [
                'image' => 'required|int|exists:comm_link_images,id',
                'similarity' => 'nullable|int|min:1|max:100',
            ]
        );

        /** @var Image $image */
        $image = Image::query()->find($image);

        return ImageHashResource::collection($image->similarImages($similarity ?? 50, 50));
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
