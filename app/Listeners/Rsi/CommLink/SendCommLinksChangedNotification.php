<?php declare(strict_types = 1);

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Models\Account\User\User;
use App\Notifications\Rsi\CommLink\CommLinksChanged as CommLinksChangedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Send Notification to Admins (Sysop, Bureaucrat)
 */
class SendCommLinksChangedNotification
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Rsi\CommLink\CommLinksChanged $event
     *
     * @return void
     */
    public function handle(CommLinksChangedEvent $event)
    {
        /** @var \Illuminate\Database\Eloquent\Collection $admins */
        $admins = User::query()->whereNotNull('email')->whereHas('adminGroup')->get();

        Notification::send($admins, new CommLinksChangedNotification($event->commLinks));
    }
}
