<?php

declare(strict_types=1);

namespace App\Policies\Web\User\StarCitizen\Manufacturer;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\User\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class ManufacturerPolicy
 */
class ManufacturerPolicy extends BaseAdminPolicy
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
    public function update(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::MITARBEITER;
    }
}
