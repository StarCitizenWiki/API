<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;

/**
 * Class AdminControllerTest
 *
 * {@inheritdoc}
 */
class AdminControllerSysopTest extends BaseAdminControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'dashboard' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * Creates the Admin
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin = factory(Admin::class)->create();
        $this->admin->groups()->sync(AdminGroup::where('name', 'sysop')->first()->id);
    }
}
