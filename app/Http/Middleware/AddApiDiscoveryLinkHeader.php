<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddApiDiscoveryLinkHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set(
            'Link',
            '</.well-known/api-catalog>; rel="api-catalog", </api/openapi>; rel="service-desc"; type="application/vnd.oai.openapi"',
        );

        return $response;
    }
}
