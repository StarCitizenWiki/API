<?php

declare(strict_types=1);

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\Mission\MissionPrerequisiteGroup;
use App\Models\Game\Mission\MissionPrerequisiteGroupMission;
use App\Models\Game\Mission\MissionUnlockGroup;
use App\Models\Game\Mission\MissionUnlockGroupMission;
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

it('groups prerequisite missions by title', function (): void {
    $mainMission = Mission::factory()->create();
    $mainData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mainMission)
        ->create();

    $linkedMission1 = Mission::factory()->create();
    $linkedData1 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission1)
        ->create(['title' => 'Supply Run', 'mission_type' => 'Delivery']);

    $linkedMission2 = Mission::factory()->create();
    $linkedData2 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission2)
        ->create(['title' => 'Supply Run', 'mission_type' => 'Delivery']);

    $linkedMission3 = Mission::factory()->create();
    $linkedData3 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission3)
        ->create(['title' => 'Different Mission', 'mission_type' => 'Bounty Hunter']);

    $group = MissionPrerequisiteGroup::create([
        'mission_data_id' => $mainData->id,
        'group_index' => 0,
        'required_count' => 2,
    ]);

    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData1->id,
    ]);
    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData2->id,
    ]);
    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData3->id,
    ]);

    $response = $this->getJson("/api/missions/{$mainMission->uuid}");

    $response->assertSuccessful();

    $prereqGroups = $response->json('data.prerequisite_groups');
    expect($prereqGroups)->toHaveCount(1);

    $missions = $prereqGroups[0]['missions'];
    expect($missions)->toHaveCount(2);

    $grouped = collect($missions)->first(fn (array $m) => data_get($m, 'variant_count'));
    expect($grouped)->not->toBeNull()
        ->and($grouped['title'])->toBe('Supply Run')
        ->and($grouped['variant_count'])->toBe(2)
        ->and($grouped['variants'])->toHaveCount(1)
        ->and($grouped['variants'][0]['uuid'])->toBe($linkedMission2->uuid);

    $ungrouped = collect($missions)->first(fn (array $m) => data_get($m, 'title') === 'Different Mission');
    expect($ungrouped)->not->toBeNull()
        ->and($ungrouped)->not->toHaveKey('variant_count')
        ->and($ungrouped)->not->toHaveKey('variants');
});

it('groups unlock missions by title', function (): void {
    $mainMission = Mission::factory()->create();
    $mainData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mainMission)
        ->create();

    $linkedMission1 = Mission::factory()->create();
    $linkedData1 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission1)
        ->create(['title' => 'Escort VIP', 'mission_type' => 'Mercenary']);

    $linkedMission2 = Mission::factory()->create();
    $linkedData2 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission2)
        ->create(['title' => 'Escort VIP', 'mission_type' => 'Mercenary']);

    $group = MissionUnlockGroup::create([
        'mission_data_id' => $mainData->id,
        'group_index' => 0,
        'tag_uuid' => 'aaaaaaaa-0000-4000-8000-000000000001',
        'tag_name' => 'unlock_tag',
    ]);

    MissionUnlockGroupMission::create([
        'unlock_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData1->id,
    ]);
    MissionUnlockGroupMission::create([
        'unlock_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData2->id,
    ]);

    $response = $this->getJson("/api/missions/{$mainMission->uuid}");

    $response->assertSuccessful();

    $unlockGroups = $response->json('data.unlock_groups');
    expect($unlockGroups)->toHaveCount(1);

    $missions = $unlockGroups[0]['missions'];
    expect($missions)->toHaveCount(1);

    expect($missions[0]['title'])->toBe('Escort VIP')
        ->and($missions[0]['variant_count'])->toBe(2)
        ->and($missions[0]['variants'])->toHaveCount(1)
        ->and($missions[0]['variants'][0]['uuid'])->toBe($linkedMission2->uuid);
});

it('does not group missions with null titles', function (): void {
    $mainMission = Mission::factory()->create();
    $mainData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mainMission)
        ->create();

    $linkedMission1 = Mission::factory()->create();
    $linkedData1 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission1)
        ->create(['title' => null, 'mission_type' => 'Delivery']);

    $linkedMission2 = Mission::factory()->create();
    $linkedData2 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission2)
        ->create(['title' => null, 'mission_type' => 'Delivery']);

    $group = MissionPrerequisiteGroup::create([
        'mission_data_id' => $mainData->id,
        'group_index' => 0,
        'required_count' => null,
    ]);

    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData1->id,
    ]);
    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData2->id,
    ]);

    $response = $this->getJson("/api/missions/{$mainMission->uuid}");

    $response->assertSuccessful();

    $missions = $response->json('data.prerequisite_groups.0.missions');
    expect($missions)->toHaveCount(2);

    foreach ($missions as $mission) {
        expect($mission)->not->toHaveKey('variant_count');
    }
});

it('does not group missions with empty string titles', function (): void {
    $mainMission = Mission::factory()->create();
    $mainData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mainMission)
        ->create();

    $linkedMission1 = Mission::factory()->create();
    $linkedData1 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission1)
        ->create(['title' => '', 'mission_type' => 'Delivery']);

    $linkedMission2 = Mission::factory()->create();
    $linkedData2 = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission2)
        ->create(['title' => '', 'mission_type' => 'Delivery']);

    $group = MissionPrerequisiteGroup::create([
        'mission_data_id' => $mainData->id,
        'group_index' => 0,
        'required_count' => null,
    ]);

    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData1->id,
    ]);
    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData2->id,
    ]);

    $response = $this->getJson("/api/missions/{$mainMission->uuid}");

    $response->assertSuccessful();

    $missions = $response->json('data.prerequisite_groups.0.missions');
    expect($missions)->toHaveCount(2);

    foreach ($missions as $mission) {
        expect($mission)->not->toHaveKey('variant_count');
    }
});

it('does not add variant_count when only one mission with a given title', function (): void {
    $mainMission = Mission::factory()->create();
    $mainData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($mainMission)
        ->create();

    $linkedMission = Mission::factory()->create();
    $linkedData = MissionData::factory()
        ->forVersion($this->gameVersion)
        ->forMission($linkedMission)
        ->create(['title' => 'Unique Mission', 'mission_type' => 'Delivery']);

    $group = MissionPrerequisiteGroup::create([
        'mission_data_id' => $mainData->id,
        'group_index' => 0,
        'required_count' => null,
    ]);

    MissionPrerequisiteGroupMission::create([
        'prerequisite_group_id' => $group->id,
        'linked_mission_data_id' => $linkedData->id,
    ]);

    $response = $this->getJson("/api/missions/{$mainMission->uuid}");

    $response->assertSuccessful();

    $missions = $response->json('data.prerequisite_groups.0.missions');
    expect($missions)->toHaveCount(1)
        ->and($missions[0])->not->toHaveKey('variant_count')
        ->and($missions[0])->not->toHaveKey('variants');
});
