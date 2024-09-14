<?php

declare(strict_types=1);

namespace App\Policies\Web\Rsi\CommLink;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ManufacturerPolicy
 */
class CommLinkPolicy extends BaseAdminPolicy
{
    use HandlesAuthorization;

    /**
     * View all / single resource
     */
    public function view(): bool
    {
        return true;
    }

    /**
     * Update a Resource
     *
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * Preview Comm-Link Version
     *
     *
     * @return bool
     */
    public function preview(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }
}
