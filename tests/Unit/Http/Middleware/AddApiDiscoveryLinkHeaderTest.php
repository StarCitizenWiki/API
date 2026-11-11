<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\AddApiDiscoveryLinkHeader;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

describe('AddApiDiscoveryLinkHeader middleware', function (): void {
    it('adds api-catalog and service-desc Link headers', function (): void {
        $middleware = new AddApiDiscoveryLinkHeader;
        $response = $middleware->handle(new Request, fn (): Response => new Response);

        expect($response->headers->get('Link'))
            ->toBe('</.well-known/api-catalog>; rel="api-catalog", </api/openapi>; rel="service-desc"; type="application/vnd.oai.openapi"');
    });
});
