<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\Notification;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;

/**
 * Class NotificationControllerTest
 *
 * {@inheritdoc}
 */
class NotificationControllerBureaucratTest extends BaseNotificationControllerTestCase
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
     */
    protected function setUp()
    {
        parent::setUp();

        $this->admin = factory(Admin::class)->create();
        $this->admin->groups()->sync(AdminGroup::where('name', 'bureaucrat')->first()->id);
    }
}
