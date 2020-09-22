<?php

declare(strict_types=1);

namespace App\Mail\Rsi\CommLink;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Changelog Mail Generator
 */
class NewCommLinksDownloaded extends Mailable
{
    use Queueable;
    use SerializesModels;

    private Collection $commLinks;

    /**
     * Create a new message instance.
     *
     * @param Collection $commLinks
     */
    public function __construct(Collection $commLinks)
    {
        $this->commLinks = $commLinks;
        $this->subject = 'Neue Comm-Links vorhanden';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->markdown(
            'emails.rsi.comm_link.new_comm_link_downloaded',
            [
                'commLinks' => $this->commLinks,
            ]
        );
    }
}
