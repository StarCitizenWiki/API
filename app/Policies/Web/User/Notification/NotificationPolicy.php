<?php declare(strict_types = 1);

namespace App\Policies\Web\User\Notification;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\User\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class NotificationPolicy
 */
class NotificationPolicy extends BaseAdminPolicy
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
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Create a new resource
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Update a Resource
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Delete a resource
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }
}
