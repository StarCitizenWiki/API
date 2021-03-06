<?php

declare(strict_types=1);

namespace App\Jobs\Web;

use App\Mail\NotificationEmail;
use App\Models\Account\User\User;
use App\Models\System\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendNotificationEmail
 */
class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $notification;

    /**
     * Create a new job instance.
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $users = User::query()->whereHas(
            'settings',
            function (Builder $query) {
                $query->where('receive_api_notifications', true);
            }
        )->get();

        Mail::to($users)->queue(new NotificationEmail($this->notification));
    }
}
