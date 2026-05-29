<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);
});

it('marks fixed reward only MBE missions as having rewards', function (): void {
    $mission = Mission::factory()->create();

    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'entry_type' => 'mission_broker',
            'reward_min' => 5000,
            'reward_max' => 0,
            'reward_currency' => 'UEC',
            'data' => [
                'FixedReward' => [
                    'Amount' => 5000,
                    'Max' => 0,
                    'BonusEligible' => false,
                    'Currency' => 'UEC',
                ],
            ],
        ]);

    $this->getJson("/api/missions/{$mission->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.reward_min', 5000)
        ->assertJsonPath('data.reward_currency', 'UEC')
        ->assertJsonPath('data.has_rewards', true);
});

it('does not mark zero fixed reward MBE missions as having rewards', function (): void {
    $mission = Mission::factory()->create();

    MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create([
            'entry_type' => 'mission_broker',
            'reward_min' => 0,
            'reward_max' => 0,
            'reward_currency' => 'UEC',
            'data' => [
                'FixedReward' => [
                    'Amount' => 0,
                    'Max' => 0,
                    'BonusEligible' => false,
                    'Currency' => 'UEC',
                ],
            ],
        ]);

    $this->getJson("/api/missions/{$mission->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.has_rewards', false);
});
