<?php

declare(strict_types=1);

namespace App\Notifications\Rsi\CommLink;

use App\Mail\Rsi\CommLink\CommLinksChanged as CommLinksChangedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/**
 * Notification for sending Changelog to Admins
 */
class CommLinksChanged extends Notification
{
    use Queueable;

    private Collection $commLinks;

    /**
     * Create a new notification instance.
     *
     * @param Collection $commLinks
     */
    public function __construct(Collection $commLinks)
    {
        $this->commLinks = $commLinks;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return Mailable
     */
    public function toMail($notifiable)
    {
        return (new CommLinksChangedMail($this->commLinks))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     *
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
