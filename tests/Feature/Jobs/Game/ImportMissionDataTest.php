<?php

declare(strict_types=1);

use App\Jobs\Game\ImportMissionData;
use App\Models\Game\Blueprint;
use App\Models\Game\BlueprintData;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->version = GameVersion::factory()->create();
});

function createMissionRewardItem(string $uuid, string $name, GameVersion $version): Item
{
    $item = Item::factory()->create([
        'uuid' => $uuid,
        'slug' => str($name)->slug(),
    ]);

    ItemData::factory()->create([
        'item_id' => $item->id,
        'game_version_id' => $version->id,
        'name' => $name,
    ]);

    return $item;
}

function dispatchMissionImport(int $versionId, array $payload, string $file = 'contracts/contract.json'): void
{
    Storage::disk('scunpacked')->put($file, json_encode($payload, JSON_THROW_ON_ERROR));

    (new ImportMissionData($versionId, $file, 'scunpacked'))->handle();
}

it('creates mission data on first import', function (): void {
    $payload = missionPayload();

    dispatchMissionImport($this->version->id, $payload);

    $mission = Mission::query()->where('uuid', $payload['UUID'])->first();
    expect($mission)->not->toBeNull();

    $missionData = MissionData::query()
        ->where('mission_id', $mission->id)
        ->where('game_version_id', $this->version->id)
        ->first();

    expect($missionData)->not->toBeNull()
        ->and($missionData->title)->toBe('Eliminate the Target')
        ->and($missionData->has_combat)->toBeTrue();
});

it('persists the row when source data changes', function (): void {
    $payload = missionPayload();

    dispatchMissionImport($this->version->id, $payload);

    $missionData = MissionData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    // Simulate a genuine data change in the source contract.
    $payload['DisplayTitle'] = 'Eliminate the Updated Target';
    dispatchMissionImport($this->version->id, $payload);

    $missionData->refresh();

    // The guard must let a genuine change through and persist it.
    expect($missionData->title)->toBe('Eliminate the Updated Target');
});

it('computes the mission key from blueprint pool UUIDs', function (): void {
    $payload = missionPayload(blueprints: true);

    dispatchMissionImport($this->version->id, $payload);

    $missionData = MissionData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    // md5 of the single sorted pool UUID.
    $expectedKey = md5('11111111-1111-1111-1111-111111111111');

    expect($missionData->mission_key)->toBe($expectedKey);
});

it('sets a null mission key when there are no blueprints', function (): void {
    $payload = missionPayload(blueprints: false);

    dispatchMissionImport($this->version->id, $payload);

    $missionData = MissionData::query()
        ->where('game_version_id', $this->version->id)
        ->first();

    expect($missionData->mission_key)->toBeNull();
});

