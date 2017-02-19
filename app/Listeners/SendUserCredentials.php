<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendUserCredentials
{

    /**
     * Create the event listener.
     *
     * @internal param Mailer $mailer
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  UserRegistered  $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        $user = $event->user;
        Mail::to($user->email)->send(new \App\Mail\UserRegistered($user));
    }
}
