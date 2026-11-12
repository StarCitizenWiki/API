<?php

declare(strict_types=1);

use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\Mission\MissionPrerequisiteGroup;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use App\Models\StarCitizen\Starmap\Starsystem;
use Database\Factories\Rsi\CommLink\CommLinkFactory;

describe('Q-5 boolean filter inputs', function (): void {
    beforeEach(function (): void {
        GameVersion::factory()->create([
            'code' => '4.0.0-LIVE.1',
            'channel' => 'testing',
            'is_default' => true,
        ]);
    });

    it('rejects invalid boolean input', function (string $url): void {
        $this->getJson($url)->assertStatus(422);
    })->with([
        'medical beds' => ['/api/vehicles?filter[has_medical_beds]=ture'],
        'prerequisites' => ['/api/missions?filter[has_prerequisites]=ture'],
        'blueprints' => ['/api/missions?filter[has_blueprints]=junk'],
        'resources' => ['/api/locations?filter[has_resources]=ture'],
        'minor locations' => ['/api/locations?filter[hide_minor_locations]=junk'],
        'grouped' => ['/api/missions?filter[grouped]=flase'],
    ]);

    it('rejects lists and arrays for scalar controls', function (string $path, string $key): void {
        foreach (['true,false', ['true'], ['true', 'false']] as $value) {
            $this->getJson($path.'?'.http_build_query(['filter' => [$key => $value]]))->assertStatus(422);
        }
    })->with([
        ['/api/vehicles', 'has_medical_beds'],
        ['/api/missions', 'has_prerequisites'],
        ['/api/missions', 'has_blueprints'],
        ['/api/locations', 'has_resources'],
        ['/api/locations', 'hide_minor_locations'],
        ['/api/missions', 'grouped'],
    ]);

    it('treats empty scalar controls as unspecified', function (string $path, string $key): void {
        $unfiltered = $this->getJson($path)->assertOk()->json('data');
        $this->getJson($path.'?filter['.$key.']=')->assertOk()->assertJsonPath('data', $unfiltered);
    })->with([
        ['/api/vehicles', 'has_medical_beds'],
        ['/api/missions', 'has_prerequisites'],
        ['/api/missions', 'has_blueprints'],
        ['/api/locations', 'has_resources'],
        ['/api/locations', 'hide_minor_locations'],
        ['/api/missions', 'grouped'],
    ]);

    it('accepts existing boolean spellings', function (string $path, string $key, string $value): void {
        $this->getJson($path.'?'.http_build_query(['filter' => [$key => $value]]))->assertOk();
    })->with(function (): array {
        $cases = [];
        foreach ([
            ['/api/vehicles', 'has_medical_beds'],
            ['/api/missions', 'has_prerequisites'],
            ['/api/missions', 'has_blueprints'],
            ['/api/locations', 'has_resources'],
            ['/api/locations', 'hide_minor_locations'],
            ['/api/missions', 'grouped'],
        ] as [$path, $key]) {
            foreach (['true', 'false', '1', '0', 'yes', 'no', 'on', 'off', ' YES '] as $value) {
                $cases["$key $value"] = [$path, $key, $value];
            }
        }

        return $cases;
    });
});

