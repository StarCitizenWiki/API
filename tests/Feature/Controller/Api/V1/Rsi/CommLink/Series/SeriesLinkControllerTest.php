<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\Rsi\CommLink\Series;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\Series\Series;
use Tests\Feature\Controller\Api\ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\Series\SeriesController<extended>
 *
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Series\SeriesTransformer<extended>
 *
 * @covers \App\Models\Rsi\CommLink\Series\Series<extended>
 */
class SeriesLinkControllerTest extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const MODEL_DEFAULT_PAGINATION_COUNT = 15;

    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/comm-links/series';

    /**
     * {@inheritdoc}
     */
    protected $structure = [
        'name',
        'slug',
        'api_url',
    ];

    /**
     * @var \Illuminate\Support\Collection
     */
    private $series;

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
    public function testIndexAll(int $allCount = 0)
    {
        parent::testIndexAll(Series::count());
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexPaginatedCustom(int $limit = 5)
    {
        parent::testIndexPaginatedCustom($limit);
    }

    /**
     * {@inheritdoc}
     */
    public function testIndexInvalidLimit(int $limit = -1)
    {
        parent::testIndexInvalidLimit($limit);
    }


    /**
     * Show Method Tests
     */

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\Series\SeriesController::show
     */
    public function testShow()
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                $this->series->first()->slug
            )
        );

        $response->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'title',
                            'rsi_url',
                            'api_url',
                            'channel',
                            'category',
                            'series',
                            'images',
                            'links',
                            'created_at',
                        ],
                    ],
                ]
            )
            ->assertJsonCount(
                $this->series->first()->commLinks->count(),
                'data'
            );
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\Series\SeriesController::show
     */
    public function testShowNotFound()
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                static::NOT_EXISTENT_NAME
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

        $this->series = factory(Series::class, 20)->create();
        $this->series->first(
            function (Series $series) {
                $this->commLinks = factory(CommLink::class, 5)->create()->each(
                    function (CommLink $commLink) use ($series) {
                        $commLink->series_id = $series->id;
                        $commLink->save();
                    }
                );
            }
        );
    }
}
