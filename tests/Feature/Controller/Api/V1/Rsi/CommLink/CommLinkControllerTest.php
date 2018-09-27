<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 27.09.2018
 * Time: 12:18
 */

namespace Tests\Feature\Controller\Api\V1\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use Tests\Feature\Controller\Api\ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Web\Api\CommLinkController<extended>
 *
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\CommLinkTransformer<extended>
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
        'channel',
        'category',
        'series',
        'images',
        'links',
        'created_at',
    ];


    /**
     * Index Method Tests
     */

    /**
     * {@inheritdoc}
     */
    public function testIndexAll(int $allCount = 0)
    {
        parent::testIndexAll(CommLink::count());
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
     * Creates Faked Comm Links in DB
     */
    protected function setUp()
    {
        parent::setUp();

        factory(CommLink::class, 20)->create();
    }
}
