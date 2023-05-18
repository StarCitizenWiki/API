<?php

declare(strict_types=1);

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Models\Account\User\User;
use App\Notifications\Rsi\CommLink\NewCommLinksDownloaded as NewCommLinksDownloadedNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

/**
 * Send Notification to Admins (Sysop, Bureaucrat) or Users with Receive Comm-Link Notification Flag
 */
class SendNewCommLinksDownloadedNotification
{
    /**
     * Handle the event.
     *
     * @param NewCommLinksDownloaded $event
     *
     * @return void
     */
    public function handle(NewCommLinksDownloaded $event): void
    {
        if ($event->commLinks->count() > 0) {
            $users = User::query()
                ->whereNotNull('email')
                ->whereNot('email', '')
                ->where(function (Builder $query) {
                    $query->whereRelation('settings', 'receive_comm_link_notifications', true)
                        ->orWhereHas('adminGroup');
                })
                ->get();

            app('Log')::info(sprintf('Sending Comm-Link Notification to %d users', $users->count()));

            Notification::send($users, new NewCommLinksDownloadedNotification($event->commLinks));
        }
    }
}
