<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Admin\Notification;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;

/**
 * Class NotificationControllerTest
 *
 * @covers \App\Policies\Web\Admin\Notification\NotificationPolicy<extended>
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 *
 * @covers \App\Providers\RouteServiceProvider
 *
 * @covers \App\Models\Api\Notification
 */
class NotificationControllerBlockedTest extends NotificationControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'create' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'edit' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'store' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'update' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'destroy' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin = factory(Admin::class)->state('blocked')->create();
        $this->admin->groups()->sync(AdminGroup::where('name', 'sysop')->first()->id);
    }
}