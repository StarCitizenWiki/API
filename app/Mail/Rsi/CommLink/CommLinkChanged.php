<?php declare(strict_types = 1);

namespace App\Mail\Rsi\CommLink;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Changelog Mail Generator
 */
class CommLinkChanged extends Mailable
{
    use Queueable;
    use SerializesModels;

    private $commLinks;

    private $commLinksWithoutContent;
    private $commLinksWithContent;

    /**
     * Create a new message instance.
     *
     * @param \Illuminate\Database\Eloquent\Collection $commLinks
     */
    public function __construct(Collection $commLinks)
    {
        $this->commLinks = $commLinks;
        $this->commLinksWithoutContent = $this->commLinks->where('had_content', '=', false);
        $this->commLinksWithContent = $this->commLinks->where('had_content', '=', true);
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
