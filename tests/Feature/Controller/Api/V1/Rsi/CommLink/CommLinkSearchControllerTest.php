<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Api\V1\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Tests\Feature\Controller\Api\ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController<extended>
 *
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer<extended>
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Image\ImageTransformer<extended>
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Link\LinkTransformer<extended>
 *
 * @covers \App\Models\Rsi\CommLink\CommLink<extended>
 */
class CommLinkSearchControllerTest extends ApiTestCase
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
     * @var \Illuminate\Support\Collection
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
        self::markTestSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 5): void
    {
        self::markTestSkipped();
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexInvalidLimit(int $limit = -1): void
    {
        self::markTestSkipped();
    }


    /**
     * Show Method Tests
     */

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::searchByTitle
     */
    public function testSearchByTitle(): void
    {
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 1',
                ]
            )
        );
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 2',
                ]
            )
        );

        $response = $this->post(
            sprintf(
                '%s/search',
                static::BASE_API_ENDPOINT
            ),
            [
                'keyword' => 'Example',
            ]
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertSee('Example Title 1')
            ->assertSee('Example Title 2');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::searchByTitle
     */
    public function testSearchByTitlePartial(): void
    {
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 1',
                ]
            )
        );
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 2',
                ]
            )
        );

        $response = $this->post(
            sprintf(
                '%s/search',
                static::BASE_API_ENDPOINT
            ),
            [
                'keyword' => 'itle',
            ]
        );

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertSee('Example Title 1')
            ->assertSee('Example Title 2');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::searchByTitle
     */
    public function testSearchByTitleNone(): void
    {
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 1',
                ]
            )
        );
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 2',
                ]
            )
        );

        $response = $this->post(
            sprintf(
                '%s/search',
                static::BASE_API_ENDPOINT
            ),
            [
                'keyword' => 'MATCHNONE',
            ]
        );

        $response->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertDontSee('Example Title 1')
            ->assertDontSee('Example Title 2');
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::searchByTitle
     */
    public function testSearchByTitleNoBody(): void
    {
        $response = $this->post(
            sprintf(
                '%s/search',
                static::BASE_API_ENDPOINT
            ),
            [
            ]
        );

        $response->assertStatus(422);
    }


    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\CommLinkSearchController::searchByTitle
     */
    public function testSearchByTitleTooShort(): void
    {
        $response = $this->post(
            sprintf(
                '%s/search',
                static::BASE_API_ENDPOINT
            ),
            [
                'keyword' => 'ab',
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

        $this->commLinks = factory(CommLink::class, 20)->create();
    }
}
