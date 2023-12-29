<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Rsi\CommLink\Channel;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;

/**
 * Class Category Controller Test
 *
 * @covers \App\Policies\Web\Rsi\CommLink\CommLinkPolicy<extended>
 *
 * @covers \App\Http\Middleware\CheckUserState
 *
 * @covers \App\Providers\RouteServiceProvider
 *
 * @covers \App\Models\Rsi\CommLink\Channel\Channel
 */
class ChannelControllerBlockedTest extends ChannelControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'index' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'show' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
    ];

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->blocked()->create();
        $this->user->groups()->sync(UserGroup::where('name', 'sysop')->first()->id);
    }
}
