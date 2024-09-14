<?php

declare(strict_types=1);

namespace App\Policies\Web\Rsi\Stat;

use Illuminate\Auth\Access\HandlesAuthorization;

class StatPolicy
{
    use HandlesAuthorization;

    /**
     * View all / single resource
     */
    public function view(): bool
    {
        return true;
    }
}
