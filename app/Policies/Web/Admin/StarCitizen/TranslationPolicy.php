<?php declare(strict_types = 1);

namespace App\Policies\Web\Admin\StarCitizen;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use App\Policies\Web\Admin\AbstractBaseAdminPolicy as BaseAdminPolicy;
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
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function view(Admin $admin)
    {
        return $admin->getHighestPermissionLevel() >= AdminGroup::USER;
    }

    /**
     * Create a new resource
     *
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function create(Admin $admin)
    {
        return $admin->getHighestPermissionLevel() >= AdminGroup::MITARBEITER;
    }

    /**
     * Update a Resource
     *
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function update(Admin $admin)
    {
        return $admin->getHighestPermissionLevel() >= AdminGroup::MITARBEITER;
    }
}
