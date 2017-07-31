<?php declare(strict_types = 1);

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

/**
 * Class LogSuccessfulLogin
 *
 * @package App\Listeners
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
    public function handle(Login $event)
    {
        $event->user->last_login = date('Y-m-d H:i:s');
        $event->user->save();
    }
}
