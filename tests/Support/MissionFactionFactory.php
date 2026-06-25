<?php

declare(strict_types=1);

use App\Models\Game\Faction;
use App\Models\Game\FactionReputationRef;
use App\Models\Game\FactionScope;
use App\Models\Game\FactionStanding;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\Mission\MissionRewardGroup;
use Illuminate\Database\Eloquent\Collection;

if (! function_exists('createFactionWithReputationLadder')) {
    /**
     * Create a Faction with a reputation scope, standings, and ref.
     * Shared by Web/MissionShowFactionTest and Api/Game/MissionShowFactionTest.
     *
     * @return array{faction: Faction, scope: FactionScope, standings: Collection}
     */
    function createFactionWithReputationLadder(array $factionOverrides = []): array
    {
        $scope = FactionScope::factory()->create([
            'scope_name' => 'FactionReputation',
        ]);

        $hostile = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Hostile', 'display_name' => 'Hostile', 'min_reputation' => -1]);
        $neutral = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Neutral', 'display_name' => 'Neutral', 'min_reputation' => 0]);
        $allied = FactionStanding::factory()->create(['faction_scope_id' => $scope->id, 'name' => 'Allied', 'display_name' => 'Allied', 'min_reputation' => 50000]);

        $faction = Faction::factory()->create(array_merge([
            'name' => 'Nine Tails',
            'faction_type' => 'Unlawful',
            'lawful' => false,
            'headquarters' => 'Grim HEX',
            'area' => 'Stanton',
            'focus' => 'Piracy',
            'founded' => '2872',
            'has_reputation' => true,
        ], $factionOverrides));

        FactionReputationRef::factory()->create([
            'faction_id' => $faction->id,
            'faction_scope_id' => $scope->id,
        ]);

        return [
            'faction' => $faction,
            'scope' => $scope,
            'standings' => collect([$hostile, $neutral, $allied]),
        ];
    }
}

if (! function_exists('createMissionWithFaction')) {
    /**
     * Create a Mission + MissionData linked to the given faction and game version.
     */
    function createMissionWithFaction(GameVersion $version, Faction $faction, array $missionDataOverrides = []): MissionData
    {
        $mission = Mission::factory()->create();

        return MissionData::factory()
            ->forVersion($version)
            ->forMission($mission)
            ->create(array_merge(['faction_id' => $faction->id], $missionDataOverrides));
    }
}

if (! function_exists('createRewardItem')) {
    /**
     * Create an Item + ItemData + MissionRewardGroup + attach to MissionData.
     * Shared by Web/MissionRewardGroupsTest and Api/Game/MissionRewardGroupsTest.
     */
    function createRewardItem(GameVersion $version, MissionData $missionData, string $uuid, string $name, int $groupIndex, ?float $weight = null, ?bool $ownerOnly = null, ?int $amount = null, ?bool $sendToHome = null): void
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
}
