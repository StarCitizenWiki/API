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
use App\Contracts\Web\Admin\AuthRepositoryInterface;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth1\User;

/**
 * Stub Implementation for local development
 */
class AuthRepository implements AuthRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function startAuth()
    {
        return Socialite::with('mediawiki')->stateless(false)->redirect();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserFromProvider()
    {
        return Socialite::with('mediawiki')->stateless(false)->user();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrCreateLocalUser(User $user, string $provider): Admin
    {
        $authUser = Admin::where('provider_id', $user->id)->first();

        if ($authUser) {
            $this->syncLocalUserGroups($user, $authUser);

            return $authUser;
        }

        /** @var \App\Models\Account\Admin\Admin $admin */
        $admin = Admin::create(
            [
                'username' => $user->username,
                'blocked' => $user->blocked,
                'provider_id' => $user->getId(),
                'provider' => $provider,
            ]
        );

        $this->syncLocalUserGroups($user, $admin);

        return $admin;
    }

    /**
     * {@inheritdoc}
     */
    public function syncLocalUserGroups(User $oauthUser, Admin $admin): void
    {
        $groups = $oauthUser->user['groups'] ?? null;

        if (is_array($groups)) {
            $groupIDs = AdminGroup::select('id')->whereIn('name', $groups)->get();

            $admin->groups()->sync($groupIDs);
        }
    }
}
