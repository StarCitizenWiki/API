<?php

declare(strict_types=1);

namespace App\Policies\Web\Changelog;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\AbstractBaseUserPolicy as BaseUserPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ChangelogPolicy
 */
class ChangelogPolicy extends BaseUserPolicy
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
}
