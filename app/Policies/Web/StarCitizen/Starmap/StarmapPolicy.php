<?php

declare(strict_types=1);

namespace App\Policies\Web\StarCitizen\Starmap;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class VehiclePolicy
 */
class StarmapPolicy extends BaseAdminPolicy
{
    use HandlesAuthorization;

    /**
     * View all / single resource
     *
     * @return bool
     */
    public function view(): bool
    {
        return true;
    }

    /**
     * Update a Resource
     *
     * @param User $user
     *
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }
}
