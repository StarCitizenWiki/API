<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApiRouteCalled;
use App\Jobs\TrackApiRouteCall;

class AddApiRouteTrackingJob
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(ApiRouteCalled $event)
    {
        TrackApiRouteCall::dispatchIf(
            config('services.plausible.enabled') && config('app.env') === 'production',
            $event->request
        );
    }
}
