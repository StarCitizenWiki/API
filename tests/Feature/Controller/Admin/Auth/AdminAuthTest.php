<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.08.2018
 * Time: 12:01
 */

namespace Tests\Feature\Controller\Admin\Auth;

use App\Contracts\Web\Admin\AuthRepositoryInterface;
use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Feature\Controller\Admin\AdminTestCase;

/**
 * Class AdminAuthStubTest
 *
 * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController
 */
class AdminAuthTest extends AdminTestCase
{
    /**
     * Login Form
     */
    public function testLoginForm()
    {
        $response = $this->get(route('web.admin.auth.login'));
        $response->assertViewIs('admin.auth.login');
    }

    /**
     * @covers \App\Repositories\Web\Admin\AuthRepository
     */
    public function testLoginSocialite()
    {
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        /** @var \App\Models\Account\Admin\Admin $localUser */
        $localUser = factory(Admin::class)->create(
            [
                'provider' => $provider,
            ]
        );
        $localUser->groups()->sync(AdminGroup::where('name', 'sysop')->first()->id);

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($localUser);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.admin.auth.login.callback'));

        $response->assertViewIs('admin.dashboard')->assertSee($localUser->username);
    }
}
