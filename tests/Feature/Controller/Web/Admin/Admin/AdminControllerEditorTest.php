<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Admin\Admin;

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
class AdminControllerEditorTest extends AdminControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'dashboard' => \Illuminate\Http\Response::HTTP_OK,

        'index' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'edit' => \Illuminate\Http\Response::HTTP_FORBIDDEN,

        'update' => \Illuminate\Http\Response::HTTP_FORBIDDEN,
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
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        /** @var \App\Models\Account\Admin\Admin $localUser */
        $localUser = factory(Admin::class)->create(
            [
                'provider' => $provider,
            ]
        );
        $localUser->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($localUser);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

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
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        /** @var \App\Models\Account\Admin\Admin $localUser */
        $localUser = factory(Admin::class)->create(
            [
                'provider' => $provider,
            ]
        );
        $localUser->groups()->sync(AdminGroup::where('name', 'editor')->first()->id);
        $localUser->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($localUser);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.admin.auth.login.callback'));

        $response->assertViewIs('admin.dashboard')->assertSee($localUser->username);
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
