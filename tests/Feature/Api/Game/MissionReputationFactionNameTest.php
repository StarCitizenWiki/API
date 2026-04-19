<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('resolves UNINITIALIZED faction name in reputation_gained via faction UUID', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'Nine Tails',
        'uuid' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'data' => [
                'ReputationGained' => [
                    [
                        'Faction' => '<= UNINITIALIZED =>',
                        'FactionUUID' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                        'Amount' => 500,
                        'Scope' => 'FactionReputation',
                        'Tier' => null,
                    ],
                ],
                'ReputationLost' => null,
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.reputation_gained.0.faction', 'Nine Tails')
        ->assertJsonPath('data.reputation_gained.0.faction_uuid', 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');
});

it('resolves UNINITIALIZED faction name in reputation_lost via faction UUID', function (): void {
    $faction = Faction::factory()->create([
        'name' => 'Crusader Security',
        'uuid' => '11111111-2222-3333-4444-555555555555',
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'data' => [
                'ReputationGained' => null,
                'ReputationLost' => [
                    [
                        'Faction' => '<= UNINITIALIZED =>',
                        'FactionUUID' => '11111111-2222-3333-4444-555555555555',
                        'Amount' => -200,
                        'Scope' => 'FactionReputation',
                        'Tier' => null,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.reputation_lost.0.faction', 'Crusader Security')
        ->assertJsonPath('data.reputation_lost.0.faction_uuid', '11111111-2222-3333-4444-555555555555');
});

it('keeps normal faction name as-is when not UNINITIALIZED', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'data' => [
                'ReputationGained' => [
                    [
                        'Faction' => 'Nine Tails',
                        'FactionUUID' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                        'Amount' => 100,
                        'Scope' => 'FactionReputation',
                        'Tier' => null,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.reputation_gained.0.faction', 'Nine Tails');
});

it('falls back to raw faction name when UUID does not match a known faction', function (): void {
    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'data' => [
                'ReputationGained' => [
                    [
                        'Faction' => '<= UNINITIALIZED =>',
                        'FactionUUID' => '00000000-0000-0000-0000-000000000000',
                        'Amount' => 100,
                        'Scope' => 'FactionReputation',
                        'Tier' => null,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.reputation_gained.0.faction', '<= UNINITIALIZED =>');
});

it('resolves mixed UNINITIALIZED and normal factions in same response', function (): void {
    Faction::factory()->create([
        'name' => 'XenoThreat',
        'uuid' => '99999999-8888-7777-6666-555555555555',
    ]);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'data' => [
                'ReputationGained' => [
                    [
                        'Faction' => 'Nine Tails',
                        'FactionUUID' => 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee',
                        'Amount' => 100,
                        'Scope' => 'FactionReputation',
                        'Tier' => null,
                    ],
                    [
                        'Faction' => '<= UNINITIALIZED =>',
                        'FactionUUID' => '99999999-8888-7777-6666-555555555555',
                        'Amount' => 250,
                        'Scope' => 'FactionReputation',
                        'Tier' => null,
                    ],
                ],
            ],
        ]);

    $response = $this->getJson("/api/missions/{$mission->uuid}");

    $response->assertSuccessful()
        ->assertJsonPath('data.reputation_gained.0.faction', 'Nine Tails')
        ->assertJsonPath('data.reputation_gained.1.faction', 'XenoThreat');
});
