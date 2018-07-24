<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 03.03.2017
 * Time: 18:16
 */

namespace App\Repositories\StarCitizenWiki\Auth;

use App\Repositories\StarCitizenWiki\Interfaces\AuthRepositoryInterface;

/**
 * Class ShipsRepository
 * Stub that allows Login by Username instead of querying the wiki itself
 */
class AuthRepositoryStub implements AuthRepositoryInterface
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function authenticateUsingCredentials($username, $password): bool
    {
        return true;
    }
}
