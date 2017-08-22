<?php declare(strict_types = 1);

namespace App\Listeners;

use App\Events\UrlShortened;
use App\Mail\UrlShortened as UrlShortenedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendUrlShortenedNotification
 * Sends an Admin Email containing the shortened URL
 *
 * @package App\Listeners
 */
class SendUrlShortenedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param \App\Events\UrlShortened $event Event
     *
     * @return void
     */
    public function handle(UrlShortened $event)
    {
        $url = $event->url;
        Mail::to('info@star-citizen.wiki')->send(new UrlShortenedMail($url));
    }
}
