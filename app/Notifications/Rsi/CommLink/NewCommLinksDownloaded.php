<?php declare(strict_types = 1);

namespace App\Notifications\Rsi\CommLink;

use App\Mail\Rsi\CommLink\NewCommLinksDownloaded as NewCommLinksDownloadedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;

/**
 * Notification for sending newly downloaded Comm-Links to Admins and those who want to be notified
 */
class NewCommLinksDownloaded extends Notification
{
    use Queueable;

    private $commLinks;

    /**
     * Create a new notification instance.
     *
     * @param \Illuminate\Database\Eloquent\Collection $commLinks
     */
    public function __construct(Collection $commLinks)
    {
        $this->commLinks = $commLinks;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
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
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Mail\Mailable
     */
    public function toMail($notifiable)
    {
        return (new NewCommLinksDownloadedMail($this->commLinks))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed $notifiable
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
