<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 11:00
 */

namespace App\Repositories\Web\User;

use App\Models\Account\User\User as UserModel;
use App\Models\Account\User\UserGroup;
use App\Contracts\Web\User\AuthRepositoryInterface;
use SocialiteProviders\Manager\OAuth1\User;

/**
 * Stub Implementation
 */
class AuthRepositoryStub implements AuthRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function startAuth()
    {
        return redirect()->route('web.user.auth.login.callback');
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
    public function getOrCreateLocalUser(User $oauthUser, string $provider): UserModel
    {
        $user = UserModel::where('username', 'Local Wiki Admin')->first();
        if (null !== $user) {
            return $user;
        }

        /** @var \App\Models\Account\User\User $user */
        $user = factory(UserModel::class)->create(
            [
                'username' => 'Local Wiki Admin',
                'email' => 'admin@example.com',
            ]
        );
        $user->groups()->sync([UserGroup::where('name', 'bureaucrat')->first()->id]);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function syncLocalUserGroups(User $oauthUser, UserModel $user): void
    {
        return;
    }
}