describe('reward items', function (): void {
    it('imports grouped RewardItems into separate reward groups', function (): void {
        $weapon = createMissionRewardItem('44444444-4444-4444-4444-444444444444', 'Energy Cell', $this->version);
        $armor = createMissionRewardItem('55555555-5555-5555-5555-555555555555', 'Combat Armor', $this->version);

        $payload = missionPayload();
        $payload['RewardItems'] = [
            [
                'Weight' => 0.5,
                'AwardOnlyToMissionOwner' => true,
                'Items' => [
                    ['UUID' => $weapon->uuid, 'Amount' => 10, 'SendToHome' => true],
                ],
            ],
            [
                'Items' => [
                    ['UUID' => $armor->uuid, 'Amount' => 3, 'SendToHome' => false],
                ],
            ],
        ];

        dispatchMissionImport($this->version->id, $payload);

        $missionData = MissionData::query()
            ->where('game_version_id', $this->version->id)
            ->first();

        $groups = $missionData->rewardGroups()->orderBy('group_index')->get();

        expect($groups)->toHaveCount(2)
            ->and($groups[0]->group_index)->toBe(0)
            ->and($groups[0]->weight)->toBe(0.5)
            ->and($groups[0]->award_only_to_mission_owner)->toBeTrue()
            ->and($groups[1]->group_index)->toBe(1)
            ->and($groups[1]->weight)->toBeNull()
            ->and($groups[1]->award_only_to_mission_owner)->toBeNull();

        $firstItems = $groups[0]->items()->get();
        expect($firstItems)->toHaveCount(1)
            ->and($firstItems[0]->amount)->toBe(10)
            ->and($firstItems[0]->send_to_home)->toBeTrue();

        $secondItems = $groups[1]->items()->get();
        expect($secondItems)->toHaveCount(1)
            ->and($secondItems[0]->amount)->toBe(3)
            ->and($secondItems[0]->send_to_home)->toBeFalse();

        // Re-importing must replace, not duplicate, the reward groups.
        dispatchMissionImport($this->version->id, $payload);
        expect($missionData->rewardGroups()->count())->toBe(2);
    });

    it('skips reward items whose item UUID is unknown', function (): void {
        $known = createMissionRewardItem('77777777-7777-7777-7777-777777777777', 'MedPen', $this->version);

        $payload = missionPayload();
        $payload['RewardItems'] = [
            [
                'Items' => [
                    ['UUID' => $known->uuid, 'Amount' => 1, 'SendToHome' => false],
                    ['UUID' => '00000000-0000-0000-0000-000000000000', 'Amount' => 99, 'SendToHome' => false],
                ],
            ],
        ];

        dispatchMissionImport($this->version->id, $payload);

        $missionData = MissionData::query()
            ->where('game_version_id', $this->version->id)
            ->first();

        $group = $missionData->rewardGroups()->first();
        expect($group)->not->toBeNull()
            ->and($group->items()->count())->toBe(1);
    });
});

