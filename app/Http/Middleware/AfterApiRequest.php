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
class AfterApiRequest
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
		$response->header("Content-Type", "application/json");
		$response->header("Cache-Control", "no-cache,no-store, must-revalidate");
		$response->header("Pragma", "no-cache");
		$response->header("Content-Length", strlen($response->getOriginalContent()));
		$response->header("Vary", "Accept-Encoding");
		$response->header("Connection", "keep-alive");
		$response->header("star-citizen-API-Version", "1.0");

		return $response;
	}
}