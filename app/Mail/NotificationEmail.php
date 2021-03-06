<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\System\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class NotificationCreated
 */
class NotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public Notification $notification;

    /**
     * Create a new message instance.
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        $this->subject('Star Citizen Wiki API - Notification');

        return $this->markdown('emails.notification');
    }
}
