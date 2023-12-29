<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Dashboard;

use App\Models\Account\User\UserGroup;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\DashboardPolicy<extended>
 *
 * @covers \App\Http\Middleware\CheckUserState
 */
class DashboardControllerSysopTest extends DashboardControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'show' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user->groups()->sync(UserGroup::where('name', 'sysop')->first()->id);
    }
}
