<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 29.08.2017
 * Time: 12:59
 */

namespace App\Repositories\StarCitizenWiki\Interfaces;

/**
 * Interface AuthRepositoryInterface
 */
interface AuthRepositoryInterface
{
    /**
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function authenticateUsingCredentials($username, $password): bool;
}
