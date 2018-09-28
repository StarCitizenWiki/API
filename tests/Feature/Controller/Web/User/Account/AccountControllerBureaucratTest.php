<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 28.09.2018
 * Time: 12:16
 */

namespace Tests\Feature\Controller\Web\User\Account;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;

/**
 * @covers \App\Http\Controllers\Web\User\Account\AccountController
 *
 * @covers \App\Policies\Web\User\Account\AccountPolicy<extended>
 */
class AccountControllerBureaucratTest extends AccountControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_OK,

        'update' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the User model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->user->groups()->sync(UserGroup::where('name', 'bureaucrat')->first()->id);
    }
}
