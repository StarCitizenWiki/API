<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\Dashboard;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\User\DashboardPolicy<extended>
 *
 * @covers \App\Http\Middleware\CheckUserState
 */
class DashboardControllerEditorTest extends DashboardControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'show' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'editor')->first()->id);
    }
}
