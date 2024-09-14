<?php

declare(strict_types=1);

namespace App\Policies\Web\User;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class AdminPolicy
 */
class UserPolicy extends BaseAdminPolicy
{
    use HandlesAuthorization;

    /**
     * Admin Index
     *
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Admin Update
     *
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Admin delete
     *
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }
}
