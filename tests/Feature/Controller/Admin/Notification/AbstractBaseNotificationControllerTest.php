<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 07.08.2018
 * Time: 11:52
 */

namespace Tests\Feature\Controller\Admin\Notification;


use App\Models\Account\Admin\AdminGroup;
use App\Models\Api\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class AbstractBaseNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $notifications;

    protected function setUp()
    {
        parent::setUp();
        if (AdminGroup::count() !== 5) {
            $this->createAdminGroups();
        }
        $this->notifications = factory(Notification::class, 5)->states('active')->create();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->notifications = null;
        unset($this->notifications);
    }
}
