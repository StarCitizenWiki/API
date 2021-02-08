<?php

declare(strict_types=1);

namespace App\Policies\Web\User\Rsi\Stat;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatPolicy
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
}
