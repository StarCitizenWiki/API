<?php

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\NewCommLinksDownloaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewCommLinksNotification
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
     * @param  NewCommLinksDownloaded  $event
     * @return void
     */
    public function handle(NewCommLinksDownloaded $event)
    {
        //
    }
}
