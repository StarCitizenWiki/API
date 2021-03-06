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
    public function handle(NewCommLinksDownloaded $event)
    {
        if ($event->commLinks->count() > 0) {
            $admins = User::query()
                ->whereNotNull('email')
                ->whereHas(
                    'settings',
                    static function (Builder $query) {
                        $query->where('receive_comm_link_notifications', true);
                    }
                )
                ->orWhereHas('adminGroup')
                ->get();

            Notification::send($admins, new NewCommLinksDownloadedNotification($event->commLinks));
        }
    }
}
