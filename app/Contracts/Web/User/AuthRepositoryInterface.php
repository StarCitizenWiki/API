<?php

declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 09.08.2018
 * Time: 10:51.
 */

namespace App\Contracts\Web\User;

use App\Models\Account\User\User;
use Illuminate\Http\Request;

/**
 * Interface AuthRepositoryInterface.
 */
interface AuthRepositoryInterface
{
    /**
     * Starts the Auth process and redirects to the provider.
     *
     * @return \Illuminate\Http\Response | Illuminate\View\View
     */
    public function startAuth();

    /**
     * Returns the User from the OAuth Provider.
     *
     * @param Request $request
     *
     * @return \App\Models\Account\User\User
     */
    public function getUserFromProvider(Request $request): User;

    /**
     * Returns the associated local user. Creates a new Record if no user has been found for the given provider.
     *
     * @param \App\Models\Account\User\User $oauthUser
     * @param string                        $provider
     *
     * @return \App\Models\Account\User\User
     */
    public function getOrCreateLocalUser(User $oauthUser, string $provider): User;

    /**
     * Syncs the given wiki groups to the local record.
     *
     * @param \App\Models\Account\User\User $oauthUser
     * @param \App\Models\Account\User\User $user
     */
    public function syncLocalUserGroups(User $oauthUser, User $user): void;
}
