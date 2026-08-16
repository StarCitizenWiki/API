<?php

declare(strict_types=1);

use App\Http\Middleware\TrustCloudflareProxies;
use Illuminate\Http\Request;

describe('TrustCloudflareProxies', function (): void {
    beforeEach(function (): void {
        $this->middleware = new TrustCloudflareProxies;
    });

    afterEach(function (): void {
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);
    });

    it('resolves the client ip from x-forwarded-for when the request comes from a cloudflare edge ip', function (): void {
        $request = Request::create('/api/stats', 'GET', server: [
            'REMOTE_ADDR' => '104.16.1.1',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.5',
        ]);

        $this->middleware->handle($request, fn () => response('ok'));

        expect($request->ip())->toBe('203.0.113.5');
    });

    it('ignores a forged x-forwarded-for header from a non-cloudflare ip', function (): void {
        $request = Request::create('/api/stats', 'GET', server: [
            'REMOTE_ADDR' => '198.51.100.7',
            'HTTP_X_FORWARDED_FOR' => '1.2.3.4',
        ]);

        $this->middleware->handle($request, fn () => response('ok'));

        expect($request->ip())->toBe('198.51.100.7');
    });

    it('resolves the scheme from x-forwarded-proto when the request comes from the traefik docker network', function (): void {
        $request = Request::create('/api/stats', 'GET', server: [
            'REMOTE_ADDR' => '172.18.0.5',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $this->middleware->handle($request, fn () => response('ok'));

        expect($request->isSecure())->toBeTrue();
    });
});
