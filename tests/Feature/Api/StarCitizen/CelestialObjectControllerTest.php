<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\StarCitizen\Starmap\CelestialObject;
use App\Models\StarCitizen\Starmap\Jumppoint;
use App\Models\StarCitizen\Starmap\Starsystem;

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

describe('jumppoints include', function (): void {
    it('returns jumppoint with entry and exit data when included', function (): void {
        $entrySystem = Starsystem::factory()->create(['code' => 'ELLIS']);
        $exitSystem = Starsystem::factory()->create(['code' => 'NYX']);

        $entryCO = CelestialObject::factory()->create([
            'starsystem_id' => $entrySystem->cig_id,
            'code' => 'ELLIS.JUMPPOINTS.NEXUS',
            'designation' => 'Nexus JP',
            'type' => 'JUMPPOINT',
        ]);

        $exitCO = CelestialObject::factory()->create([
            'starsystem_id' => $exitSystem->cig_id,
            'code' => 'NYX.JUMPPOINTS.BREMEN',
            'designation' => 'Bremen JP',
            'type' => 'JUMPPOINT',
        ]);

        $jumppoint = Jumppoint::factory()->create([
            'entry_id' => $entryCO->cig_id,
            'exit_id' => $exitCO->cig_id,
            'name' => 'Nexus Jump',
            'size' => 'MEDIUM',
        ]);

        $response = $this->getJson(route('celestial-objects.show', [
            'code' => $entryCO->code,
            'include' => 'jumppoints',
        ]));

        $response->assertSuccessful();

        $jp = $response->json('data.jumppoints');

        expect($jp)->not->toBeNull()
            ->and($jp['id'])->toBe($jumppoint->cig_id)
            ->and($jp['name'])->toBe('Nexus Jump')
            ->and($jp['size'])->toBe('MEDIUM')
            ->and($jp['entry'])->not->toBeNull()
            ->and($jp['entry']['code'])->toBe($entryCO->code)
            ->and($jp['entry']['id'])->toBe($entryCO->cig_id)
            ->and($jp['exit'])->not->toBeNull()
            ->and($jp['exit']['code'])->toBe($exitCO->code)
            ->and($jp['exit']['id'])->toBe($exitCO->cig_id);
    });

    it('returns null jumppoints when no jumppoint exists', function (): void {
        $system = Starsystem::factory()->create(['code' => 'SOL']);

        CelestialObject::factory()->create([
            'starsystem_id' => $system->cig_id,
            'code' => 'SOL.STARS.SUN',
            'type' => 'STAR',
        ]);

        $response = $this->getJson(route('celestial-objects.index', [
            'include' => 'jumppoints',
        ]));

        $response->assertSuccessful();

        $jp = $response->json('data.0.jumppoints');

        expect($jp)->toBeNull();
    });

    it('returns jumppoints in index when included', function (): void {
        $system = Starsystem::factory()->create(['code' => 'ELLIS']);

        $entryCO = CelestialObject::factory()->create([
            'starsystem_id' => $system->cig_id,
            'code' => 'ELLIS.JUMPPOINTS.NEXUS',
            'type' => 'JUMPPOINT',
        ]);

        $exitCO = CelestialObject::factory()->create([
            'starsystem_id' => $system->cig_id,
            'code' => 'ELLIS.JUMPPOINTS.TERRA',
            'type' => 'JUMPPOINT',
        ]);

        $jumppoint = Jumppoint::factory()->create([
            'entry_id' => $entryCO->cig_id,
            'exit_id' => $exitCO->cig_id,
            'name' => 'Nexus Jump',
            'size' => 'LARGE',
        ]);

        $response = $this->getJson(route('celestial-objects.index', [
            'include' => 'jumppoints',
        ]));

        $response->assertSuccessful();

        $item = collect($response->json('data'))->first(fn (array $item): bool => $item['code'] === $entryCO->code);

        expect($item)->not->toBeNull()
            ->and($item['jumppoints']['id'])->toBe($jumppoint->cig_id)
            ->and($item['jumppoints']['entry']['code'])->toBe($entryCO->code)
            ->and($item['jumppoints']['exit']['code'])->toBe($exitCO->code);
    });
});
