<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

/**
 * Class LogSuccessfulLogin
 */
class LogSuccessfulLogin
{
    /**
     * Handle the event.
     *
     * @param Login $event Event
     *
     * @return void
     */
    public function handle(Login $event): void
    {
        $event->user->last_login = date('Y-m-d H:i:s');
        $event->user->save();
    }
}
