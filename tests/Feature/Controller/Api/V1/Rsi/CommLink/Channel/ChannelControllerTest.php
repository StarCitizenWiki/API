<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Api\V1\Rsi\CommLink\Channel;

use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use Tests\Feature\Controller\Api\ApiTestCase;

/**
 * {@inheritdoc}
 *
 * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\Channel\ChannelController<extended>
 *
 * @covers \App\Transformers\Api\V1\Rsi\CommLink\Channel\ChannelTransformer<extended>
 *
 * @covers \App\Models\Rsi\CommLink\Channel\Channel<extended>
 */
class ChannelControllerTest extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected const MODEL_DEFAULT_PAGINATION_COUNT = 15;

    /**
     * {@inheritdoc}
     */
    protected const BASE_API_ENDPOINT = '/api/comm-links/channels';

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
    private $channels;

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
        parent::testIndexAll(Channel::count());
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
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\Channel\ChannelController::show
     */
    public function testShow()
    {
        $response = $this->get(
            sprintf(
                '%s/%s',
                static::BASE_API_ENDPOINT,
                $this->channels->first()->slug
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
                $this->channels->first()->commLinks->count(),
                'data'
            );
    }

    /**
     * @covers \App\Http\Controllers\Api\V1\Rsi\CommLink\Channel\ChannelController::show
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

        $this->channels = factory(Channel::class, 20)->create();
        $this->channels->first(
            function (Channel $channel) {
                $this->commLinks = factory(CommLink::class, 5)->create()->each(
                    function (CommLink $commLink) use ($channel) {
                        $commLink->channel_id = $channel->id;
                        $commLink->save();
                    }
                );
            }
        );
    }
}
