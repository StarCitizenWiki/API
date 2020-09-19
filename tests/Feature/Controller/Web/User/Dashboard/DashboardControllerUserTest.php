<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Dashboard;

use App\Models\Account\User\UserGroup;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\User\DashboardPolicy<extended>
 *
 * @covers \App\Http\Middleware\CheckUserState
 */
class DashboardControllerUserTest extends DashboardControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'show' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
