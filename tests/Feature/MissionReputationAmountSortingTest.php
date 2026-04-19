<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->version = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'released_at' => now(),
        'is_default' => true,
    ]);
});

function createMissionWithReputationAmount(GameVersion $version, ?int $amount, string $title): MissionData
{
    $mission = Mission::factory()->create();

    $reputationGained = $amount !== null
        ? [['Faction' => 'Ninetails', 'Scope' => 'FactionReputation', 'Amount' => $amount]]
        : [];

    return MissionData::factory()
        ->forVersion($version)
        ->forMission($mission)
        ->create([
            'title' => $title,
            'data' => ['ReputationGained' => $reputationGained],
        ]);
}

it('sorts missions by reputation_amount ascending', function (): void {
    createMissionWithReputationAmount($this->version, 500, 'High Rep');
    createMissionWithReputationAmount($this->version, 100, 'Low Rep');
    createMissionWithReputationAmount($this->version, 250, 'Mid Rep');

    $response = $this->getJson('/api/missions?sort=reputation_amount');

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title')->toArray();
    expect($titles)->toBe(['Low Rep', 'Mid Rep', 'High Rep']);
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('sorts missions by reputation_amount descending', function (): void {
    createMissionWithReputationAmount($this->version, 500, 'High Rep');
    createMissionWithReputationAmount($this->version, 100, 'Low Rep');
    createMissionWithReputationAmount($this->version, 250, 'Mid Rep');

    $response = $this->getJson('/api/missions?sort=-reputation_amount');

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title')->toArray();
    expect($titles)->toBe(['High Rep', 'Mid Rep', 'Low Rep']);
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');

it('places null reputation_amount last when sorting', function (): void {
    createMissionWithReputationAmount($this->version, 100, 'Has Rep');
    createMissionWithReputationAmount($this->version, null, 'No Rep');
    createMissionWithReputationAmount($this->version, 200, 'More Rep');

    $response = $this->getJson('/api/missions?sort=reputation_amount');

    $response->assertSuccessful();
    $titles = collect($response->json('data'))->pluck('title')->toArray();
    expect($titles)->toBe(['Has Rep', 'More Rep', 'No Rep']);
})->skip(fn (): bool => DB::connection()->getDriverName() !== 'pgsql', 'PostgreSQL only test')
    ->group('db-pgsql');
