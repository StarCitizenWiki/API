<?php

namespace App\Listeners;

use App\Mail\URLShortened as URLShortenedMail;
use App\Events\URLShortened;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendURLShortenedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  URLShortened  $event
     * @return void
     */
    public function handle(URLShortened $event)
    {
        $url = $event->url;
        Mail::to('api@star-citizen.wiki')->send(new URLShortenedMail($url));
    }
}
