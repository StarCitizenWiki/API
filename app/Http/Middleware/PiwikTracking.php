<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use PiwikTracker;

/**
 * Class PiwikTracking
 * Passes the RequestData to Piwik
 *
 * @package App\Http\Middleware
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
            $piwikClient = new PiwikTracker(PIWIK_SITE_ID);
            $piwikClient::$URL = PIWIK_URL;

            $piwikClient->setUrl($request->fullUrl());
            $piwikClient->setGenerationTime(microtime(true) - LARAVEL_START);

            $key = $request->header('Authorization', null);

            if (is_null($key)) {
                $key = $request->query->get('Authorization', null);
            }

            $piwikClient->setUserId($key);
            $piwikClient->doTrackPageView($request->getRequestUri());
        }

        return $next($request);
    }
}
