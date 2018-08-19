<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 10:57
 */

namespace App\Providers\Web\Admin;

use App\Contracts\Web\Admin\AuthRepositoryInterface;
use App\Repositories\Web\Admin\AuthRepository;
use App\Repositories\Web\Admin\AuthRepositoryStub;
use Illuminate\Support\ServiceProvider;

/**
 * Admin Auth Service Provider
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Binds the stub implementation to the interface if app is local
     */
    public function register()
    {
        $adminAuthImplementation = AuthRepository::class;
        if (config('auth.providers.admins.use_stub') === true) {
            $adminAuthImplementation = AuthRepositoryStub::class;
        }

        $this->app->bind(AuthRepositoryInterface::class, $adminAuthImplementation);
    }
}
