<?php declare(strict_types = 1);

namespace App\Jobs;

use App\Mail\NotificationEmail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;


/**
 * Class SendNotificationEmail
 * @package App\Jobs
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
     * @param \App\Models\Notification $notification
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
    public function handle()
    {
        if ($this->notification->output_email) {
            $users = User::all()->where(
                'receive_notification_level',
                '<=',
                $this->notification->level
            );

            Mail::to($users)->send(new NotificationEmail($this->notification));
        }
    }
}
