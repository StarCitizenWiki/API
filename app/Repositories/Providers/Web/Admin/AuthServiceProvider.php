<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 10:57
 */

namespace App\Repositories\Providers\Web\Admin;

use App\Repositories\Contracts\Web\Admin\AuthRepositoryInterface;
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
        $adminAuthImplementation = 'App\Repositories\Web\Admin\AuthRepository';
        if (config('app.env') === 'local' && config('auth.providers.admins.use_stub_when_local') === true) {
            $adminAuthImplementation = 'App\Repositories\Web\Admin\AuthRepositoryStub';
        }

        $this->app->bind(AuthRepositoryInterface::class, $adminAuthImplementation);
    }
}
