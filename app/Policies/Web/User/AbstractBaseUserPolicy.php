<?php
declare(strict_types=1);
/**
 * User: Hannes
 * Date: 06.08.2018
 * Time: 19:45
 */

namespace App\Policies\Web\User;

use App\Models\Account\User\User;

/**
 * Class AbstractBaseUserPolicy
 */
abstract class AbstractBaseUserPolicy
{
    /**
     * Don't allow Access if Account is blocked
     *
     * @param \App\Models\Account\User\User $user
     * @param mixed                         $ability
     *
     * @return bool
     */
    public function before(User $user, $ability)
    {
        if ($user->blocked) {
            return false;
        }
    }
}
