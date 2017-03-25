<?php

namespace App\Mail;

use App\Models\ShortURL\ShortURL;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Class URLShortened
 *
 * @package App\Mail
 */
class URLShortened extends Mailable
{
    use Queueable, SerializesModels;

    public $url;

    /**
     * Create a new message instance.
     *
     * @param ShortURL $url URL Object
     */
    public function __construct(ShortURL $url)
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

        return $this->markdown('mail.urlshortened');
    }
}
