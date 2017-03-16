<?php

namespace App\Listeners;

use App\Events\URLShortened;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendURLShortenedNotification
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
        Mail::to('api@star-citizen.wiki')->send(new \App\Mail\URLShortened($url));
    }
}
