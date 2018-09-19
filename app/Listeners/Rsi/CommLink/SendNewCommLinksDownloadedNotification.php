<?php declare(strict_types = 1);

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use App\Models\Account\Admin\Admin;
use App\Notifications\Rsi\CommLink\NewCommLinksDownloaded as NewCommLinksDownloadedNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Send Notification top Admins (Sysop, Bureaucrat) and Editors
 */
class SendNewCommLinksDownloadedNotification
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Rsi\CommLink\NewCommLinksDownloaded $event
     *
     * @return void
     */
    public function handle(NewCommLinksDownloaded $event)
    {
        $admins = Admin::query()->whereHas('editorGroup')->orWhereHas('adminGroup')->whereNotNull('email')->get();

        Notification::send($admins, new NewCommLinksDownloadedNotification($event->commLinks));
    }
}
