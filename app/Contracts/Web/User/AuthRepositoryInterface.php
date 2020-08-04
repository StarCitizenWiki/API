<?php declare(strict_types=1);

namespace App\Contracts\Web\User;

use App\Models\Account\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Interface AuthRepositoryInterface.
 */
interface AuthRepositoryInterface
{
    /**
     * Starts the Auth process and redirects to the provider.
     *
     * @return Response
     */
    public function startAuth();

    /**
     * Returns the User from the OAuth Provider.
     *
     * @param Request $request
     *
     * @return User
     */
    public function getUserFromProvider(Request $request): User;

    /**
     * Returns the associated local user. Creates a new Record if no user has been found for the given provider.
     *
     * @param User   $oauthUser
     * @param string $provider
     *
     * @return User
     */
    public function getOrCreateLocalUser(User $oauthUser, string $provider): User;

    /**
     * Syncs the given wiki groups to the local record.
     *
     * @param User $oauthUser
     * @param User $user
     */
    public function syncLocalUserGroups(User $oauthUser, User $user): void;
}
