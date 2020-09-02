<?php declare(strict_types = 1);

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\CommLinksChanged as CommLinksChangedEvent;
use App\Models\Account\User\User;
use App\Notifications\Rsi\CommLink\CommLinksChanged as CommLinksChangedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Send Notification to Admins (Sysop, Bureaucrat)
 */
class SendCommLinksChangedNotification
{
    /**
     * Handle the event.
     *
     * @param CommLinksChangedEvent $event
     *
     * @return void
     */
    public function handle(CommLinksChangedEvent $event): void
    {
        /** @var Collection $admins */
        $admins = User::query()->whereNotNull('email')->whereHas('adminGroup')->get();

        Notification::send($admins, new CommLinksChangedNotification($event->commLinks));
    }
}
