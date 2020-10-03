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
class CommLinksChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    private Collection $commLinksWithoutContent;
    private Collection $commLinksWithContent;

    /**
     * Create a new message instance.
     *
     * @param Collection $commLinks
     */
    public function __construct(Collection $commLinks)
    {
        $this->commLinksWithoutContent = $commLinks->where('had_content', '=', false);
        $this->commLinksWithContent = $commLinks->where('had_content', '=', true);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown(
            'emails.rsi.comm_link.comm_link_changed',
            [
                'withoutContent' => $this->commLinksWithoutContent,
                'withContent' => $this->commLinksWithContent,
            ]
        );
    }
}