describe('missions', function (): void {
    beforeEach(function (): void {
        $this->version = GameVersion::factory()->create([
            'code' => '4.0.0-LIVE.1',
            'channel' => 'testing',
            'is_default' => true,
        ]);
    });

    it('filters prerequisite and blueprint presence using scalar boolean spellings', function (): void {
        $matching = MissionData::factory()->for(Mission::factory(), 'mission')->forVersion($this->version)->create(['title' => 'Matching']);
        MissionData::factory()->for(Mission::factory(), 'mission')->forVersion($this->version)->create(['title' => 'Other']);
        MissionPrerequisiteGroup::create(['mission_data_id' => $matching->id, 'group_index' => 0, 'required_count' => 1]);
        $blueprint = BlueprintData::factory()->create();
        $item = ItemData::factory()->for($this->version, 'gameVersion')->create();
        $matching->blueprints()->attach($blueprint->id, ['pool_uuid' => fake()->uuid(), 'item_data_id' => $item->id, 'chance' => 1.0]);

        foreach (['has_prerequisites', 'has_blueprints'] as $key) {
            foreach (['true' => 'Matching', ' YES ' => 'Matching', 'false' => 'Other', ' OFF ' => 'Other'] as $value => $title) {
                $this->getJson('/api/missions?'.http_build_query(['filter' => [$key => $value]]))
                    ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', $title);
            }
        }
    });

    it('applies comma-separated rank_index values as OR', function (): void {
        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['title' => 'Easy Mission', 'rank_index' => 1]);

        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['title' => 'Hard Mission', 'rank_index' => 5]);

        $response = $this->getJson('/api/missions?filter[rank_index]=1,5');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(2);
    });

    it('matches a single rank_index exactly', function (): void {
        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['title' => 'Easy Mission', 'rank_index' => 1]);

        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['title' => 'Hard Mission', 'rank_index' => 5]);

        $response = $this->getJson('/api/missions?filter[rank_index]=5');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
        expect($response->json('data.0.rank_index'))->toBe(5);
    });

    it('rejects operators on rank_index', function (): void {
        $this->getJson('/api/missions?filter[rank_index]=>3')->assertStatus(422);
    });

    it('rejects non-numeric rank_index values', function (): void {
        $this->getJson('/api/missions?filter[rank_index]=abc')->assertStatus(422);
    });

    it('rejects junk on boolean mission filters', function (): void {
        $this->getJson('/api/missions?filter[illegal]=maybe')->assertStatus(422);
    });

    it('accepts booleans on boolean mission filters', function (): void {
        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['illegal' => true]);

        $this->getJson('/api/missions?filter[illegal]=true')->assertOk();
        $this->getJson('/api/missions?filter[illegal]=0')->assertOk();
    });

    it('applies comma-separated boolean lists as OR on has_combat', function (): void {
        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['title' => 'Fighters', 'has_combat' => true]);

        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['title' => 'Haulers', 'has_combat' => false]);

        $all = $this->getJson('/api/missions?filter[has_combat]=true,false');

        $all->assertOk();
        expect($all->json('meta.total'))->toBe(2);

        $combatOnly = $this->getJson('/api/missions?filter[has_combat]=true');

        $combatOnly->assertOk();
        expect($combatOnly->json('meta.total'))->toBe(1);
    });

    it('rejects comma-separated boolean lists containing junk', function (): void {
        $this->getJson('/api/missions?filter[has_combat]=true,maybe')->assertStatus(422);
    });
});

describe('factions', function (): void {
    beforeEach(function (): void {
        GameVersion::factory()->create([
            'code' => '4.0.0-LIVE.1',
            'channel' => 'testing',
            'is_default' => true,
        ]);
    });

    it('rejects junk on boolean filters', function (): void {
        $this->getJson('/api/factions?filter[lawful]=junk')->assertStatus(422);
        $this->getJson('/api/factions?filter[is_npc]=junk')->assertStatus(422);
    });
});

describe('game versions', function (): void {
    it('rejects junk on is_default', function (): void {
        $this->getJson('/api/game-versions?filter[is_default]=abc')->assertStatus(422);
    });

    it('accepts booleans on is_default', function (): void {
        GameVersion::factory()->create(['is_default' => true]);

        $this->getJson('/api/game-versions?filter[is_default]=true')->assertOk();
    });
});

