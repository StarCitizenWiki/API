<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Image\Image;
use App\Models\Rsi\CommLink\Link\Link;
use Tests\Feature\Controller\Api\V1\ApiTestCase;
use Illuminate\Support\Collection;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkController<extended>
 *
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer<extended>
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Image\ImageTransformer<extended>
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Link\LinkTransformer<extended>
 *
 * @covers \App\Models\Rsi\CommLink\CommLink<extended>
 */
class CommLinkControllerTest extends ApiTestCase
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
        'images',
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
        parent::testIndexAll(CommLink::count());
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 5): void
    {
        parent::testIndexPaginatedCustom($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexInvalidLimit(int $limit = -1): void
    {
        parent::testIndexInvalidLimit($limit);
    }


    /**
     * Show Method Tests
     */

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkController::show
     */
    public function testShow(): void
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                $this->commLinks->first()->cig_id
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                ]
            )
            ->assertJsonCount(
                $this->commLinks->first()->images->count(),
                'data.images.data'
            )
            ->assertJsonCount(
                $this->commLinks->first()->links->count(),
                'data.links.data'
            );
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkController::show
     * @covers \App\Transformers\Api\V1\Rsi\CommLink\Image\ImageTransformer::transform
     */
    public function testShowIncludeImageHashes(): void
    {
        $this->commLinks->each(
            function (CommLink $commLink) {
                $commLink->images()->saveMany(Image::factory()->count(3)->make());
                $commLink->links()->saveMany(Link::factory()->count(3)->make());
            }
        );

        $structure = $this->structure;
        $structure['images'] = [
            'data' => [
                '*' => [
                    'rsi_url',
                    'api_url',
                    'alt',
                    'size',
                    'mime_type',
                    'last_modified',
                    'hashes' => [
                        'perceptual_hash',
                        'difference_hash',
                        'average_hash',
                    ],
                ],
            ],
        ];

        $response = $this->get(
            sprintf(
                '%s/%s?include=images.hashes',
                static::BASE_API_ENDPOINT,
                $this->commLinks->first()->cig_id
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' =>
                        $structure
                    ,
                ]
            )
            ->assertJsonCount(
                $this->commLinks->first()->images->count(),
                'data.images.data'
            )
            ->assertJsonCount(
                $this->commLinks->first()->links->count(),
                'data.links.data'
            )->assertSee('perceptual_hash');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkController::show
     */
    public function testShowNotFound(): void
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                static::NOT_EXISTENT_ID
            )
        );

        $response->assertNotFound();
    }

    /**
     * Creates Faked Comm-Links in DB
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->commLinks = CommLink::factory()->count(20)->create();
    }
}
