<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Admin\Rsi\CommLink\Channel;

use App\Models\Rsi\CommLink\Channel\Channel;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use Dingo\Api\Http\Response;
use Tests\Feature\Controller\Admin\AdminTestCase;

/**
 * Class Channel Controller Test Case
 */
class ChannelControllerTestCase extends AdminTestCase
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
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\Channel\ChannelController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.rsi.comm-links.channels.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.channels.index')->assertSee($this->channel->name);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\Channel\ChannelController::show
     * @covers \App\Models\Rsi\CommLink\Channel\Channel
     */
    public function testShow()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.rsi.comm-links.channels.show', $this->channel)
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.index')->assertSee(
                $this->commLinks->first()->title
            );
        }
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm Link Channel
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createSystemLanguages();

        $this->channel = factory(Channel::class)->create();

        $this->commLinks = factory(CommLink::class, 5)->create(['channel_id' => $this->channel->id])->each(
            function (CommLink $commLink) {
                $commLink->translations()->save(factory(CommLinkTranslation::class)->make());
            }
        );
    }
}
