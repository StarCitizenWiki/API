<?php declare(strict_types = 1);

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Models\Account\Admin\Admin;
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
        $admins = Admin::query()->whereHas('adminGroup')->whereNotNull('email')->get();

        Notification::send($admins, new CommLinksChangedNotification($event->commLinks));
    }
}
