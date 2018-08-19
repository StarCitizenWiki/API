<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 19.08.2018
 * Time: 12:01
 */

namespace Tests\Feature\Controller\Admin\Auth;

use App\Models\Account\Admin\Admin;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Feature\Controller\Admin\AdminTestCase;

/**
 * Class AdminAuthStubTest
 *
 * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController
 */
class AdminAuthStubTest extends AdminTestCase
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
     * @covers \App\Repositories\Web\Admin\AuthRepositoryStub
     */
    public function testLoginStub()
    {
        $response = $this->followingRedirects()->get(route('web.admin.auth.login.start'));
        $response->assertViewIs('admin.dashboard')->assertSee('Local Wiki Admin');
    }

    /**
     * @covers \App\Repositories\Web\Admin\AuthRepository
     */
    public function testLoginSocialite()
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')->andReturn('Redirected');
        $providerName = 'mediawiki';

        $socialAccount = factory(Admin::class)->create(['provider' => $providerName]);

        $abstractUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        $abstractUser->shouldReceive('getId')->andReturn($socialAccount->provider_id);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('mediawiki')->andReturn($provider);

        $response = $this->followingRedirects()->get(route('web.admin.auth.login.callback'));

        $response->assertViewIs('admin.dashboard')->assertSee('Local Wiki Admin');
    }
}
