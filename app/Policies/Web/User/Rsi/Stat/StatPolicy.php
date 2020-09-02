<?php declare(strict_types = 1);

namespace App\Policies\Web\User\Rsi\Stat;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatPolicy
{
    use HandlesAuthorization;

    /**
     * View all / single resource
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::USER;
    }
}
