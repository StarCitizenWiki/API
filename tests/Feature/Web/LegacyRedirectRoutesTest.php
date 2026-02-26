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

it('redirects legacy routes for GET and POST while preserving query parameters', function (string $legacyPath, string $targetPath): void {
    assertLegacyAnyRedirect($this, $legacyPath, $targetPath);
})->with([
    'dashboard to root' => ['/dashboard', '/'],
    'comm link image route' => ['/rsi/comm-links/images/cover-art', '/comm-links/images/cover-art'],
    'comm link similar image route' => ['/rsi/comm-links/images/cover-art/similar', '/comm-links/images/cover-art/similar'],
    'comm link id route' => ['/rsi/comm-links/98765', '/comm-links/98765'],
    'ground vehicles to matrix' => ['/starcitizen/vehicles/ground-vehicles', '/ship-matrix/vehicles'],
    'ships to matrix' => ['/starcitizen/vehicles/ships', '/ship-matrix/vehicles'],
]);