describe('locations', function (): void {
    beforeEach(function (): void {
        $this->version = GameVersion::factory()->create([
            'code' => '4.0.0-LIVE.1',
            'channel' => 'testing',
            'is_default' => true,
        ]);
    });

    it('rejects junk on boolean filters', function (): void {
        $this->getJson('/api/locations?filter[is_scannable]=junk')->assertStatus(422);
        $this->getJson('/api/locations?filter[block_travel]=junk')->assertStatus(422);
    });

    it('applies comma-separated boolean lists as OR on is_scannable', function (): void {
        StarmapLocationData::factory()
            ->for(StarmapLocation::factory()->create(), 'location')
            ->create([
                'game_version_id' => $this->version->id,
                'is_scannable' => true,
            ]);

        StarmapLocationData::factory()
            ->for(StarmapLocation::factory()->create(), 'location')
            ->create([
                'game_version_id' => $this->version->id,
                'is_scannable' => false,
            ]);

        $response = $this->getJson('/api/locations?filter[is_scannable]=true,false');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(2);
    });

    it('rejects non-uuid parent_uuid values', function (): void {
        $this->getJson('/api/locations?filter[parent_uuid]=abc')->assertStatus(422);
        $this->getJson('/api/locations?filter[parent_uuid]=nyx')->assertStatus(422);
    });

    it('accepts uuid parent_uuid values', function (): void {
        StarmapLocationData::factory()
            ->for(StarmapLocation::factory()->create(), 'location')
            ->create();

        $this->getJson('/api/locations?filter[parent_uuid]=00000000-0000-0000-0000-000000000000')->assertOk();
    });

    it('accepts comma-separated parent_uuid lists', function (): void {
        StarmapLocationData::factory()
            ->for(StarmapLocation::factory()->create(), 'location')
            ->create();

        $this->getJson('/api/locations?filter[parent_uuid]=00000000-0000-0000-0000-000000000000,11111111-1111-1111-1111-111111111111')->assertOk();
    });

    it('returns 200 not 500 for comma-separated parent_uuid lists with whitespace', function (): void {
        StarmapLocationData::factory()
            ->for(StarmapLocation::factory()->create(), 'location')
            ->create([
                'game_version_id' => $this->version->id,
                'parent_location_uuid' => '11111111-1111-1111-1111-111111111111',
            ]);

        $response = $this->getJson('/api/locations?filter[parent_uuid]=11111111-1111-1111-1111-111111111111, 22222222-2222-2222-2222-222222222222');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });
});

describe('blueprints', function (): void {
    beforeEach(function (): void {
        GameVersion::factory()->create([
            'code' => '4.0.0-LIVE.1',
            'channel' => 'testing',
            'is_default' => true,
        ]);
    });

    it('rejects non-uuid output.uuid values', function (): void {
        $this->getJson('/api/blueprints?filter[output.uuid]=abc')->assertStatus(422);
    });
});

