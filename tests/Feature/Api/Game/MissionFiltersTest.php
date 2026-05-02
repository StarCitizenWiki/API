<?php

declare(strict_types=1);

use App\Models\Game\Faction;
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

it('returns 200 when using query filter with faction-linked missions', function (): void {
    $faction = Faction::factory()->create(['name' => 'Nine Tails']);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create([
            'title' => 'Red Alert',
            'description' => 'A dangerous mission',
            'faction_id' => $faction->id,
        ]);

    $response = $this->getJson('/api/missions/filters?filter[query]=red+ar');

    $response->assertSuccessful();
});

it('returns 200 when using title filter with faction-linked missions', function (): void {
    $faction = Faction::factory()->create(['name' => 'Outlaws']);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create([
            'title' => 'Special Delivery',
            'faction_id' => $faction->id,
        ]);

    $response = $this->getJson('/api/missions/filters?filter[title]=Delivery');

    $response->assertSuccessful();
});

it('returns 200 when using description filter with faction-linked missions', function (): void {
    $faction = Faction::factory()->create(['name' => 'Bounty Hunters']);

    $mission = Mission::factory()->create();
    MissionData::factory()
        ->forVersion($this->version)
        ->forMission($mission)
        ->create([
            'description' => 'Eliminate the target at the outpost',
            'faction_id' => $faction->id,
        ]);

    $response = $this->getJson('/api/missions/filters?filter[description]=target');

    $response->assertSuccessful();
});
