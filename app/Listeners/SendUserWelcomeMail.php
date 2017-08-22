<?php declare(strict_types = 1);

namespace App\Listeners;

use App\Mail\UserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendUserCredentials
 * Sends out User Credentials
 *
 * @package App\Listeners
 */
class SendUserWelcomeMail implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Registered $event Event
     *
     * @return void
     */
    public function handle(Registered $event)
    {
        $user = $event->user;
        Mail::to(
            [
                $user->email,
                'info@star-citizen.wiki',
            ]
        )->send(new UserRegistered($user));
    }
}
