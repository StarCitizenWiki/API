<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\Admin\AdminPolicy<extended>
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 */
class AdminControllerBureaucratTest extends BaseAdminControllerTestCase
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
        $this->admin->groups()->sync(AdminGroup::where('name', 'bureaucrat')->first()->id);
    }
}
