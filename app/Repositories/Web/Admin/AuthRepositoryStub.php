<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 11:00
 */

namespace App\Repositories\Web\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use App\Repositories\Contracts\Web\Admin\AuthRepositoryInterface;
use SocialiteProviders\Manager\OAuth1\User;

/**
 * Mediawiki Auth Repository
 */
class AuthRepositoryStub implements AuthRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function startAuth()
    {
        return redirect()->route('web.admin.auth.login.callback');
    }

    /**
     * {@inheritdoc}
     */
    public function getUserFromProvider()
    {
        return new User();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrCreateLocalUser(User $user, string $provider): Admin
    {
        $admin = Admin::where('username', 'Local Wiki Admin')->first();
        if (null !== $admin) {
            return $admin;
        }

        /** @var \App\Models\Account\Admin\Admin $admin */
        $admin = factory(Admin::class)->create(
            [
                'username' => 'Local Wiki Admin',
            ]
        );
        $admin->groups()->sync([AdminGroup::where('name', 'bureaucrat')->first()->id]);

        return $admin;
    }

    /**
     * {@inheritdoc}
     */
    public function syncLocalUserGroups(User $oauthUser, Admin $admin): void
    {
        return;
    }
}
