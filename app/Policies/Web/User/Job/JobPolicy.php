<?php declare(strict_types = 1);

namespace App\Policies\Web\User\Job;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\User\AbstractBaseUserPolicy as BaseUserPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy to Start Jobs from the Dashboard
 */
class JobPolicy extends BaseUserPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function startCommLinkTranslationJob(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function startCommLinkWikiPageCreationJob(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function startCommLinkImageDownloadJob(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function startCommLinkDownloadJob(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function startCommLinkProofReadStatusUpdateJob(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }
}
