<?php

declare(strict_types=1);

namespace App\Policies\Web;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class TranslationPolicy
 */
class TranslationPolicy extends BaseAdminPolicy
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
     * Create a new resource
     *
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
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
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }
}
