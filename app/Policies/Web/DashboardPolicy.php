<?php

declare(strict_types=1);

namespace App\Policies\Web;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class DashboardPolicy
 */
class DashboardPolicy extends BaseAdminPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }
}
