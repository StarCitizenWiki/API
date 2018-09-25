<?php declare(strict_types = 1);

namespace App\Policies\Web\User\Rsi\CommLink;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\User\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ManufacturerPolicy
 */
class CommLinkPolicy extends BaseAdminPolicy
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

    /**
     * Update a Resource
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->isEditor() || $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Update Comm Link Settings
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function updateSettings(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Preview Comm Link Version
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function preview(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }
}
