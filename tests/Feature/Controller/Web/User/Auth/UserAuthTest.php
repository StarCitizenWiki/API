<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\Auth;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Mockery;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class AdminAuthStubTest
 *
 * @covers \App\Http\Controllers\Web\User\Auth\LoginController
 */
class UserAuthTest extends UserTestCase
{
    /**
     * Login Form
     */
    public function testLoginForm()
    {
        $response = $this->get(route('web.user.auth.login'));
        $response->assertViewIs('user.auth.login');
    }

    /**
     * @covers \App\Repositories\Web\User\AuthRepository
     */
    public function testLoginSocialite()
    {
        $this->markTestSkipped('TBD');
        return;
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        /** @var \App\Models\Account\User\User $localUser */
        $localUser = factory(User::class)->create(
            [
                'provider' => $provider,
            ]
        );
        $localUser->groups()->sync(UserGroup::where('name', 'sysop')->first()->id);

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($localUser);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        //Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.user.auth.login.callback'));

        if ($localUser->isAdmin()) {
            $response->assertViewIs('user.dashboard');
        } else {
            $response->assertViewIs('user.account.index')->assertSee($localUser->username);
        }
    }
}
