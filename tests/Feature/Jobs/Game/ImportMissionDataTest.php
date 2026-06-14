<?php

declare(strict_types=1);

use App\Jobs\Game\ImportMissionData;
use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('scunpacked');

    $this->version = GameVersion::factory()->create();
});

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

it('rewrites the row only when data actually changes', function (): void {
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
