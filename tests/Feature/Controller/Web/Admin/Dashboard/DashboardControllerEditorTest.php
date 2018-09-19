<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Admin\Dashboard;

use App\Contracts\Web\Admin\AuthRepositoryInterface;
use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\Admin\DashboardPolicy<extended>
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 */
class DashboardControllerEditorTest extends DashboardControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'show' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);
    }
}
