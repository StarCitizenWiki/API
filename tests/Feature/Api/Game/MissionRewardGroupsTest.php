<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\Mission\MissionRewardGroup;

beforeEach(function (): void {
    $this->gameVersion = GameVersion::factory()->create([
        'code' => '4.0.0-LIVE',
        'channel' => 'live',
        'is_default' => true,
        'released_at' => now(),
    ]);

    $this->mission = Mission::factory()->create();

    $this->missionData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($this->mission)
        ->create([
            'entry_type' => 'mission_broker',
        ]);
});

function attachRewardItem(MissionRewardGroup $group, ItemData $itemData, ?int $amount = null, ?bool $sendToHome = null): void
{
    $group->items()->create([
        'item_data_id' => $itemData->id,
        'amount' => $amount,
        'send_to_home' => $sendToHome,
    ]);
}

function makeItemData(GameVersion $version, string $uuid, string $name): ItemData
{
    $item = Item::factory()->create([
        'uuid' => $uuid,
        'slug' => str($name)->slug(),
    ]);

    return ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => $name,
    ]);
}

describe('reward_groups', function (): void {
    it('maps grouped reward items with weight and owner flags', function (): void {
        $weapon = makeItemData($this->gameVersion, '44444444-4444-4444-4444-444444444444', 'Energy Cell');
        $armor = makeItemData($this->gameVersion, '55555555-5555-5555-5555-555555555555', 'Combat Armor');

        $first = MissionRewardGroup::factory()
            ->forMissionData($this->missionData)
            ->create(['group_index' => 0, 'weight' => 0.5, 'award_only_to_mission_owner' => true]);
        attachRewardItem($first, $weapon, 10, true);

        $second = MissionRewardGroup::factory()
            ->forMissionData($this->missionData)
            ->create(['group_index' => 1, 'weight' => null, 'award_only_to_mission_owner' => null]);
        attachRewardItem($second, $armor, 3, false);

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
        $first = makeItemData($this->gameVersion, '44444444-4444-4444-4444-444444444444', 'Energy Cell');
        $second = makeItemData($this->gameVersion, '55555555-5555-5555-5555-555555555555', 'Combat Armor');

        $groupA = MissionRewardGroup::factory()->forMissionData($this->missionData)->create(['group_index' => 0]);
        attachRewardItem($groupA, $first, 10, true);

        $groupB = MissionRewardGroup::factory()->forMissionData($this->missionData)->create(['group_index' => 1]);
        attachRewardItem($groupB, $second, 3, false);

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
