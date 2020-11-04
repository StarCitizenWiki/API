<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink\Category;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;

/**
 * Class Category Controller Test
 *
 * @covers \App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy<extended>
 *
 * @covers \App\Http\Middleware\CheckUserState
 *
 * @covers \App\Providers\RouteServiceProvider
 *
 * @covers \App\Models\Rsi\CommLink\Category\Category
 */
class CategoryControllerUserTest extends CategoryControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_OK,

        'show' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
