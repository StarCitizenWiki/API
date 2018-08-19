<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\Notification;

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
class NotificationControllerSysopTest extends NotificationControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_OK,

        'create' => \Illuminate\Http\Response::HTTP_OK,

        'edit' => \Illuminate\Http\Response::HTTP_OK,

        'store' => \Illuminate\Http\Response::HTTP_FOUND,

        'update' => \Illuminate\Http\Response::HTTP_FOUND,

        'destroy' => \Illuminate\Http\Response::HTTP_FOUND,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin->groups()->sync(AdminGroup::where('name', 'sysop')->first()->id);
    }
}
