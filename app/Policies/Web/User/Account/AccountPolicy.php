<?php

declare(strict_types=1);

namespace App\Policies\Web\User\Account;

use App\Models\Account\User\User;
use App\Policies\Web\User\AbstractBaseUserPolicy as BaseAdminPolicy;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

/**
 * Class AccountPolicy
 */
class AccountPolicy extends BaseAdminPolicy
{
    use HandlesAuthorization;

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function view(User $user)
    {
        return $user->id === Auth::id() || Auth::user()->isAdmin();
    }

    /**
     * @param \App\Models\Account\User\User $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->id === Auth::id() || Auth::user()->isAdmin();
    }
}
