<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 11:00
 */

namespace App\Repositories\Web\User;

use App\Contracts\Web\User\AuthRepositoryInterface;
use App\Models\Account\User\User as UserModel;
use App\Models\Account\User\UserGroup;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Manager\OAuth1\User;

/**
 * Mediawiki Bridge
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
    public function getOrCreateLocalUser(User $oauthUser, string $provider): UserModel
    {
        /** @var \App\Models\Account\User\User $authUser */
        $authUser = UserModel::query()->where('provider_id', $oauthUser->id)->where('provider', $provider)->first();
        Session::put('oauth.user_token', $oauthUser->token);
        Session::put('oauth.user_secret', $oauthUser->tokenSecret);

        if ($authUser) {
            $this->syncLocalUserGroups($oauthUser, $authUser);

            if ($authUser->email !== $oauthUser->getEmail()) {
                $authUser->update(
                    [
                        'email' => $oauthUser->getEmail(),
                    ]
                );
            }

            return $authUser;
        }

        return $this->createLocalUser($oauthUser, $provider);
    }

    /**
     * {@inheritdoc}
     */
    public function syncLocalUserGroups(User $oauthUser, UserModel $user): void
    {
        $groups = $oauthUser->user['groups'] ?? null;

        if (is_array($groups)) {
            $groupIDs = UserGroup::select('id')->whereIn('name', $groups)->get();

            $user->groups()->sync($groupIDs);
        }
    }

    /**
     * Creates the local User Record
     *
     * @param \SocialiteProviders\Manager\OAuth1\User $oauthUser
     * @param string                                  $provider
     *
     * @return \App\Models\Account\User\User
     */
    private function createLocalUser(User $oauthUser, string $provider)
    {
        /** @var \App\Models\Account\User\User $localUser */
        $localUser = UserModel::create(
            [
                'username' => $oauthUser->username,
                'email' => $oauthUser->getEmail(),
                'blocked' => $oauthUser->blocked,
                'provider_id' => $oauthUser->getId(),
                'provider' => $provider,
                'api_token' => str_random(60),
            ]
        );

        $this->syncLocalUserGroups($oauthUser, $localUser);

        return $localUser;
    }
}
