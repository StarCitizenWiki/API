<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Account;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;

/**
 * @covers \App\Http\Controllers\Web\Account\AccountController
 *
 * @covers \App\Policies\Web\Account\AccountPolicy<extended>
 */
class AccountControllerMitarbeiterTest extends AccountControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_OK,

        'update' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the User model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->groups()->sync(UserGroup::where('name', 'mitarbeiter')->first()->id);
    }
}
