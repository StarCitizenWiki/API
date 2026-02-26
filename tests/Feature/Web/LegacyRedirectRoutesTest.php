<?php

declare(strict_types=1);

use Tests\TestCase;

function assertLegacyAnyRedirect(TestCase $testCase, string $legacyPath, string $targetPath): void
{
    $query = [
        'legacy' => 'phase-3',
        'source' => 'tests',
    ];

    $legacyUrl = url()->query($legacyPath, $query);
    $targetUrl = url()->query($targetPath, $query);

    $testCase->get($legacyUrl)
        ->assertMovedPermanently()
        ->assertLocation($targetUrl);

    $testCase->post($legacyUrl)
        ->assertMovedPermanently()
        ->assertLocation($targetUrl);
}

it('redirects /dashboard to / for GET and POST while preserving query parameters', function (): void {
    assertLegacyAnyRedirect($this, '/dashboard', '/');
});

it('redirects /rsi/comm-links/images/{image} to /comm-links/images/{image} for GET and POST while preserving query parameters', function (): void {
    assertLegacyAnyRedirect($this, '/rsi/comm-links/images/cover-art', '/comm-links/images/cover-art');
});

it('redirects /rsi/comm-links/images/{image}/similar to /comm-links/images/{image}/similar for GET and POST while preserving query parameters', function (): void {
    assertLegacyAnyRedirect(
        $this,
        '/rsi/comm-links/images/cover-art/similar',
        '/comm-links/images/cover-art/similar',
    );
});

it('redirects /rsi/comm-links/{id} to /comm-links/{id} for GET and POST while preserving query parameters', function (): void {
    assertLegacyAnyRedirect($this, '/rsi/comm-links/98765', '/comm-links/98765');
});

it('redirects /starcitizen/vehicles/ground-vehicles to /ship-matrix/vehicles for GET and POST while preserving query parameters', function (): void {
    assertLegacyAnyRedirect($this, '/starcitizen/vehicles/ground-vehicles', '/ship-matrix/vehicles');
});

it('redirects /starcitizen/vehicles/ships to /ship-matrix/vehicles for GET and POST while preserving query parameters', function (): void {
    assertLegacyAnyRedirect($this, '/starcitizen/vehicles/ships', '/ship-matrix/vehicles');
});
