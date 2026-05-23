<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Starsystem;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    GameVersion::factory()->create([
        'code' => '3.25.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

describe('index', function (): void {
    it('filters celestial objects by starsystem, name, designation, and type', function (): void {
        $system = Starsystem::factory()->create([
            'cig_id' => 1001,
            'name' => 'Sol',
        ]);

        $target = CelestialObject::factory()->create([
            'starsystem_id' => $system->cig_id,
            'name' => 'Terra',
            'designation' => 'III',
            'type' => 'PLANET',
        ]);

        CelestialObject::factory()->create([
            'starsystem_id' => $system->cig_id,
            'name' => 'Terra Nova',
            'designation' => 'IV',
            'type' => 'MOON',
        ]);

        $response = $this->getJson(route('celestial-objects.index', [
            'include' => 'starsystem',
            'filter' => [
                'starsystem' => 'Sol',
                'name' => 'Terra',
                'designation' => 'III',
                'type' => 'PLANET',
            ],
        ]));

        $response->assertSuccessful();

        $ids = collect($response->json('data'))->pluck('id')->all();

        expect($ids)->toBe([$target->cig_id]);
    });

    it('sorts celestial objects by sensor population', function (): void {
        $low = CelestialObject::factory()->create(['sensor_population' => 1.0]);
        $mid = CelestialObject::factory()->create(['sensor_population' => 5.0]);
        $high = CelestialObject::factory()->create(['sensor_population' => 10.0]);

        $response = $this->getJson(route('celestial-objects.index', [
            'sort' => 'sensor_population',
        ]));

        $response->assertSuccessful();

        $ids = collect($response->json('data'))->pluck('id')->all();

        expect($ids)->toBe([$low->cig_id, $mid->cig_id, $high->cig_id]);
    });
});

describe('show', function (): void {
    it('returns a celestial object when the code case differs', function (): void {
        $celestialObject = CelestialObject::factory()->create([
            'code' => 'MIN.MOONS.MIN1d',
            'name' => 'Min 1d',
        ]);

        $response = $this->getJson(route('celestial-objects.show', [
            'code' => 'MIN.MOONS.MIN1D',
        ]));

        $response->assertSuccessful();

        expect($response->json('data.id'))->toBe($celestialObject->cig_id);
    });
});

describe('search', function (): void {
    it('searches celestial objects by text query without bigint cast errors', function (): void {
        $starsystem = Starsystem::factory()->create(['cig_id' => 12001]);

        $matchingObject = CelestialObject::factory()->create([
            'cig_id' => 42001,
            'starsystem_id' => $starsystem->cig_id,
            'name' => 'FLIGHT MODEL TEST',
            'code' => 'FLTMDL',
        ]);

        CelestialObject::factory()->create([
            'cig_id' => 42002,
            'starsystem_id' => $starsystem->cig_id,
            'name' => 'ASTEROID BELT',
            'code' => 'ASTRO',
        ]);

        $response = $this->postJson(route('celestial-objects.search'), [
            'query' => 'flight model',
        ]);

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data')
            ->assertHeader('Deprecated', 'true');

        expect($response->json('meta.deprecated'))->toBeTrue()
            ->and(collect($response->json('data'))->pluck('id')->all())->toBe([$matchingObject->cig_id]);
    });

    it('searches celestial objects by numeric cig id', function (): void {
        $starsystem = Starsystem::factory()->create(['cig_id' => 12011]);

        $targetObject = CelestialObject::factory()->create([
            'cig_id' => 42011,
            'starsystem_id' => $starsystem->cig_id,
            'name' => 'TARGET OBJECT',
            'code' => 'TGT01',
        ]);

        CelestialObject::factory()->create([
            'cig_id' => 42012,
            'starsystem_id' => $starsystem->cig_id,
            'name' => 'OTHER OBJECT',
            'code' => 'OTH01',
        ]);

        $response = $this->postJson(route('celestial-objects.search'), [
            'query' => (string) $targetObject->cig_id,
        ]);

        $response->assertSuccessful()
            ->assertJsonCount(1, 'data');

        expect(collect($response->json('data'))->pluck('id')->all())
            ->toBe([$targetObject->cig_id]);
    });
});
