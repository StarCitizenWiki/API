<?php declare(strict_types = 1);

namespace App\Policies\Web\User\Rsi\CommLink\Image;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Policies\Web\User\AbstractBaseUserPolicy as BasePolicy;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImagePolicy extends BasePolicy
{
    use HandlesAuthorization;

    public function view(User $user)
    {
        return $user->getHighestPermissionLevel() >= UserGroup::USER;
    }
}
