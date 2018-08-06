<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\Notification;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Carbon\Carbon;

/**
 * Class NotificationControllerTest
 */
class NotificationControllerMitarbeiterTest extends AbstractBaseNotificationControllerTest
{
    /**
     * @var \App\Models\Account\Admin\Admin
     */
    private $admin;

    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('web.admin.notifications.index'));

        $response->assertOk()
            ->assertSee(__('Benachrichtigungen'));
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::create
     */
    public function testCreate()
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('web.admin.notifications.create'));

        $response->assertStatus(403);
    }


    /**
     * @covers \App\Http\Controllers\Web\Admin\Notification\NotificationController::edit
     */
    public function testEdit()
    {
        $notification = $this->notifications[0];

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('web.admin.notifications.edit', $notification));

        $response->assertStatus(403);
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

        $response->assertStatus(403);
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

        $response->assertStatus(403);
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

        $response->assertStatus(403);
    }

    /**
     * TODO Tests for Other Permission Levels
     */
    protected function setUp()
    {
        parent::setUp();
        $group = factory(AdminGroup::class)->states('mitarbeiter')->create();

        $this->admin = factory(Admin::class)->create();
        $this->admin->groups()->sync([$group->id]);
    }
}
