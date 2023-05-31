<?php

declare(strict_types=1);

namespace App\Policies\Web\User\Transcript;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\User\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class TranscriptPolicy extends BaseAdminPolicy
{
    use HandlesAuthorization;

    /**
     * View all / single resource.
     *
     * @param User $user
     *
     * @return bool
     */
    public function index(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::USER;
    }

    /**
     * View all / single resource.
     *
     * @param User $user
     *
     * @return bool
     */
    public function view(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::USER;
    }

    /**
     * Update a Resource.
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }
}
