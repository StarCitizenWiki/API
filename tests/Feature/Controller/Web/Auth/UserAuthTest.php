<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Auth;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Mockery;
use Tests\Feature\Controller\Web\UserTestCase;

/**
 * Class AdminAuthStubTest
 *
 * @covers \App\Http\Controllers\Web\Auth\LoginController
 */
class UserAuthTest extends UserTestCase
{
    /**
     * Login Form
     */
    public function testLoginForm()
    {
        $response = $this->get(route('web.auth.login'));
        $response->assertViewIs('web.auth.login');
    }

    /**
     * @covers \App\Repositories\Web\AuthRepository
     */
    public function testLoginSocialite()
    {
        self::markTestSkipped('TBD');

        return;
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        /** @var \App\Models\Account\User\User $localUser */
        $localUser = User::factory()->create(
            [
                'provider' => $provider,
            ]
        );
        $localUser->groups()->sync(UserGroup::where('name', 'sysop')->first()->id);

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($localUser);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        //Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.auth.login.callback'));

        if ($localUser->isAdmin()) {
            $response->assertViewIs('web.dashboard');
        } else {
            $response->assertViewIs('web.account.index')->assertSee($localUser->username);
        }
    }
}
