<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Web\Admin\License;

use App\Contracts\Web\Admin\AuthRepositoryInterface;
use App\Http\Controllers\Web\Admin\License\LicenseController;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Feature\Controller\Web\Admin\AdminTestCase;

/**
 * Class AbstractBaseAdminControllerTest
 */
class LicenseControllerTestCase extends AdminTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\Admin\License\LicenseController::show
     */
    public function testShow()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.license.show'));

        $response->assertStatus(static::RESPONSE_STATUSES['show']);

        if ($this->admin->isEditor()) {
            $response->assertViewIs('admin.license.show');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\License\LicenseController::accept
     */
    public function testAccept()
    {
        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->post(
            route('web.admin.license.accept')
        );

        $response->assertStatus(static::RESPONSE_STATUSES['accept']);

        if ($this->admin->isEditor()) {
            $response->assertViewIs('admin.dashboard');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\License\LicenseController::show
     */
    public function testShowAccepted()
    {
        $this->admin->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        $response = $this->actingAs($this->admin, 'admin')->followingRedirects()->get(
            route('web.admin.license.show')
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show_accepted']);

        if ($this->admin->isEditor()) {
            $response->assertViewIs('admin.dashboard');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController::authenticated
     * @covers \App\Repositories\Web\Admin\AuthRepository::getOrCreateLocalUser
     * @covers \App\Repositories\Web\Admin\AuthRepository::getUserFromProvider
     */
    public function testShowAfterLogin()
    {
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($this->admin);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.admin.auth.login.callback'));

        if ($response->status() === Response::HTTP_OK) {
            if ($this->admin->isEditor()) {
                $response->assertViewIs('admin.license.show');
            } else {
                $response->assertViewIs('admin.dashboard');
            }
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController
     * @covers \App\Http\Controllers\Web\Admin\Auth\LoginController::authenticated
     * @covers \App\Repositories\Web\Admin\AuthRepository::getOrCreateLocalUser
     * @covers \App\Repositories\Web\Admin\AuthRepository::getUserFromProvider
     */
    public function testShowAfterLoginAccepted()
    {
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        $this->admin->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($this->admin);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.admin.auth.login.callback'));

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.dashboard')->assertSee($this->admin->username);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\License\LicenseController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(LicenseController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth:admin');

        $reflectedClass = new \ReflectionClass(LicenseController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }
}
