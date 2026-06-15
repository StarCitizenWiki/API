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
});

function rewardItem(GameVersion $version, MissionData $missionData, string $uuid, string $name, int $groupIndex, ?float $weight = null, ?bool $ownerOnly = null, ?int $amount = null, ?bool $sendToHome = null): void
{
    $item = Item::factory()->create([
        'uuid' => $uuid,
        'slug' => str($name)->slug(),
    ]);

    $itemData = ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => $name,
    ]);

    $group = MissionRewardGroup::factory()
        ->forMissionData($missionData)
        ->create([
            'group_index' => $groupIndex,
            'weight' => $weight,
            'award_only_to_mission_owner' => $ownerOnly,
        ]);

    $group->items()->create([
        'item_data_id' => $itemData->id,
        'amount' => $amount,
        'send_to_home' => $sendToHome,
    ]);
}

it('renders a table with one row per reward group on multi-group missions', function (): void {
    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['title' => 'Bundle Reward Mission']);

    rewardItem($this->gameVersion, $missionData, '44444444-4444-4444-4444-444444444444', 'Energy Cell', 0, 0.5, true, 10, true);
    rewardItem($this->gameVersion, $missionData, '55555555-5555-5555-5555-555555555555', 'Combat Armor', 1, null, null, 3, false);

    $this->get("/missions/{$mission->slug}")
        ->assertSuccessful()
        ->assertSeeText('Reward Items')
        ->assertSeeText('One of the following reward bundles is awarded on completion.')
        ->assertSeeText('Awarded only to the mission owner.')
        ->assertSeeText('Reward Group 1')
        ->assertSeeText('Reward Group 2')
        ->assertSeeText('Energy Cell')
        ->assertSeeText('Combat Armor');
});

it('does not render group headers for single-group missions', function (): void {
    $mission = Mission::factory()->create();
    $missionData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mission)
        ->create(['title' => 'Single Reward Mission']);

    rewardItem($this->gameVersion, $missionData, '66666666-6666-6666-6666-666666666666', 'Frag Grenade', 0, null, null, 2, true);

    $this->get("/missions/{$mission->slug}")
        ->assertSuccessful()
        ->assertSeeText('Reward Items')
        ->assertSeeText('Frag Grenade')
        ->assertDontSeeText('Reward Group 1')
        ->assertDontSeeText('One of the following reward bundles is awarded on completion.');
});