describe('cross-version lookup isolation', function (): void {
    beforeEach(function (): void {
        $this->versionB = GameVersion::factory()->create();
    });

    it('resolves reward item data ids for the importing version, not a previously cached one', function (): void {
        $item = Item::factory()->create([
            'uuid' => '99999999-9999-9999-9999-999999999999',
            'slug' => 'versioned-reward-item',
        ]);

        $itemDataA = ItemData::factory()->create([
            'item_id' => $item->id,
            'game_version_id' => $this->version->id,
        ]);

        $itemDataB = ItemData::factory()->create([
            'item_id' => $item->id,
            'game_version_id' => $this->versionB->id,
        ]);

        $payload = missionPayload();
        $payload['RewardItems'] = [
            ['Items' => [['UUID' => $item->uuid, 'Amount' => 1, 'SendToHome' => false]]],
        ];

        // Same worker process: version A primes the lookups, version B must get its own ids.
        dispatchMissionImport($this->version->id, $payload);
        dispatchMissionImport($this->versionB->id, $payload);

        $dataA = MissionData::query()->where('game_version_id', $this->version->id)->firstOrFail();
        $dataB = MissionData::query()->where('game_version_id', $this->versionB->id)->firstOrFail();

        expect($dataA->rewardGroups()->firstOrFail()->items()->firstOrFail()->item_data_id)->toBe($itemDataA->id)
            ->and($dataB->rewardGroups()->firstOrFail()->items()->firstOrFail()->item_data_id)->toBe($itemDataB->id);
    });

    it('links mission blueprint pivots to the importing version\'s blueprint data', function (): void {
        $blueprint = Blueprint::factory()->create([
            'uuid' => '88888888-8888-8888-8888-888888888888',
        ]);

        $blueprintDataA = BlueprintData::factory()->create([
            'blueprint_id' => $blueprint->id,
            'game_version_id' => $this->version->id,
        ]);

        $blueprintDataB = BlueprintData::factory()->create([
            'blueprint_id' => $blueprint->id,
            'game_version_id' => $this->versionB->id,
        ]);

        $item = Item::factory()->create([
            'uuid' => '77777777-7777-7777-7777-777777777777',
            'slug' => 'blueprint-pool-item',
        ]);

        $itemDataA = ItemData::factory()->create([
            'item_id' => $item->id,
            'game_version_id' => $this->version->id,
        ]);

        $itemDataB = ItemData::factory()->create([
            'item_id' => $item->id,
            'game_version_id' => $this->versionB->id,
        ]);

        $payload = missionPayload();
        $payload['Blueprints'] = [
            [
                'PoolUUID' => '11111111-1111-1111-1111-111111111111',
                'Chance' => 1.0,
                'PoolContents' => [
                    ['BlueprintUUID' => $blueprint->uuid, 'ItemUUID' => $item->uuid],
                ],
            ],
        ];

        dispatchMissionImport($this->version->id, $payload);
        dispatchMissionImport($this->versionB->id, $payload);

        $pivotA = MissionData::query()
            ->where('game_version_id', $this->version->id)
            ->firstOrFail()
            ->blueprints()
            ->firstOrFail();

        $pivotB = MissionData::query()
            ->where('game_version_id', $this->versionB->id)
            ->firstOrFail()
            ->blueprints()
            ->firstOrFail();

        expect($pivotA->id)->toBe($blueprintDataA->id)
            ->and($pivotA->pivot->item_data_id)->toBe($itemDataA->id)
            ->and($pivotB->id)->toBe($blueprintDataB->id)
            ->and($pivotB->pivot->item_data_id)->toBe($itemDataB->id);
    });

    it('syncs starmap locations and derived star systems for the importing version', function (): void {
        $location = StarmapLocation::factory()->create([
            'uuid' => '66666666-6666-6666-6666-666666666666',
        ]);

        $locationDataA = StarmapLocationData::factory()->create([
            'starmap_location_id' => $location->id,
            'game_version_id' => $this->version->id,
            'system' => 'Stanton System',
        ]);

        $locationDataB = StarmapLocationData::factory()->create([
            'starmap_location_id' => $location->id,
            'game_version_id' => $this->versionB->id,
            'system' => 'Pyro System',
        ]);

        $payload = missionPayload();
        $payload['LocationPools'] = [
            ['Purpose' => 'target', 'ResolvedLocations' => [['UUID' => $location->uuid]]],
        ];

        dispatchMissionImport($this->version->id, $payload);
        dispatchMissionImport($this->versionB->id, $payload);

        $dataA = MissionData::query()->where('game_version_id', $this->version->id)->firstOrFail();
        $dataB = MissionData::query()->where('game_version_id', $this->versionB->id)->firstOrFail();

        expect($dataA->starmapLocations()->firstOrFail()->id)->toBe($locationDataA->id)
            ->and($dataB->starmapLocations()->firstOrFail()->id)->toBe($locationDataB->id)
            ->and($dataA->star_systems)->toBe(['Stanton'])
            ->and($dataB->star_systems)->toBe(['Pyro']);
    });
});

/**
 * Build a minimal, valid mission contract payload.
 */
function missionPayload(bool $blueprints = false): array
{
    $payload = [
        'UUID' => '22222222-2222-2222-2222-222222222222',
        'DisplayTitle' => 'Eliminate the Target',
        'Title' => 'Eliminate the Target',
        'DisplayDescription' => 'Take out the marked target.',
        'Description' => 'Take out the marked target.',
        'DebugName' => 'Combat_Eliminate',
        'Type' => 'Combat',
        'MissionType' => ['Name' => 'Bounty', 'UUID' => '33333333-3333-3333-3333-333333333333'],
        'MissionGiver' => 'Miles Eckhart',
        'Illegal' => false,
        'Shareable' => false,
        'OnceOnly' => false,
        'AvailableInPrison' => false,
        'NotForRelease' => false,
        'WorkInProgress' => false,
        'CalculatedReward' => true,
        'CombatSummary' => ['Total' => ['Min' => 3, 'Max' => 5]],
        'Combat' => [['Role' => 'enemy']],
    ];

    if ($blueprints) {
        $payload['Blueprints'] = [
            [
                'PoolUUID' => '11111111-1111-1111-1111-111111111111',
                'Chance' => 1.0,
                'PoolContents' => [],
            ],
        ];
    }

    return $payload;
}
