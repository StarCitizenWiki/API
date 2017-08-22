<?php declare(strict_types = 1);

namespace App\Listeners;

use App\Events\NotificationCreated;
use App\Mail\NotificationCreated as NotificationMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendNotification
 * @package App\Listeners
 */
class SendNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  NotificationCreated $event
     *
     * @return void
     */
    public function handle(NotificationCreated $event)
    {
        /** @var \App\Models\Notification $notification */
        $notification = $event->notification;

        if ($notification->email) {
            $users = User::all()->where(
                'receive_notification_level',
                '<=',
                $notification->level
            );

            Mail::to($users)->send(new NotificationMail($notification));
        }
    }
}
