<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

describe('routes', function (): void {
    it('returns no content for the current starsystem placeholder route', function (): void {
        $starsystem = Starsystem::factory()->create(['code' => 'TESTSYS']);

        $this->get(route('web.starmap.systems.show', ['code' => $starsystem->code]))
            ->assertNoContent(Response::HTTP_NO_CONTENT);
    });

    it('returns no content for the current celestial object placeholder route', function (): void {
        $celestial = CelestialObject::factory()->create(['code' => 'TESTOBJ']);

        $this->get(route('web.starmap.celestial-objects.show', ['code' => $celestial->code]))
            ->assertNoContent(Response::HTTP_NO_CONTENT);
    });
});

describe('url generation', function (): void {
    it('starsystem resource generates correct web_url', function (): void {
        $starsystem = Starsystem::factory()->create([
            'code' => 'TESTSYS',
            'cig_id' => 12345,
        ]);

        $response = $this->getJson(route('starsystems.show', ['code' => $starsystem->code]));

        $webUrl = $response->json('data')['web_url'];

        expect($webUrl)->toBe(url('/starmap/systems/TESTSYS'));
    });

    it('celestial object resource generates correct web_url', function (): void {
        $starsystem = Starsystem::factory()->create([
            'code' => 'TESTSYS',
        ]);

        $celestial = CelestialObject::factory()->create([
            'code' => 'TESTOBJ',
            'cig_id' => 67890,
            'starsystem_id' => $starsystem->id,
        ]);

        $response = $this->getJson(route('celestial-objects.show', ['code' => $celestial->code]));

        $webUrl = $response->json('data')['web_url'];

        expect($webUrl)->toBe(route('web.starmap.celestial-objects.show', ['code' => $celestial->code]));
    });
});
