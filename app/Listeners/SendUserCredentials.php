<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\UserRegistered as UserRegisteredMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendUserCredentials
 * Sends out User Credentials
 *
 * @package App\Listeners
 */
class SendUserCredentials implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param UserRegistered $event Event
     *
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        $user = $event->user;
        $password = $event->password;
        Mail::to([
            $user->email,
            'info@star-citizen.wiki',
        ])->send(new UserRegisteredMail($user, $password));
    }
}
