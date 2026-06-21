<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Symfony\Component\HttpFoundation\Response;

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