<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:23
 */

namespace Tests\Feature\Controller\Web\User\License;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Http\Controllers\Web\User\License\LicenseController;
use Illuminate\Http\Response;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Feature\Controller\Web\User\UserTestCase;

/**
 * Class AbstractBaseAdminControllerTest
 */
class LicenseControllerTestCase extends UserTestCase
{
    /**
     * @covers \App\Http\Controllers\Web\User\License\LicenseController::show
     */
    public function testShow()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.license.show'));

        $response->assertStatus(static::RESPONSE_STATUSES['show']);

        if ($this->user->isEditor()) {
            $response->assertViewIs('user.license.show');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\License\LicenseController::accept
     */
    public function testAccept()
    {
        $response = $this->actingAs($this->user)->followingRedirects()->post(
            route('web.user.license.accept')
        );

        $response->assertStatus(static::RESPONSE_STATUSES['accept']);

        if ($this->user->isEditor()) {
            $response->assertViewIs('user.dashboard');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\License\LicenseController::show
     */
    public function testShowAccepted()
    {
        $this->user->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        $response = $this->actingAs($this->user)->followingRedirects()->get(
            route('web.user.license.show')
        );

        $response->assertStatus(static::RESPONSE_STATUSES['show_accepted']);

        if ($this->user->isEditor()) {
            $response->assertViewIs('user.dashboard');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Auth\LoginController
     * @covers \App\Http\Controllers\Web\User\Auth\LoginController::authenticated
     * @covers \App\Repositories\Web\User\AuthRepository::getOrCreateLocalUser
     * @covers \App\Repositories\Web\User\AuthRepository::getUserFromProvider
     */
    public function testShowAfterLogin()
    {
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($this->user);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.user.auth.login.callback'));

        if ($response->status() === Response::HTTP_OK) {
            if ($this->user->isEditor()) {
                $response->assertViewIs('user.license.show');
            } else {
                $response->assertViewIs('user.dashboard');
            }
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Auth\LoginController
     * @covers \App\Http\Controllers\Web\User\Auth\LoginController::authenticated
     * @covers \App\Repositories\Web\User\AuthRepository::getOrCreateLocalUser
     * @covers \App\Repositories\Web\User\AuthRepository::getUserFromProvider
     */
    public function testShowAfterLoginAccepted()
    {
        $provider = 'mediawiki';

        $authRepository = Mockery::mock(AuthRepositoryInterface::class);
        $oauthUser = Mockery::mock('\SocialiteProviders\Manager\OAuth1\User');

        $this->user->settings()->updateOrCreate(
            [
                'editor_license_accepted' => true,
            ]
        );

        $authRepository->shouldReceive('getUserFromProvider')->andReturn($oauthUser);
        $authRepository->shouldReceive('getOrCreateLocalUser')->with($oauthUser, $provider)->andReturn($this->user);
        $this->app->instance(AuthRepositoryInterface::class, $authRepository);

        Socialite::shouldReceive('with->stateless->user')->andReturn($oauthUser);

        $response = $this->followingRedirects()->get(route('web.user.auth.login.callback'));

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.dashboard')->assertSee($this->user->username);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\License\LicenseController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(LicenseController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(LicenseController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }
}