describe('starsystems', function (): void {
    it('supports range operators on size', function (): void {
        Starsystem::factory()->create(['name' => 'Small System', 'aggregated_size' => 100.0]);
        Starsystem::factory()->create(['name' => 'Big System', 'aggregated_size' => 900.0]);

        $response = $this->getJson('/api/starsystems?filter[size]=>500');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('rejects non-numeric size values', function (): void {
        $this->getJson('/api/starsystems?filter[size]=abc')->assertStatus(422);
    });

    it('returns 422 not 500 for junk filters on starsystem search', function (): void {
        $this->postJson('/api/starsystems/search', ['query' => 'stanton', 'filter' => ['size' => 'junk']])
            ->assertStatus(422);
    });
});

describe('comm-links', function (): void {
    it('rejects non-numeric id values', function (): void {
        $this->getJson('/api/comm-links?filter[id]=abc')->assertStatus(422);
    });

    it('accepts numeric id values', function (): void {
        CommLinkFactory::new()->create(['cig_id' => 15740]);

        $this->getJson('/api/comm-links?filter[id]=15740')->assertOk();
    });

    it('returns 422 not 500 for values outside the int8 id range', function (): void {
        // cig_id is bigint, so the id filter allows the full int8 range.
        $this->getJson('/api/comm-links?filter[id]=9223372036854775807')->assertOk();
        $this->getJson('/api/comm-links?filter[id]=-9223372036854775808')->assertOk();
        $this->getJson('/api/comm-links?filter[id]=9223372036854775808')->assertStatus(422);
        $this->getJson('/api/comm-links?filter[id]=-9223372036854775809')->assertStatus(422);
    });
});

describe('array filter values', function (): void {
    beforeEach(function (): void {
        $this->version = GameVersion::factory()->create([
            'code' => '4.0.0-LIVE.1',
            'channel' => 'testing',
            'is_default' => true,
        ]);
    });

    it('accepts arrays of valid boolean values', function (): void {
        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['illegal' => true]);

        $response = $this->getJson('/api/missions?'.http_build_query(['filter' => ['illegal' => ['true']]]));

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('returns 422 not 500 when a boolean array contains junk', function (): void {
        $this->getJson('/api/missions?'.http_build_query(['filter' => ['illegal' => ['true', 'maybe']]]))->assertStatus(422);
    });

    it('accepts arrays of valid numeric values', function (): void {
        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['rank_index' => 1]);

        MissionData::factory()
            ->for(Mission::factory(), 'mission')
            ->forVersion($this->version)
            ->create(['rank_index' => 5]);

        $response = $this->getJson('/api/missions?'.http_build_query(['filter' => ['rank_index' => ['1', '5']]]));

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(2);
    });

    it('returns 422 not 500 when a numeric array contains junk', function (): void {
        $this->getJson('/api/missions?'.http_build_query(['filter' => ['rank_index' => ['1', 'abc']]]))->assertStatus(422);
    });

    it('accepts arrays of valid uuid values', function (): void {
        StarmapLocationData::factory()
            ->for(StarmapLocation::factory()->create(), 'location')
            ->create();

        $this->getJson('/api/locations?'.http_build_query(['filter' => ['parent_uuid' => ['00000000-0000-0000-0000-000000000000']]]))->assertOk();
    });

    it('returns 422 not 500 when a uuid array contains junk', function (): void {
        $this->getJson('/api/locations?'.http_build_query(['filter' => ['parent_uuid' => ['00000000-0000-0000-0000-000000000000', 'nyx']]]))->assertStatus(422);
    });
});

describe('vehicles', function (): void {
    beforeEach(function (): void {
        $this->version = GameVersion::factory()->create([
            'code' => '4.0.0-LIVE.1',
            'channel' => 'testing',
            'is_default' => true,
        ]);
    });

    it('filters medical beds with valid boolean values', function (): void {
        $medical = VehicleData::factory()->for(Vehicle::factory(), 'vehicle')->for($this->version, 'gameVersion')
            ->create(['max_medical_tier' => 'T2']);
        $other = VehicleData::factory()->for(Vehicle::factory(), 'vehicle')->for($this->version, 'gameVersion')
            ->create(['max_medical_tier' => null]);

        $this->getJson('/api/vehicles?filter[has_medical_beds]=yes')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.uuid', $medical->vehicle->uuid);
        $this->getJson('/api/vehicles?filter[has_medical_beds]=no')
            ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.uuid', $other->vehicle->uuid);
    });

    it('returns 422 not 500 when a scalar key clobbers a dotted filter', function (): void {
        $this->getJson('/api/vehicles?filter[crew.min]=junk&filter[crew]=1')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The filter parameters are ambiguous.');
    });

    it('returns 422 when the colliding keys arrive in reverse order', function (): void {
        $this->getJson('/api/vehicles?filter[crew]=1&filter[crew.min]=junk')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The filter parameters are ambiguous.');
    });

    it('returns 422 when a scalar key collides with a deeper dotted filter', function (): void {
        $this->getJson('/api/vehicles?filter[shield]=10&filter[shield.hp]=junk')
            ->assertStatus(422)
            ->assertJsonPath('message', 'The filter parameters are ambiguous.');
    });

    it('accepts the dotted crew.min filter alone', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->create(['name' => 'Crewed Ship', 'crew_min' => 2]);

        $response = $this->getJson('/api/vehicles?filter[crew.min]=>1');

        $response->assertOk();
        expect($response->json('meta.total'))->toBe(1);
    });

    it('accepts a scalar filter key alone', function (): void {
        $this->getJson('/api/vehicles?filter[is_spaceship]=true')->assertOk();
    });

    it('accepts non-colliding dotted and scalar filter keys together', function (): void {
        $this->getJson('/api/vehicles?filter[crew.min]=2&filter[health]=>100')->assertOk();
    });

    it('returns 422 not 500 for junk filters on vehicle search', function (): void {
        $this->postJson('/api/vehicles/search', ['query' => 'arrow', 'filter' => ['mass_total' => 'junk']])
            ->assertStatus(422);
    });

    it('applies valid filters on vehicle search', function (): void {
        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->create(['name' => 'Arrow', 'mass_total' => 12345]);

        VehicleData::factory()
            ->for(Vehicle::factory(), 'vehicle')
            ->for($this->version, 'gameVersion')
            ->create(['name' => 'Arrow Heavy', 'mass_total' => 99999]);

        $response = $this->postJson('/api/vehicles/search', ['query' => 'Arrow', 'filter' => ['mass_total' => 12345]]);

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(1);
    });
});
