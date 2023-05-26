<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink\Channel;

use App\Http\Controllers\Web\User\Rsi\CommLink\Channel\ChannelController;
use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class Channel Controller Test Case
 */
class ChannelControllerTestCase extends UserTestCase
{
    /**
     * @var \App\Models\Rsi\CommLink\Channel\Channel
     */
    protected $channel;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $commLinks;

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Channel\ChannelController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.rsi.comm-links.channels.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.channels.index')->assertSee($this->channel->name);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Channel\ChannelController::show
     * @covers \App\Models\Rsi\CommLink\Channel\Channel
     */
    public function testShow()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.rsi.comm-links.channels.show', $this->channel)
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.index')->assertSee(
                $this->commLinks->first()->title
            );
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\Channel\ChannelController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(ChannelController::class)->disableOriginalConstructor()->getMock();
        $controller->expects(self::once())->method('middleware')->with('auth');
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm-Link Channel
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();

        $this->channel = Channel::factory()->create();

        $this->commLinks = CommLink::factory()->count(5)->create(['channel_id' => $this->channel->id]);
    }
}
