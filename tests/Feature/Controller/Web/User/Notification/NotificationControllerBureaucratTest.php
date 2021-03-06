<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\Notification;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;

/**
 * Class NotificationControllerTest
 *
 * @covers \App\Policies\Web\User\Notification\NotificationPolicy<extended>
 *
 * @covers \App\Http\Middleware\CheckUserState
 *
 * @covers \App\Providers\RouteServiceProvider
 *
 * @covers \App\Models\System\Notification
 */
class NotificationControllerBureaucratTest extends NotificationControllerTestCase
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
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'bureaucrat')->first()->id);
    }
}
