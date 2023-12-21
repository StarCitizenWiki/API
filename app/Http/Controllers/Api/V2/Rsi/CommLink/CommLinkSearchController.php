<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V2\Rsi\CommLink;

use App\Http\Controllers\Api\V2\AbstractApiV2Controller;
use App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest;
use App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest;
use App\Http\Resources\Rsi\CommLink\CommLinkResource;
use App\Http\Resources\Rsi\CommLink\Image\ImageHashResource;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Image\ImageHash as ImageHashModel;
use App\Services\ImageHash\Implementations\PerceptualHash2;
use App\Services\Parser\CommLink\Image as ImageParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\AverageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use OpenApi\Attributes as OA;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CommLinkSearchController extends AbstractApiV2Controller
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
                )
            ]
        ),
        tags: ['Comm-Links', 'RSI-Website'],
        parameters: [
            new OA\Parameter(ref: '#/components/parameters/locale'),
        ],
        responses: [
            new OA\Response(
                ref: '#/components/schemas/comm_link_v2',
                response: 200,
                description: 'A singular Comm-Link',
            ),
            new OA\Response(
                response: 404,
                description: 'No Comm-Link with found.',
            )
        ],
    )]
    public function searchByTitle(Request $request): AnonymousResourceCollection
    {
        $request->validate((new CommLinkSearchRequest())->rules());

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
                )
            ]
        ),
        tags: ['Comm-Links', 'RSI-Website'],
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
            )
        ],
    )]
    public function reverseImageLinkSearch(Request $request): AnonymousResourceCollection
    {
        $request->validate((new ReverseImageLinkSearchRequest())->rules());

        /** @var Image $image */
        $image = Image::query()
            ->where(
                'dir',
                $this->getDirHashFromImageUrl($request->get('url', ''))
            )
            ->firstOr(
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
        tags: ['Comm-Links', 'RSI-Website'],
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
            new OA\Parameter(
                name: 'method',
                in: 'query',
                required: true,
                schema: new OA\Schema(
                    description: 'Available Comm-Link includes',
                    type: 'array',
                    items: new OA\Items(
                        type: 'string',
                        default: 'perceptual',
                        enum: [
                            'perceptual',
                            'difference',
                            'average',
                        ]
                    ),
                ),
                explode: false,
            )
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
            )
        ],
    )]
    public function reverseImageSearch(Request $request): AnonymousResourceCollection
    {
        $this->checkExtensionsLoaded();

        $request->validate((new ReverseImageSearchRequest())->rules());

        $hashData = [
            'perceptual_hash' => $this->hashImage(new ImageHash(new PerceptualHash2()), $request->file('image')),
            'difference_hash' => $this->hashImage(new ImageHash(new AverageHash()), $request->file('image')),
            'average_hash' => $this->hashImage(new ImageHash(new DifferenceHash()), $request->file('image')),
        ];

        $data = $this->getResultImages($hashData, (int)$request->get('similarity'));

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

        $image = Image::query()->find($image);

        $hashData = [
            'perceptual_hash' => $image->hash->perceptual_hash,
            'average_hash' => $image->hash->difference_hash,
            'difference_hash' => $image->hash->average_hash,
        ];

        $data = $this->getResultImages($hashData, $similarity ?? 50, true);

        return ImageHashResource::collection($data);
    }

    private function getResultImages(array $hashData, int $similarity = 50, bool $fromDB = false)
    {
        return $this->getHashesFromDatabase($hashData, $fromDB)
            ->map(
                function (object $data) {
                    $id = $data->comm_link_image_id;
                    $image = Image::query()->find($id);

                    $image->similarity = max(
                        round((1 - ($data->a_distance / 64)) * 100),
                        round((1 - ($data->d_distance / 64)) * 100),
                        round((1 - ($data->p_distance / 64)) * 100)
                    );

                    $min = min(
                        $data->a_distance,
                        $data->d_distance,
                        $data->p_distance
                    );

                    $image->similarity_method = $min === $data->a_distance ? __('Basierend auf dem vorherigen Pixel') :
                        ($min === $data->d_distance ? __('Basierend auf der durchschnittlichen Bildfarbe') : __('Basierend auf Merkmalen des Inhalts'));

                    return $image;
                }
            )
            ->sortByDesc('similarity')
            ->filter(fn (object $image) => $image->similarity >= 100 - $similarity);
    }

    /**
     * Returns the RSI directory hash of an image url
     *
     * @param string $url The RSI Media URl
     *
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
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            app('Log')::error('Required extension "GD" or "Imagick" not available.');

            throw new HttpException(501, 'Required extension "GD" or "Imagick" not available.');
        }
    }

    /**
     * Hashes an uploaded image
     *
     * @param ImageHash $hasher The hasher with set hash method
     * @param UploadedFile $file The uploaded file
     *
     * @return array
     */
    private function hashImage(ImageHash $hasher, $file): array
    {
        $hash = $hasher->hash($file)->toHex();

        return ['hash' => $hash];
    }

    /**
     * Return hashes based on database connection type
     *
     * @param array $hashData
     *
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    private function getHashesFromDatabase(array $hashData, bool $fromDB = false)
    {
        // Since SQLITE does not support the BIT_COUNT operation we only search for exact hash matches
        if (config('database.default') === 'sqlite') {
            return $this->getHashesFromSQLiteStore($hashData['perceptual_hash']);
        }

        return $this->getHashesFromSQLStore($hashData, $fromDB);
    }

    /**
     * Get the image hashes that equal the provided hash
     *
     * @param string $hash The image hash
     *
     * @return Builder[]|Collection
     */
    private function getHashesFromSQLiteStore(string $hash)
    {
        return ImageHashModel::query()
            ->where('perceptual_hash', $hash)
            ->get('comm_link_image_id');
    }

    /**
     * Get the image hashes matching the provided hash method and hamming distance
     *
     * @param array $hashes Image hash split in the middle and hex decoded
     * @param bool $fromDB Wheter the provided hashes come directly from the DB (i.e. they are not hex)
     *
     * @return \Illuminate\Support\Collection
     */
    private function getHashesFromSQLStore(array $hashes, bool $fromDB = false): \Illuminate\Support\Collection
    {
        $query = 'CAST(CONV(?, 16, 10) AS UNSIGNED INTEGER)';
        if ($fromDB) {
            $query = '?';
        }
        return ImageHashModel::query()
            ->with('image')
            ->select('comm_link_image_hashes.comm_link_image_id')
            ->selectRaw(
                'BIT_COUNT(perceptual_hash ^ ' . $query . ') AS p_distance',
                [$hashes['perceptual_hash']]
            )
            ->selectRaw(
                'BIT_COUNT(average_hash ^ ' . $query . ') AS a_distance',
                [$hashes['difference_hash']]
            )
            ->selectRaw(
                'BIT_COUNT(difference_hash ^ ' . $query . ') AS d_distance',
                [$hashes['average_hash']]
            )
            ->join('comm_link_images', 'comm_link_image_hashes.comm_link_image_id', '=', 'comm_link_images.id')
            ->join('comm_link_image_metadata', 'comm_link_image_metadata.comm_link_image_id', '=', 'comm_link_images.id')
            // Filter out small images
            ->where('size', '>=', 250 * 1024)
            ->orderBy('p_distance')
            ->limit(50)
            ->get();
    }
}
