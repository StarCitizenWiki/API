<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 10:51
 */

namespace App\Contracts\Web\User;

use App\Models\Account\User\User as UserModel;
use SocialiteProviders\Manager\OAuth1\User;

/**
 * Interface AuthRepositoryInterface
 */
interface AuthRepositoryInterface
{
    /**
     * Starts the Auth process and redirects to the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function startAuth();

    /**
     * Returns the User from the OAuth Provider
     *
     * @return \SocialiteProviders\Manager\OAuth1\User
     */
    public function getUserFromProvider();

    /**
     * Returns the associated local user. Creates a new Record if no user has been found for the given provider
     *
     * @param \SocialiteProviders\Manager\OAuth1\User $oauthUser
     * @param string                                  $provider
     *
     * @return \App\Models\Account\User\User
     */
    public function getOrCreateLocalUser(User $oauthUser, string $provider);

    /**
     * Syncs the given wiki groups to the local record
     *
     * @param \SocialiteProviders\Manager\OAuth1\User $oauthUser
     * @param \App\Models\Account\User\User           $user
     *
     * @return void
     */
    public function syncLocalUserGroups(User $oauthUser, UserModel $user);
}
