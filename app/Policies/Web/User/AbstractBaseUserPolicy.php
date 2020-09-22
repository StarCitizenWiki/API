<?php

declare(strict_types=1);

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
     * @param User  $user
     * @param mixed $ability
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
