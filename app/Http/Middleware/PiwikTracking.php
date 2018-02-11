<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use PiwikTracker;

/**
 * Class PiwikTracking
 * Passes the RequestData to Piwik
 */
class PiwikTracking
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request Request
     * @param \Closure                 $next    Next
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function handle($request, Closure $next)
    {
        /**
         * Local nicht tracken
         */
        if (App::environment('production')) {
            /**
             * Piwik Tracker Class
             *
             * @var PiwikTracker $piwikClient
             */
            $piwikClient = new PiwikTracker(config('api.piwik.site_id'));
            $piwikClient::$URL = config('api.piwik.url');

            $piwikClient->setUrl($request->fullUrl());
            $piwikClient->setGenerationTime(microtime(true) - LARAVEL_START);

            $user = $request->user();

            $key = false;
            if (!is_null($user)) {
                $key = $user->id;
            }

            $piwikClient->setUserId($key);
            $piwikClient->doTrackPageView($request->getRequestUri());
        }

        return $next($request);
    }
}
