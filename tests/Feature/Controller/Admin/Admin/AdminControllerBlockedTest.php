<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;

/**
 * Class AdminControllerTest
 *
 * {@inheritdoc}
 */
class AdminControllerBlockedTest extends BaseAdminControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'dashboard' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * Creates the Admin
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin = factory(Admin::class)->state('blocked')->create();
        $this->admin->groups()->sync(AdminGroup::where('name', 'bureaucrat')->first()->id);
    }
}
