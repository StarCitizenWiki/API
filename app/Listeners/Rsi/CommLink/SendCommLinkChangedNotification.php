<?php declare(strict_types = 1);

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\CommLinkChanged;
use App\Models\Account\Admin\Admin;
use App\Notifications\Rsi\CommLink\CommLinkChanged as CommLinkChangedNotification;
use Illuminate\Support\Facades\Notification;

class SendCommLinkChangedNotification
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Rsi\CommLink\CommLinkChanged $event
     *
     * @return void
     */
    public function handle(CommLinkChanged $event)
    {
        $admins = Admin::query()->whereHas('adminGroup')->whereNotNull('email')->get();

        Notification::send($admins, new CommLinkChangedNotification($event->commLinks));
    }
}
