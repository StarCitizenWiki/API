<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\Rsi\CommLink\Image;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Tests\Feature\Controller\Api\ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController<extended>
 *
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer<extended>
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Image\ImageTransformer<extended>
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Image\ImageHashTransformer<extended>
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Link\LinkTransformer<extended>
 *
 * @covers \App\Models\Rsi\CommLink\CommLink<extended>
 */
class ImageControllerTest extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const MODEL_DEFAULT_PAGINATION_COUNT = 15;

    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/comm-links';

    /**
     * {@inheritdoc}
     */
    protected $structure = [
        'id',
        'title',
        'rsi_url',
        'api_url',
        'api_public_url',
        'channel',
        'category',
        'series',
        'images' => [
            'data' => [
                '*' => [
                    'rsi_url',
                    'api_url',
                    'alt',
                    'size',
                    'mime_type',
                    'last_modified',
                ],
            ],
        ],
        'links',
        'comment_count',
        'created_at',
    ];

    /**
     * @var Collection
     */
    private $commLinks;

    /**
     * Index Method Tests
     */

    /**
     * {@inheritdoc}
     */
    public function testIndexAll(int $allCount = 0): void
    {
        self::markTestSkipped('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 5): void
    {
        self::markTestSkipped('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedDefault(int $limit = 5): void
    {
        self::markTestSkipped('Not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexInvalidLimit(int $limit = -1): void
    {
        self::markTestSkipped('Not implemented.');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageLinkSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest
     */
    public function testSearchWithLink(): void
    {
        /** @var CommLink $commLink */
        $commLink = CommLink::query()->first();

        $image = $commLink->images[0];

        $response = $this->post(
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-link-search'),
            [
                'url' => $image->url,
            ]
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        '*' => $this->structure,
                    ],
                ]
            )
            ->assertSee($commLink->title, false);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageLinkSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest
     */
    public function testSearchWithInvalidLink(): void
    {
        $response = $this->post(
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-link-search'),
            [
                'url' => 'NOT AN URL',
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageLinkSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageLinkSearchRequest
     */
    public function testSearchWithMissingLink(): void
    {
        $response = $this->post(
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-link-search'),
            [

            ]
        );

        $response->assertStatus(422);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest
     * @covers \App\Transformers\Api\V1\Rsi\CommLink\Image\ImageTransformer::transform
     * TODO Fix Github Actions
     */
    public function testSearchImage(): void
    {
        self::markTestSkipped('Fails in GitHub Actions.');

        /** @var CommLink $commLink */
        $commLink = CommLink::query()->first();

        /** @var Image $image */
        $image = $commLink->images()->create(
            [
                'src' => '/none/none.jpg',
                'alt' => 'none',
                'dir' => 'none',
            ]
        );

        $image->metadata()->create(
            [
                'size' => 16831,
                'mime' => 'image/jpeg',
                'last_modified' => '2013-07-19 05:30:44',
            ]
        );

        // Known hashes for the file
        $image->hash()->create(
            [
                'perceptual_hash' => '035fa9d1fe3d6a1e',
                'p_hash_1' => hexdec(str_split('035fa9d1fe3d6a1e', 8)[0]),
                'p_hash_2' => hexdec(str_split('035fa9d1fe3d6a1e', 8)[1]),

                'difference_hash' => 'ccc4842666465417',
                'd_hash_1' => 0,
                'd_hash_2' => 0,

                'average_hash' => '01091d7f5d1d185f',
                'a_hash_1' => 0,
                'a_hash_2' => 0,
            ]
        );

        $response = $this->json(
            'post',
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-search'),
            [
                'image' => new UploadedFile(
                    storage_path('framework/testing/ChrisRobertsWCfilm1.jpg'),
                    'ChrisRobertsWCfilm1.jpg',
                    'image/jpeg',
                    null,
                    true
                ),
                'method' => 'perceptual',
                'similarity' => 10,
            ]
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'rsi_url',
                            'api_url',
                            'alt',
                            'size',
                            'mime_type',
                            'last_modified',
                            'similarity',
                            'hashes',
                            'commLinks',
                        ],
                    ],
                ]
            );
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest
     */
    public function testSearchImageMissing(): void
    {
        $response = $this->json(
            'post',
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-search'),
            [
                'image' => '',
                'method' => 'perceptual',
                'similarity' => 10,
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest
     */
    public function testSearchInvalidMethod(): void
    {
        $response = $this->json(
            'post',
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-search'),
            [
                'image' => new UploadedFile(
                    storage_path('framework/testing/ChrisRobertsWCfilm1.jpg'),
                    'ChrisRobertsWCfilm1.jpg',
                    'image/jpeg',
                    null,
                    true
                ),
                'method' => 'invalid',
                'similarity' => 10,
            ]
        );

        $response->assertStatus(422);
    }


    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest
     */
    public function testSearchInvalidNegativeSimilarity(): void
    {
        $response = $this->json(
            'post',
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-search'),
            [
                'image' => new UploadedFile(
                    storage_path('framework/testing/ChrisRobertsWCfilm1.jpg'),
                    'ChrisRobertsWCfilm1.jpg',
                    'image/jpeg',
                    null,
                    true
                ),
                'method' => 'average',
                'similarity' => -10,
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest
     */
    public function testSearchInvalidSimilarity(): void
    {
        $response = $this->json(
            'post',
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-search'),
            [
                'image' => new UploadedFile(
                    storage_path('framework/testing/ChrisRobertsWCfilm1.jpg'),
                    'ChrisRobertsWCfilm1.jpg',
                    'image/jpeg',
                    null,
                    true
                ),
                'method' => 'difference',
                'similarity' => 101,
            ]
        );

        $response->assertStatus(422);
    }


    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::reverseImageSearch
     * @covers \App\Http\Requests\Rsi\CommLink\ReverseImageSearchRequest
     */
    public function testSearchNonNumericSimilarity(): void
    {
        $response = $this->json(
            'post',
            sprintf('%s/%s', static::BASE_API_ENDPOINT, 'reverse-image-search'),
            [
                'image' => new UploadedFile(
                    storage_path('framework/testing/ChrisRobertsWCfilm1.jpg'),
                    'ChrisRobertsWCfilm1.jpg',
                    'image/jpeg',
                    null,
                    true
                ),
                'method' => 'difference',
                'similarity' => 'Ten',
            ]
        );

        $response->assertStatus(422);
    }

    /**
     * Creates Faked Comm-Links in DB
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->commLinks = CommLink::factory()->count(20)->create();
        $this->commLinks->each(
            function (CommLink $commLink) {
                $commLink->images()->saveMany(Image::factory()->count(3)->make());
                $commLink->links()->saveMany(Link::factory()->count(3)->make());
            }
        );
    }
}
