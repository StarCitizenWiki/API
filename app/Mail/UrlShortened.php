<?php declare(strict_types = 1);

namespace App\Mail;

use App\Models\ShortUrl\ShortUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UrlShortened
 */
class UrlShortened extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $url;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\ShortUrl\ShortUrl $url URL Object
     */
    public function __construct(ShortUrl $url)
    {
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject('Star Citizen Wiki API - URL Shortened');

        return $this->markdown('emails.urlshortened');
    }
}
