<?php

declare(strict_types=1);

use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

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

it('redirects legacy routes for get and post while preserving query parameters', function (string $legacyPath, string $targetPath): void {
    assertLegacyAnyRedirect($this, $legacyPath, $targetPath);
})->with([
    'dashboard to root' => ['/dashboard', '/'],
    'comm links index route' => ['/rsi/comm-links', '/comm-links'],
    'comm link image route' => ['/rsi/comm-links/images/cover-art', '/comm-links/images/cover-art'],
    'comm link images index route' => ['/rsi/comm-links/images', '/comm-links/images'],
    'comm link similar image route' => ['/rsi/comm-links/images/cover-art/similar', '/comm-links/images/cover-art/similar'],
    'comm link id route' => ['/rsi/comm-links/98765', '/comm-links/98765'],
    'comm link search route' => ['/rsi/comm-links/search', '/comm-links/search'],
    'galactapedia index route' => ['/starcitizen/galactapedia', '/galactapedia'],
    'galactapedia article route' => ['/starcitizen/galactapedia/RwZBLzvn4N', '/galactapedia/RwZBLzvn4N'],
    'starmap systems index route' => ['/starcitizen/starmap/starsystems', '/starmap/systems'],
    'starmap celestial objects index route' => ['/starcitizen/starmap/celestial_objects', '/starmap/celestial-objects'],
    'ground vehicles to matrix' => ['/starcitizen/vehicles/ground-vehicles', '/ship-matrix/vehicles'],
    'rsi stats route' => ['/rsi/stats', '/stats'],
    'ships to matrix' => ['/starcitizen/vehicles/ships', '/ship-matrix/vehicles'],
]);

it('redirects legacy starsystem detail routes to the new code based route while preserving query parameters', function (): void {
    $starsystem = Starsystem::factory()->create([
        'cig_id' => 'legacy-system-id',
        'code' => 'TESTSYS',
    ]);

    $legacyUrl = url()->query('/starcitizen/starmap/starsystems/'.$starsystem->cig_id, [
        'legacy' => 'phase-3',
        'source' => 'tests',
    ]);

    $targetUrl = url()->query('/starmap/systems/'.$starsystem->code, [
        'legacy' => 'phase-3',
        'source' => 'tests',
    ]);

    $this->get($legacyUrl)
        ->assertMovedPermanently()
        ->assertLocation($targetUrl);
});

it('redirects legacy celestial object detail routes to the new code based route while preserving query parameters', function (): void {
    $celestialObject = CelestialObject::factory()->create([
        'cig_id' => 'legacy-object-id',
        'code' => 'TESTOBJ',
    ]);

    $legacyUrl = url()->query('/starcitizen/starmap/celestial_objects/'.$celestialObject->cig_id, [
        'legacy' => 'phase-3',
        'source' => 'tests',
    ]);

    $targetUrl = url()->query('/starmap/celestial-objects/'.$celestialObject->code, [
        'legacy' => 'phase-3',
        'source' => 'tests',
    ]);

    $this->get($legacyUrl)
        ->assertMovedPermanently()
        ->assertLocation($targetUrl);
});
