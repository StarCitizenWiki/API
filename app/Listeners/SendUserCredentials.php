<?php

namespace App\Listeners;

use App\Mail\UserRegistered as UserRegisteredMail;
use App\Events\UserRegistered;
use Illuminate\Queue\InteractsWithQueue;
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
        Mail::to($user->email)->send(new UserRegisteredMail($user));
    }
}
