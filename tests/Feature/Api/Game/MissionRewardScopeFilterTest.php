<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

function createMissionData(GameVersion $version, array $overrides = []): MissionData
{
    $mission = Mission::factory()->create();

    return MissionData::factory()
        ->forVersion($version)
        ->forMission($mission)
        ->create($overrides);
}

it('filters by reward_scope column', function (): void {
    createMissionData($this->version, ['reward_scope' => 'Bounty Hunter', 'title' => 'BH Mission']);
    createMissionData($this->version, ['reward_scope' => 'Hauling', 'title' => 'Haul Mission']);

    $response = $this->getJson('/api/missions?filter[reward_scope]=Bounty Hunter');

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('BH Mission')
        ->and($titles)->not->toContain('Haul Mission');
});

it('supports multiple reward_scope values', function (): void {
    createMissionData($this->version, ['reward_scope' => 'Bounty Hunter', 'title' => 'BH']);
    createMissionData($this->version, ['reward_scope' => 'Hauling', 'title' => 'Haul']);
    createMissionData($this->version, ['reward_scope' => 'Salvage', 'title' => 'Salvage']);

    $response = $this->getJson('/api/missions?filter[reward_scope][]=Bounty Hunter&filter[reward_scope][]=Salvage');

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('BH')
        ->and($titles)->toContain('Salvage')
        ->and($titles)->not->toContain('Haul');
});

it('returns empty for non-existent reward_scope', function (): void {
    createMissionData($this->version, ['reward_scope' => 'Bounty Hunter', 'title' => 'BH']);

    $response = $this->getJson('/api/missions?filter[reward_scope]=NonExistent');

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->not->toContain('BH');
});
