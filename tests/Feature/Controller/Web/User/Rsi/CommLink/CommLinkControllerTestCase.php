<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink;

use App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController;
use App\Jobs\Rsi\CommLink\Import\ImportCommLink;
use App\Models\Rsi\CommLink\CommLink;
use App\Models\Rsi\CommLink\CommLinkTranslation;
use Dingo\Api\Dispatcher;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Controller\Web\User\UserTestCase;


/**
 * Class Comm-Link Controller Test Case.
 */
class CommLinkControllerTestCase extends UserTestCase
{
    /**
     * @var \App\Models\Rsi\CommLink\CommLink
     */
    protected $commLink;

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::index
     * @covers \App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy::view
     */
    public function testIndex(): void
    {
        $response = $this->actingAs($this->user)->get(route('web.user.rsi.comm-links.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if (Response::HTTP_OK === $response->status()) {
            $response->assertViewIs('user.rsi.comm_links.index')->assertSee($this->commLink->title);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::show
     * @covers \App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy::view
     */
    public function testShow(): void
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.rsi.comm-links.show', $this->commLink->getRouteKey())
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show']);
        if (Response::HTTP_OK === $response->status()) {
            $response->assertViewIs('user.rsi.comm_links.show')
                ->assertSee($this->commLink->title)
                ->assertSee(__('en_EN'))
                ->assertSee(__('de_DE'));

            if ($this->user->can('web.user.rsi.comm-links.update')) {
                $response->assertSee(__('Bearbeiten'));
            }
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::edit
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::getCommLinkVersions
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::processCommLinkVersions
     *
     * @covers \App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy::update
     */
    public function testEdit(): void
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

        $response = $this->actingAs($this->user)->get(
            route('web.user.rsi.comm-links.edit', $this->commLink->getRouteKey())
        );

        $response->assertStatus(static::RESPONSE_STATUSES['edit']);
        if (Response::HTTP_OK === $response->status()) {
            $response->assertViewIs('user.rsi.comm_links.edit')
                ->assertSee(__('Comm-Link bearbeiten'))
                ->assertSee(__('Lesen'))
                ->assertSee(__('Speichern'));
        }

        Storage::disk('comm_links')->delete("{$this->commLink->cig_id}/{$this->commLink->file}");
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::update
     *
     * @covers \App\Http\Requests\Rsi\CommLink\CommLinkRequest
     *
     * @covers \App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy::update
     *
     * @covers \App\Models\Rsi\CommLink\CommLink
     * @covers \App\Models\System\ModelChangelog
     * @covers \App\Events\ModelUpdating
     * @covers \App\Listeners\ModelUpdating
     */
    public function testUpdate(): void
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.user.rsi.comm-links.update', $this->commLink),
            [
                'title' => $this->commLink->title,
                'url' => $this->commLink->url,
                'created_at' => $this->commLink->created_at,
                'de_DE' => 'Deutscher Text',
                'en_EN' => 'Bla',
                'channel' => $this->commLink->channel->id,
                'series' => $this->commLink->series->id,
                'category' => $this->commLink->category->id,
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::update
     *
     * @covers \App\Http\Requests\Rsi\CommLink\CommLinkRequest
     *
     * @covers \App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy::update
     *
     * @covers \App\Models\Rsi\CommLink\CommLink
     * @covers \App\Models\System\ModelChangelog
     * @covers \App\Events\ModelUpdating
     * @covers \App\Listeners\ModelUpdating
     */
    public function testUpdateVersion(): void
    {
        $this->markTestSkipped();

        return;

        Bus::fake();

        $response = $this->actingAs($this->user)->followingRedirects()->patch(
            route('web.user.rsi.comm-links.update', $this->commLink),
            [
                'title' => $this->commLink->title,
                'url' => $this->commLink->url,
                'created_at' => $this->commLink->created_at,
                'de_DE' => 'Deutscher Text',
                'en_EN' => 'Bla',
                'changeVersion' => '',
                'version' => '2012-01-01_000000.html',
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update_version']);

        if (Response::HTTP_OK === $response->status()) {
            Bus::assertDispatched(ImportCommLink::class);

            $response->assertViewIs('user.rsi.comm_links.show')
                ->assertSee(__('Comm-Link Import gestartet'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController::preview
     *
     * @covers \App\Jobs\Rsi\CommLink\Import\Element\Content
     */
    public function testPreview(): void
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

        $response = $this->actingAs($this->user)->get(
            route('web.user.rsi.comm-links.preview', [$this->commLink, $version])
        );

        $response->assertStatus(static::RESPONSE_STATUSES['preview']);

        if (Response::HTTP_OK === $response->status()) {
            $response->assertViewIs('user.rsi.comm_links.preview')
                ->assertSee(__('Preview Content'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkController
     */
    public function testConstructor(): void
    {
        $controller = $this->getMockBuilder(CommLinkController::class)->disableOriginalConstructor()->getMock();
        $controller->expects(self::once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(CommLinkController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller, app(Dispatcher::class));
    }

    /**
     * {@inheritdoc}
     * Creates needed Comm-Link.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        $this->commLink = CommLink::factory()->create();
        $this->commLink->translations()->save(CommLinkTranslation::factory()->german()->make());
    }
}
