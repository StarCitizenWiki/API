<?php

declare(strict_types=1);
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;

beforeEach(function (): void {
    $this->gameVersion = createDefaultGameVersion();

    $this->mission = Mission::factory()->create();

    $this->missionData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($this->mission)
        ->create([
            'entry_type' => 'mission_broker',
        ]);
});

describe('reward_groups', function (): void {
    it('maps grouped reward items with weight and owner flags', function (): void {
        createRewardItem($this->gameVersion, $this->missionData, '44444444-4444-4444-4444-444444444444', 'Energy Cell', 0, 0.5, true, 10, true);
        createRewardItem($this->gameVersion, $this->missionData, '55555555-5555-5555-5555-555555555555', 'Combat Armor', 1, null, null, 3, false);

        $this->getJson("/api/missions/{$this->mission->uuid}")
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.reward_groups')
            ->assertJsonPath('data.reward_groups.0.group_index', 0)
            ->assertJsonPath('data.reward_groups.0.weight', 0.5)
            ->assertJsonPath('data.reward_groups.0.award_only_to_mission_owner', true)
            ->assertJsonPath('data.reward_groups.0.items.0.name', 'Energy Cell')
            ->assertJsonPath('data.reward_groups.0.items.0.uuid', '44444444-4444-4444-4444-444444444444')
            ->assertJsonPath('data.reward_groups.0.items.0.amount', 10)
            ->assertJsonPath('data.reward_groups.0.items.0.send_to_home', true)
            ->assertJsonPath('data.reward_groups.1.group_index', 1)
            ->assertJsonPath('data.reward_groups.1.weight', null)
            ->assertJsonPath('data.reward_groups.1.award_only_to_mission_owner', null)
            ->assertJsonPath('data.reward_groups.1.items.0.name', 'Combat Armor')
            ->assertJsonPath('data.reward_groups.1.items.0.amount', 3)
            ->assertJsonPath('data.reward_groups.1.items.0.send_to_home', false);
    });

    it('flattens all group items into the legacy reward_items array', function (): void {
        createRewardItem($this->gameVersion, $this->missionData, '44444444-4444-4444-4444-444444444444', 'Energy Cell', 0, null, true, 10, true);
        createRewardItem($this->gameVersion, $this->missionData, '55555555-5555-5555-5555-555555555555', 'Combat Armor', 1, null, false, 3, false);

        $this->getJson("/api/missions/{$this->mission->uuid}")
            ->assertSuccessful()
            ->assertJsonCount(2, 'data.reward_items')
            ->assertJsonPath('data.reward_items.0.name', 'Energy Cell')
            ->assertJsonPath('data.reward_items.0.amount', 10)
            ->assertJsonPath('data.reward_items.1.name', 'Combat Armor')
            ->assertJsonPath('data.reward_items.1.amount', 3);
    });
});

it('omits reward_groups and reward_items when there are none', function (): void {
    $this->getJson("/api/missions/{$this->mission->uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.reward_groups', null)
        ->assertJsonPath('data.reward_items', null);
});
