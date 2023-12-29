<?php

declare(strict_types=1);

namespace App\Policies\Web\Job;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\AbstractBaseUserPolicy as BaseUserPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Policy to Start Jobs from the Dashboard
 */
class JobPolicy extends BaseUserPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function uploadCsv(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function truncate(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::BUREAUCRAT;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startCommLinkTranslationJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startCommLinkWikiPageCreationJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startCommLinkImageDownloadJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startCommLinkDownloadJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startCommLinkProofReadStatusUpdateJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SICHTER;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startShipMatrixDownloadImportJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startVehicleMsrpImportJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function startImportGalactapediaJob(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }
}
