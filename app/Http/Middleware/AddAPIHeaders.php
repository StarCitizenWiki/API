<?php
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
 * @package App\Http\Middleware
 */
class AddAPIHeaders
{
	/**
	 * Sets Header for API Requests
	 * @param \Illuminate\Http\Request $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function handle($request, Closure $next) {
		$response = $next($request);

		$response->header("Host", "star-citizen.wiki");
		if ($response != null && $response->getStatusCode() === 200)
		{
			$response->header("Content-Type", "application/json");
		}
		$response->header("Cache-Control", "no-cache,no-store, must-revalidate");
		$response->header("Pragma", "no-cache");
		if (is_array($response->getOriginalContent())) {
		    $contentLength = strlen(json_encode($response->getOriginalContent(), JSON_PRETTY_PRINT));
        } else {
		    $contentLength = strlen($response->getOriginalContent());
        }
		$response->header("Content-Length", $contentLength);
		$response->header("Vary", "Accept-Encoding");
		$response->header("Connection", "keep-alive");
		$response->header("X-SCW-API-Version", "1.0");

		return $response;
	}
}