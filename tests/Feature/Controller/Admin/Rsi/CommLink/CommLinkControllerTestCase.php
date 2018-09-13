<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Admin\Rsi\CommLink;

use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use Dingo\Api\Http\Response;
use Tests\Feature\Controller\Admin\AdminTestCase;

/**
 * Class Comm Link Controller Test Case
 */
class CommLinkControllerTestCase extends AdminTestCase
{
    /**
     * @var \App\Models\Rsi\CommLink\CommLink
     */
    protected $commLink;

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.rsi.comm_links.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.index')->assertSee($this->commLink->title);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::show
     */
    public function testShow()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.rsi.comm_links.show', $this->commLink->getRouteKey())
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.show')
                ->assertSee($this->commLink->title)
                ->assertSee(__('en_EN'))
                ->assertSee(__('de_DE'));

            if ($this->admin->can('web.admin.rsi.comm_links.update')) {
                $response->assertSee(__('Bearbeiten'));
            }
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::edit
     */
    public function testEdit()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.rsi.comm_links.edit', $this->commLink->getRouteKey())
        );

        $response->assertStatus(static::RESPONSE_STATUSES['edit']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.edit')
                ->assertSee(__('Comm Link bearbeiten'))
                ->assertSee(__('Lesen'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::update
     *
     * @covers \App\Models\Rsi\CommLink\CommLink
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.rsi.comm_links.update', $this->commLink->getRouteKey()),
            [
                'title' => $this->commLink->title,
                'url' => $this->commLink->url,
                'created_at' => $this->commLink->created_at,
                'de_DE' => 'Deutscher Text',
                'en_EN' => 'Bla',
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm Link
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createSystemLanguages();
        $this->commLink = factory(CommLink::class)->create();
        $this->commLink->translations()->save(factory(CommLinkTranslation::class)->make());
    }
}
