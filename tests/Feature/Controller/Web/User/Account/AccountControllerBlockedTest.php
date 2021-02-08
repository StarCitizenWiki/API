<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Account;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;

/**
 * @covers \App\Http\Controllers\Web\User\Account\AccountController
 *
 * @covers \App\Policies\Web\User\Account\AccountPolicy<extended>
 */
class AccountControllerBlockedTest extends AccountControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'update' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the User model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->blocked()->create();
        $this->user->groups()->sync(UserGroup::where('name', 'sysop')->first()->id);
    }
}
