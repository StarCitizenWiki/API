<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Api\Notification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Class NotificationControllerTest
 */
class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\Account\Admin\Admin
     */
    private $admin;

    /**
     * @var \Illuminate\Support\Collection
     */
    private $notifications;

    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('web.admin.notifications.index'));

        $response->assertOk()
            ->assertSee(__('Notifications'));
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::create
     */
    public function testCreate()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('web.admin.notifications.create'));

        $response->assertOk()
            ->assertSee(__('Notification hinzufügen'));
    }


    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::edit
     */
    public function testEdit()
    {
        $notification = $this->notifications[0];

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('web.admin.notifications.edit', $notification));

        $response->assertOk()
            ->assertSee('value="'.$notification->level.'"  selected');
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::store
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::processOutput
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::processPublishedAt
     */
    public function testStore()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(
                route('web.admin.notifications.store'),
                [
                    'content' => str_random(100),
                    'level' => rand(0, 3),
                    'expired_at' => Carbon::now()->addDay(),
                    'output' => [
                        'index',
                    ],
                ]
            );

        $response->assertRedirect(
            route(
                'web.admin.dashboard',
                [
                    'message' => __(
                        'crud.created',
                        [
                            'type' => 'Notification',
                        ]
                    ),
                ]
            )
        );
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::update
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::processOutput
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::processPublishedAt
     */
    public function testUpdate()
    {
        $notification = $this->notifications[1];

        $response = $this->actingAs($this->admin, 'admin')
            ->patch(
                route('web.admin.notifications.update', $notification->id),
                [
                    'content' => str_random(100),
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

        $response->assertRedirect(
            route(
                'web.admin.notifications.index',
                [
                    'message' => __(
                        'crud.updated',
                        [
                            'type' => 'Notification',
                        ]
                    ),
                ]
            )
        );
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::update
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::destroy
     */
    public function testDestroy()
    {
        $notification = $this->notifications[2];

        $response = $this->actingAs($this->admin, 'admin')
            ->patch(
                route('web.admin.notifications.update', $notification),
                [
                    'delete' => true,
                ]
            );

        $response->assertRedirect(
            route(
                'web.admin.notifications.index',
                [
                    'message' => __(
                        'crud.deleted',
                        [
                            'type' => 'Notification',
                        ]
                    ),
                ]
            )
        );
    }

    protected function setUp()
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminGroupTableSeeder']);
        $this->admin = factory(Admin::class)->create();
        $this->admin->groups()->sync([4, 5]);

        $this->notifications = factory(Notification::class, 5)->states('active')->create();
    }
}
