<?php declare(strict_types = 1);
/**
 * Created by IntelliJ IDEA.
 * User: Sebastian
 * Date: 28.02.2017
 * Time: 23:05
 */

namespace App\Http\Middleware;

use Closure;

/**
 * Class AfterApiRequest
 *
 * @package App\Http\Middleware
 */
class AddApiHeaders
{
    /**
     * Sets Header for API Requests
     *
     * @param \Illuminate\Http\Request $request Current Request
     * @param \Closure                 $next    Next Function
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Content-Type', 'application/json; charset=utf-8');
        $response->header('Cache-Control', 'no-cache,no-store, must-revalidate');
        $response->header('Pragma', 'no-cache');
        if (is_array($response->content())) {
            $contentLength = strlen(
                json_encode($response->content(), JSON_PRETTY_PRINT)
            );
        } else {
            $contentLength = strlen($response->content());
        }
        $response->header('Content-Length', $contentLength);
        $response->header('Vary', 'Accept-Encoding');
        $response->header('Connection', 'keep-alive');
        $response->header('X-SCW-API-Version', config('api.version'));

        return $response;
    }
}
