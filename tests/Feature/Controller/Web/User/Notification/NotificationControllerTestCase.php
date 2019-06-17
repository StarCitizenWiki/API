<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Web\User\Notification;

use App\Http\Controllers\Web\User\Notification\NotificationController;
use App\Mail\NotificationEmail;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class AbstractBaseNotificationControllerTest
 */
class NotificationControllerTestCase extends UserTestCase
{
    /**
     * @var array
     */
    protected $notifications;

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.notifications.index'));

        $response->assertStatus(static::RESPONSE_STATUSES['index']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.notifications.index')->assertSee(__('Benachrichtigungen'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::create
     */
    public function testCreate()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.notifications.create'));

        $response->assertStatus(static::RESPONSE_STATUSES['create']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.notifications.create')
                ->assertSee(__('Benachrichtigung hinzufügen'))
                ->assertSee(__('Inhalt'))
                ->assertSee(__('Typ'))
                ->assertSee(__('Reihenfolge'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::edit
     */
    public function testEdit()
    {
        $notification = $this->notifications[0];

        $response = $this->actingAs($this->user)->get(
            route('web.user.notifications.edit', $notification->getRouteKey())
        );

        $response->assertStatus(static::RESPONSE_STATUSES['edit']);
        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.notifications.edit')
                ->assertSee(__('Benachrichtigung bearbeiten'))
                ->assertSee(__('Inhalt'))
                ->assertSee(__('Typ'))
                ->assertSee(__('Reihenfolge'))
                ->assertSee(__('Speichern'))
                ->assertSee(__('Löschen'));
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::store
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processOutput
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processPublishedAt
     */
    public function testStore()
    {
        $response = $this->actingAs($this->user)->post(
            route('web.user.notifications.store'),
            [
                'content' => Str::random(100),
                'level' => rand(0, 3),
                'expired_at' => Carbon::now()->addDay(),
                'output' => [
                    'index',
                ],
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['store']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::store
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processOutput
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processPublishedAt
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::dispatchJob
     *
     * @covers \App\Jobs\Web\SendNotificationEmail
     *
     * @covers \App\Mail\NotificationEmail
     */
    public function testStoreWithEmailJob()
    {
        Mail::fake();

        $response = $this->actingAs($this->user)->post(
            route('web.user.notifications.store'),
            [
                'content' => Str::random(100),
                'level' => 3,
                'expired_at' => Carbon::now()->addDay(),
                'output' => [
                    'index',
                    'email',
                ],
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['store']);

        if ($response->status() !== 403) {
            Mail::assertQueued(NotificationEmail::class);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::update
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processOutput
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processPublishedAt
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\Notification $notification */
        $notification = $this->notifications[1];

        $response = $this->actingAs($this->user)->patch(
            route('web.user.notifications.update', $notification->getRouteKey()),
            [
                'content' => Str::random(100),
                'level' => rand(0, 3),
                'expired_at' => Carbon::now()->addDay(),
                'published_at' => $notification->published_at,
                'order' => 0,
                'output' => [
                    'index',
                    'status',
                ],
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::update
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processOutput
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::processPublishedAt
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::dispatchJob
     *
     * @covers \App\Jobs\Web\SendNotificationEmail
     *
     * @covers \App\Mail\NotificationEmail
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdateResendEmail()
    {
        /** @var \App\Models\Api\Notification $notification */
        $notification = factory(Notification::class)->create();

        Mail::fake();

        $response = $this->actingAs($this->user)->patch(
            route('web.user.notifications.update', $notification->getRouteKey()),
            [
                'content' => Str::random(100),
                'level' => rand(0, 3),
                'expired_at' => Carbon::now()->addDay(),
                'published_at' => $notification->published_at,
                'order' => 0,
                'resend_email' => true,
                'output' => [
                    'index',
                    'status',
                    'email',
                ],
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['update']);

        if ($response->status() !== 403) {
            Mail::assertQueued(NotificationEmail::class);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::update
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController::destroy
     */
    public function testDestroy()
    {
        $notification = $this->notifications[2];

        $response = $this->actingAs($this->user)->patch(
            route('web.user.notifications.update', $notification->getRouteKey()),
            [
                'delete' => true,
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['destroy']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Notification\NotificationController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(NotificationController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(NotificationController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }

    /**
     * {@inheritdoc}
     * Creates needed Notifications
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->notifications = factory(Notification::class, 5)->states('active')->create();
    }
}
