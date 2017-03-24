<?php

namespace App\Listeners;

use App\Mail\URLShortened as URLShortenedMail;
use App\Events\URLShortened;
use Illuminate\Queue\InteractsWithQueue;
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
     * @param URLShortened $event Event
     *
     * @return void
     */
    public function handle(URLShortened $event)
    {
        $url = $event->url;
        Mail::to('api@star-citizen.wiki')->send(new URLShortenedMail($url));
    }
}
