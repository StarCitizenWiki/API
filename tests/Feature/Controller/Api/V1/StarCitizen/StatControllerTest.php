<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\StarCitizen;

use App\Models\Api\StarCitizen\Stat\Stat;
use Tests\Feature\Controller\Api\ApiTestCase as ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\StarCitizen\Stat\StatController<extended>
 *
 * @covers \App\Transformers\Api\V1\StarCitizen\Stat\StatTransformer<extended>
 *
 * @covers \App\Models\Api\StarCitizen\Stat\Stat<extended>
 */
class StatControllerTest extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const MODEL_DEFAULT_PAGINATION_COUNT = 10;

    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/stats';

    /**
     * {@inheritdoc}
     */
    protected $structure = [
        'funds',
        'fans',
        'fleet',
        'timestamp',
    ];


    /**
     * Index Method Tests
     */

    /**
     * {@inheritdoc}
     */
    public function testIndexAll(int $allCount = 0)
    {
        parent::testIndexAll(Stat::count());
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
     * Tests Stats from Interfaces
     *
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Stat\StatController::latest
     */
    public function testLatestApiView()
    {
        $response = $this->get(sprintf('%s/latest', static::BASE_API_ENDPOINT));
        $response->assertOk()
            ->assertSee('data')
            ->assertSee('funds')
            ->assertSee('fleet')
            ->assertSee('fans')
            ->assertSee('timestamp')
            ->assertJsonCount(4, 'data')
            ->assertJsonStructure(
                [
                    'data' => $this->structure,
                ]
            )
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('x-ratelimit-limit')
            ->assertHeader('etag');
    }


    /**
     * Creates Faked Stats in DB
     */
    protected function setUp()
    {
        parent::setUp();

        factory(Stat::class, 20)->create();
    }
}
