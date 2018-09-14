<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin\Admin;

use App\Contracts\Web\Admin\AuthRepositoryInterface;
use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

/**
 * Class AdminControllerTest
 *
 * @covers \App\Policies\Web\Admin\AdminPolicy<extended>
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 */
class AdminControllerUserTest extends AdminControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'dashboard' => \Illuminate\Http\Response::HTTP_OK,
    ];

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::acceptLicenseView
     */
    public function testAcceptLicenseView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.accept_license_view'));

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('admin.accept_license');
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::acceptLicense
     */
    public function testAcceptLicense()
    {
        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->post(
            route('web.admin.accept_license')
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\AdminController::acceptLicenseView
     */
    public function testAcceptLicenseViewAccepted()
    {
        $this->admin->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->get(
            route('web.admin.accept_license')
        );

        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController::authenticated
     * @covers \App\Repositories\Web\Admin\AuthRepository::getOrCreateLocalUser
     * @covers \App\Repositories\Web\Admin\AuthRepository::getUserFromProvider
     */
    public function testAcceptLicenseViewAfterLogin()
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')->andReturn('Redirected');
        $providerName = 'mediawiki';

        /** @var \App\Models\Account\Admin\Admin $socialAccount */
        $socialAccount = factory(Admin::class)->create(['provider' => $providerName]);
        $socialAccount->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);

        $abstractUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        $abstractUser->shouldReceive('getId')->andReturn($socialAccount->provider_id);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('mediawiki')->andReturn($provider);

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $authRepository->shouldReceive('getUserFromProvider')->andReturn($abstractUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($abstractUser, $providerName)->andReturn(
            $socialAccount
        );
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        $response = $this->followingRedirects()->get(route('web.admin.auth.login.callback'));

        $response->assertViewIs('admin.accept_license');
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController::authenticated
     * @covers \App\Repositories\Web\Admin\AuthRepository::getOrCreateLocalUser
     * @covers \App\Repositories\Web\Admin\AuthRepository::getUserFromProvider
     */
    public function testAcceptLicenseViewAfterLoginAccepted()
    {
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('redirect')->andReturn('Redirected');
        $providerName = 'mediawiki';

        /** @var \App\Models\Account\Admin\Admin $socialAccount */
        $socialAccount = factory(Admin::class)->create(['provider' => $providerName]);
        $socialAccount->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);
        $socialAccount->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        $abstractUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        $abstractUser->shouldReceive('getId')->andReturn($socialAccount->provider_id);

        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('mediawiki')->andReturn($provider);

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $authRepository->shouldReceive('getUserFromProvider')->andReturn($abstractUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($abstractUser, $providerName)->andReturn(
            $socialAccount
        );
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        $response = $this->followingRedirects()->get(route('web.admin.auth.login.callback'));

        $response->assertViewIs('admin.dashboard');
    }

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp()
    {
        parent::setUp();
        $this->admin->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);
    }
}
