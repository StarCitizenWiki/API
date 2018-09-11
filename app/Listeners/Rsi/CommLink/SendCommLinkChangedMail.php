<?php

namespace App\Listeners\Rsi\CommLink;

use App\Events\Rsi\CommLink\CommLinkChanged;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCommLinkChangedMail
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
     * @param  CommLinkChanged  $event
     * @return void
     */
    public function handle(CommLinkChanged $event)
    {
        //
    }
}
