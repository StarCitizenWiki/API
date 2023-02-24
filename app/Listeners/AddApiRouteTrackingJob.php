<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ApiRouteCalled;
use App\Jobs\TrackApiRouteCall;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;

class AddApiRouteTrackingJob
{
    /**
     * Handle the event.
     *
     * @param ApiRouteCalled $event
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
