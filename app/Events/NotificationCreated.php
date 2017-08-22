<?php declare(strict_types = 1);

namespace App\Events;

use App\Models\Notification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class NotificationCreated
 * @package App\Events
 */
class NotificationCreated implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $notification;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }
}
