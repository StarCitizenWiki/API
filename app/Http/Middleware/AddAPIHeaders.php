<?php
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 28.02.2017
 * Time: 23:05
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class AfterApiRequest
 *
 * @package App\Http\Middleware
 */
class AddAPIHeaders
{
    /**
     * Sets Header for API Requests
     *
     * @param Request $request Current Request
     * @param Closure $next    Next Function
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        //$response->header("Host", $request->getHost());
        $response->header("Content-Type", "application/json");
        $response->header("Cache-Control", "no-cache,no-store, must-revalidate");
        $response->header("Pragma", "no-cache");
        if (is_array($response->content())) {
            $contentLength = strlen(
                json_encode($response->content(), JSON_PRETTY_PRINT)
            );
        } else {
            $contentLength = strlen($response->content());
        }
        $response->header("Content-Length", $contentLength);
        $response->header("Vary", "Accept-Encoding");
        $response->header("Connection", "keep-alive");
        $response->header("X-SCW-API-Version", API_VERSION);

        Log::debug('Added API Headers', [
            'method' => __METHOD__,
            'request_url' => $request->fullUrl(),
        ]);

        return $response;
    }
}
