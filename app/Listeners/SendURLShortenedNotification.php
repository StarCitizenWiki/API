<?php declare(strict_types = 1);

namespace App\Listeners;

use App\Events\URLShortened;
use App\Mail\URLShortened as URLShortenedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendURLShortenedNotification
 * Sends an Admin Email containing the shortened URL
 *
 * @package App\Listeners
 */
class SendURLShortenedNotification implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param \App\Events\URLShortened $event Event
     *
     * @return void
     */
    public function handle(URLShortened $event)
    {
        $url = $event->url;
        Mail::to('info@star-citizen.wiki')->send(new URLShortenedMail($url));
    }
}
