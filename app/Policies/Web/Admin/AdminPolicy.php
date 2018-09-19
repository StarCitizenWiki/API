<?php declare(strict_types = 1);

namespace App\Policies\Web\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Account\Admin\AdminGroup;
use App\Policies\Web\Admin\AbstractBaseAdminPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class AdminPolicy
 */
class AdminPolicy extends BaseAdminPolicy
{
    use HandlesAuthorization;

    /**
     * Admin Index
     *
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function view(Admin $admin)
    {
        return $admin->getHighestPermissionLevel() >= AdminGroup::SYSOP;
    }

    /**
     * Admin Detail
     *
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function show(Admin $admin)
    {
        return $admin->getHighestPermissionLevel() >= AdminGroup::SYSOP;
    }


    /**
     * Admin Update (Block)
     *
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function update(Admin $admin)
    {
        return $admin->getHighestPermissionLevel() >= AdminGroup::SYSOP;
    }

    /**
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function viewDashboard(Admin $admin)
    {
        return $admin->getHighestPermissionLevel() >= AdminGroup::USER;
    }

    /**
     * @param \App\Models\Account\Admin\Admin $admin
     *
     * @return bool
     */
    public function acceptLicense(Admin $admin)
    {
        return $admin->isEditor();
    }
}
