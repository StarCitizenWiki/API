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
