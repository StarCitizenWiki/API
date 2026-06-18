<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

it('resolves location by slug', function (): void {
    $location = StarmapLocation::factory()->create(['slug' => 'area18']);
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $response = $this->get('/locations/area18');

    $response->assertSuccessful();
});

it('shows mission count in overview stats on location show page', function (): void {
    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee('Missions');
    $response->assertSee('data-testid="starmap-location-quick-facts"', false);
});

it('shows missions section when location has missions', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $faction = Faction::factory()->create(['name' => 'Nine Tails']);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->for($faction, 'faction')
        ->create([
            'title' => 'Bounty Hunt Target',
            'mission_type' => 'Bounty Hunter',
            'illegal' => true,
            'has_combat' => true,
            'reward_min' => 5000,
            'reward_max' => 10000,
            'reward_currency' => 'aUEC',
        ]);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);
    $locationData->forceFill(['mission_count' => 1])->save();

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee('data-testid="starmap-location-missions-section"', false);
    $response->assertSee('data-testid="starmap-location-missions"', false);
    $response->assertSee('Bounty Hunt Target');
    $response->assertSee('Available at Location');
    $response->assertSee('View all');
    $response->assertSee('missions');
});

it('does not show missions section when location has no missions', function (): void {
    $location = StarmapLocation::factory()->create();
    StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Empty Outpost',
            'system' => 'Stanton',
            'type_name' => 'Outpost',
            'data' => ['Type' => ['Classification' => 'Manmade']],
        ]);

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertDontSee('data-testid="starmap-location-missions-section"', false);
});

it('includes link to filtered missions index', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->create(['title' => 'Test Mission']);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);
    $locationData->forceFill(['mission_count' => 1])->save();

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee('filter[location]='.$location->uuid, false);
});

it('links mission cards to mission show page', function (): void {
    $location = StarmapLocation::factory()->create();
    $locationData = StarmapLocationData::factory()
        ->for($location, 'location')
        ->for($this->version, 'gameVersion')
        ->create([
            'name' => 'Area18',
            'system' => 'Stanton',
            'type_name' => 'LandingZone',
            'data' => ['Type' => ['Classification' => 'Landing Zone']],
        ]);

    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->for($mission, 'mission')
        ->for($this->version, 'gameVersion')
        ->for(Faction::factory()->create(['name' => 'Nine Tails']), 'faction')
        ->create([
            'title' => 'Bounty Hunt',
            'mission_type' => 'Bounty Hunter',
            'illegal' => true,
            'has_combat' => true,
        ]);

    $locationData->missions()->attach($missionData->id, ['purpose' => 'availability']);
    $locationData->forceFill(['mission_count' => 1])->save();

    $response = $this->get('/locations/'.$location->uuid);

    $response->assertSuccessful();
    $response->assertSee(route('web.missions.show', ['mission' => $mission->slug]), false);
});

describe('numeric suffix fallback', function (): void {
    it('redirects a base slug to the single canonical suffixed slug (trace scenario)', function (): void {
        // The trace: GET /locations/onyx-facility-s2b1 404s because the stored
        // slug drifted to onyx-facility-s2b1-2 via suffix oscillation.
        $canonical = StarmapLocation::factory()->create(['slug' => 'onyx-facility-s2b1-2']);
        StarmapLocationData::factory()
            ->for($canonical, 'location')
            ->for($this->version, 'gameVersion')
            ->create([
                'name' => 'Onyx Facility S2B1',
                'system' => 'Nyx',
                'type_name' => 'Outpost',
            ]);

        $response = $this->get('/locations/onyx-facility-s2b1');

        $response->assertStatus(301);
        expect($response->headers->get('Location'))->toEndWith('/locations/onyx-facility-s2b1-2');
    });

    it('redirects a suffixed slug back to the canonical base slug', function (): void {
        $canonical = StarmapLocation::factory()->create(['slug' => 'bloom']);
        StarmapLocationData::factory()
            ->for($canonical, 'location')
            ->for($this->version, 'gameVersion')
            ->create(['name' => 'Bloom', 'system' => 'Pyro', 'type_name' => 'Outpost']);

        $response = $this->get('/locations/bloom-2');

        $response->assertStatus(301);
        expect($response->headers->get('Location'))->toEndWith('/locations/bloom');
    });

    it('preserves the query string when redirecting', function (): void {
        $canonical = StarmapLocation::factory()->create(['slug' => 'gaslight-2']);
        StarmapLocationData::factory()
            ->for($canonical, 'location')
            ->for($this->version, 'gameVersion')
            ->create(['name' => 'Gaslight', 'system' => 'Pyro', 'type_name' => 'Outpost']);

        $response = $this->get('/locations/gaslight?version=4.8.0-LIVE.11825000');

        $response->assertStatus(301);
        expect($response->headers->get('Location'))
            ->toEndWith('/locations/gaslight-2?version=4.8.0-LIVE.11825000');
    });

    it('returns 404 when no base slug matches at all', function (): void {
        $response = $this->get('/locations/totally-unknown-location');

        $response->assertNotFound();
    });

    it('returns 404 when multiple locations share the base name', function (): void {
        // Ambiguous procedurally-generated names (e.g. many "QV Logistics
        // Station" outposts) cannot be resolved to a single canonical slug.
        foreach (['derelict-outpost-2', 'derelict-outpost-3'] as $slug) {
            $location = StarmapLocation::factory()->create(['slug' => $slug]);
            StarmapLocationData::factory()
                ->for($location, 'location')
                ->for($this->version, 'gameVersion')
                ->create(['name' => 'Derelict Outpost', 'system' => 'Pyro', 'type_name' => 'Outpost']);
        }

        $response = $this->get('/locations/derelict-outpost');

        $response->assertNotFound();
    });

    it('does not attempt fallback for uuid identifiers', function (): void {
        $response = $this->get('/locations/'.fake()->uuid());

        $response->assertNotFound();
    });

    it('does not redirect when the only candidate is the requested slug', function (): void {
        // A location row exists but has no displayable data for the version, so
        // the detail lookup 404s. The fallback must not redirect to itself.
        StarmapLocation::factory()->create(['slug' => 'lonely-outpost']);

        $response = $this->get('/locations/lonely-outpost');

        $response->assertNotFound();
    });

    it('still resolves a direct slug without redirecting', function (): void {
        $location = StarmapLocation::factory()->create(['slug' => 'grimhex']);
        StarmapLocationData::factory()
            ->for($location, 'location')
            ->for($this->version, 'gameVersion')
            ->create(['name' => 'GrimHEX', 'system' => 'Stanton', 'type_name' => 'Outpost']);

        $response = $this->get('/locations/grimhex');

        $response->assertSuccessful();
    });
});
