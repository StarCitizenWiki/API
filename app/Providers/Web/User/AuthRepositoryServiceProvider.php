<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 10:57
 */

namespace App\Providers\Web\User;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Repositories\Web\User\AuthRepository;
use App\Repositories\Web\User\AuthRepositoryStub;
use Illuminate\Support\ServiceProvider;

/**
 * User Auth Service Provider
 */
class AuthRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Binds the stub implementation to the interface if app is local
     */
    public function register()
    {
        $userAuthRepository = AuthRepository::class;
        if (config('auth.providers.users.use_stub') === true) {
            $userAuthRepository = AuthRepositoryStub::class;
        }

        $this->app->bind(AuthRepositoryInterface::class, $userAuthRepository);
    }
}
