<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Admin\Admin;

use App\Models\Account\Admin\AdminGroup;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\Admin\Admin\AdminPolicy<extended>
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 */
class AdminControllerBureaucratTest extends AdminControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_OK,

        'edit' => \Illuminate\Http\Response::HTTP_OK,

        'update' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin->groups()->sync(AdminGroup::where('name', 'bureaucrat')->first()->id);
    }
}
