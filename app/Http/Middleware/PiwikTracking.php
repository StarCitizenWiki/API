<?php

namespace App\Http\Middleware;

use App\Traits\ProfilesMethodsTrait;
use Closure;
use Composer\DependencyResolver\Request;
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
    use ProfilesMethodsTrait;

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
        $this->startProfiling(__FUNCTION__);

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
            $piwikClient->setUserId($request->get(AUTH_KEY_FIELD_NAME, false));
            $piwikClient->doTrackPageView($request->getRequestUri());

            $this->addTrace(__FUNCTION__, "Passed URL: {$request->fullUrl()} to Piwik", __LINE__);
        }

        $this->stopProfiling(__FUNCTION__);

        return $next($request);
    }
}
