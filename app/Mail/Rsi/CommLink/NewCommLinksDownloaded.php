<?php declare(strict_types = 1);

namespace App\Mail\Rsi\CommLink;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Changelog Mail Generator
 */
class NewCommLinksDownloaded extends Mailable
{
    use Queueable;
    use SerializesModels;

    private $commLinks;

    /**
     * Create a new message instance.
     *
     * @param \Illuminate\Database\Eloquent\Collection $commLinks
     */
    public function __construct(Collection $commLinks)
    {
        $this->commLinks = $commLinks;
        $this->subject = 'Neue Comm Links vorhanden';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'emails.rsi.comm_link.new_comm_link_downloaded',
            [
                'commLinks' => $this->commLinks,
            ]
        );
    }
}
