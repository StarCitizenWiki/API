<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Admin\Rsi\CommLink;

use App\Jobs\Rsi\CommLink\Parser\ParseCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
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
     * @covers \App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy::view
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.rsi.comm-links.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.index')->assertSee($this->commLink->title);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::show
     * @covers \App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy::view
     */
    public function testShow()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.rsi.comm-links.show', $this->commLink->getRouteKey())
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.show')
                ->assertSee($this->commLink->title)
                ->assertSee(__('en_EN'))
                ->assertSee(__('de_DE'));

            if ($this->admin->can('web.admin.rsi.comm-links.update')) {
                $response->assertSee(__('Bearbeiten'));
            }
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::edit
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::getCommLinkVersions
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::processCommLinkVersions
     *
     * @covers \App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy::update
     */
    public function testEdit()
    {
        Storage::disk('comm_links')->put(
            "{$this->commLink->cig_id}/{$this->commLink->file}",
            <<<EOF
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <div id="post">
            <div class="segment">
                Preview Content
            </div>
        </div>
    </body>
</html>
EOF
        );

        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.rsi.comm-links.edit', $this->commLink->getRouteKey())
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
     * @covers \App\Http\Requests\CommLinkRequest
     *
     * @covers \App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy::update
     *
     * @covers \App\Models\Rsi\CommLink\CommLink
     * @covers \App\Models\System\ModelChangelog
     * @covers \App\Events\ModelUpdating
     * @covers \App\Listeners\ModelUpdating
     */
    public function testUpdate()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.rsi.comm-links.update', $this->commLink),
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
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::update
     *
     * @covers \App\Http\Requests\CommLinkRequest
     *
     * @covers \App\Policies\Web\Admin\Rsi\CommLink\CommLinkPolicy::update
     *
     * @covers \App\Models\Rsi\CommLink\CommLink
     * @covers \App\Models\System\ModelChangelog
     * @covers \App\Events\ModelUpdating
     * @covers \App\Listeners\ModelUpdating
     */
    public function testUpdateVersion()
    {
        // TODO Refactor?
        if (!$this->admin->can('web.admin.rsi.comm-links.update_settings')) {
            $this->markTestSkipped('Admin has no Version Update Permissions');

            return;
        }

        Bus::fake();

        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->patch(
            route('web.admin.rsi.comm-links.update', $this->commLink),
            [
                'title' => $this->commLink->title,
                'url' => $this->commLink->url,
                'created_at' => $this->commLink->created_at,
                'de_DE' => 'Deutscher Text',
                'en_EN' => 'Bla',

                'version' => '2012-01-01_000000.html',
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update_version']);

        if ($response->status() === Response::HTTP_OK) {
            Bus::assertDispatched(ParseCommLink::class);

            $response->assertViewIs('admin.rsi.comm_links.show')
                ->assertSee(__('Comm Link Import gestartet'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Rsi\CommLink\CommLinkController::preview
     *
     * @covers \App\Jobs\Rsi\CommLink\Parser\Element\Content
     */
    public function testPreview()
    {
        $version = '2012-01-01_000000';

        Storage::disk('comm_links')->put(
            "{$this->commLink->cig_id}/{$version}.html",
            <<<EOF
<html>
    <head>
        <title>Test</title>
    </head>
    <body>
        <div id="post">
            <div class="segment">
                Preview Content
            </div>
        </div>
    </body>
</html>
EOF
        );

        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.rsi.comm-links.preview', [$this->commLink, $version])
        );

        $response->assertStatus(static::RESPONSE_STATUSES['preview']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.rsi.comm_links.preview')
                ->assertSee(__('Preview Content'));
        }
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
