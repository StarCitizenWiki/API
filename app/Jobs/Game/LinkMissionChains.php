<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\Mission\MissionData;
use App\Models\Game\Mission\MissionPrerequisiteGroup;
use App\Models\Game\Mission\MissionPrerequisiteGroupMission;
use App\Models\Game\Mission\MissionPrerequisiteGroupTag;
use App\Models\Game\Mission\MissionUnlockGroup;
use App\Models\Game\Mission\MissionUnlockGroupMission;
use App\Support\Filters\FilterCache;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class LinkMissionChains implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $gameVersionId,
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        $uuidToDataId = MissionData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->join('game_missions', 'game_mission_data.mission_id', '=', 'game_missions.id')
            ->pluck('game_mission_data.id', 'game_missions.uuid')
            ->all();

        $prerequisiteGroupRows = [];
        $prerequisiteGroupMissionRows = [];
        $prerequisiteGroupTagRows = [];
        $unlockGroupRows = [];
        $unlockGroupMissionRows = [];

        MissionData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->chunkById(500, function (Collection $missionDataRecords) use ($uuidToDataId, &$prerequisiteGroupRows, &$prerequisiteGroupMissionRows, &$prerequisiteGroupTagRows, &$unlockGroupRows, &$unlockGroupMissionRows): void {
                foreach ($missionDataRecords as $missionData) {
                    $data = $missionData->data;

                    if ($data === null) {
                        continue;
                    }

                    $this->extractPrerequisites($missionData, $data, $uuidToDataId, $prerequisiteGroupRows, $prerequisiteGroupMissionRows, $prerequisiteGroupTagRows);
                    $this->extractUnlocks($missionData, $data, $uuidToDataId, $unlockGroupRows, $unlockGroupMissionRows);
                }
            });

        $missionDataIds = MissionData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->pluck('id')
            ->all();

        if ($missionDataIds !== []) {
            $prerequisiteGroupIds = MissionPrerequisiteGroup::query()
                ->whereIn('mission_data_id', $missionDataIds)
                ->pluck('id')
                ->all();

            if ($prerequisiteGroupIds !== []) {
                MissionPrerequisiteGroupTag::query()->whereIn('prerequisite_group_id', $prerequisiteGroupIds)->delete();
                MissionPrerequisiteGroupMission::query()->whereIn('prerequisite_group_id', $prerequisiteGroupIds)->delete();
            }

            MissionPrerequisiteGroup::query()->whereIn('mission_data_id', $missionDataIds)->delete();

            $unlockGroupIds = MissionUnlockGroup::query()
                ->whereIn('mission_data_id', $missionDataIds)
                ->pluck('id')
                ->all();

            if ($unlockGroupIds !== []) {
                MissionUnlockGroupMission::query()->whereIn('unlock_group_id', $unlockGroupIds)->delete();
            }

            MissionUnlockGroup::query()->whereIn('mission_data_id', $missionDataIds)->delete();
        }

        foreach (array_chunk($prerequisiteGroupRows, 500) as $chunk) {
            MissionPrerequisiteGroup::query()->insert($chunk);
        }

        $createdPrerequisiteGroups = MissionPrerequisiteGroup::query()
            ->whereIn('mission_data_id', $missionDataIds)
            ->get(['id', 'mission_data_id', 'group_index']);

        $prereqGroupLookup = [];
        foreach ($createdPrerequisiteGroups as $group) {
            $prereqGroupLookup[$group->mission_data_id.':'.$group->group_index] = $group->id;
        }

        $resolvedPrereqMissionRows = [];
        foreach ($prerequisiteGroupMissionRows as $row) {
            $key = $row['mission_data_id'].':'.$row['group_index'];
            $groupId = $prereqGroupLookup[$key] ?? null;

            if ($groupId !== null) {
                $resolvedPrereqMissionRows[] = [
                    'prerequisite_group_id' => $groupId,
                    'linked_mission_data_id' => $row['linked_mission_data_id'],
                ];
            }
        }

        $resolvedPrereqTagRows = [];
        foreach ($prerequisiteGroupTagRows as $row) {
            $key = $row['mission_data_id'].':'.$row['group_index'];
            $groupId = $prereqGroupLookup[$key] ?? null;

            if ($groupId !== null) {
                $resolvedPrereqTagRows[] = [
                    'prerequisite_group_id' => $groupId,
                    'type' => $row['type'],
                    'tag_uuid' => $row['tag_uuid'],
                    'tag_name' => $row['tag_name'],
                ];
            }
        }

        foreach (array_chunk($resolvedPrereqMissionRows, 500) as $chunk) {
            MissionPrerequisiteGroupMission::query()->insert($chunk);
        }

        foreach (array_chunk($resolvedPrereqTagRows, 500) as $chunk) {
            MissionPrerequisiteGroupTag::query()->insert($chunk);
        }

        foreach (array_chunk($unlockGroupRows, 500) as $chunk) {
            MissionUnlockGroup::query()->insert($chunk);
        }

        $createdUnlockGroups = MissionUnlockGroup::query()
            ->whereIn('mission_data_id', $missionDataIds)
            ->get(['id', 'mission_data_id', 'group_index']);

        $unlockGroupLookup = [];
        foreach ($createdUnlockGroups as $group) {
            $unlockGroupLookup[$group->mission_data_id.':'.$group->group_index] = $group->id;
        }

        $resolvedUnlockMissionRows = [];
        foreach ($unlockGroupMissionRows as $row) {
            $key = $row['mission_data_id'].':'.$row['group_index'];
            $groupId = $unlockGroupLookup[$key] ?? null;

            if ($groupId !== null) {
                $resolvedUnlockMissionRows[] = [
                    'unlock_group_id' => $groupId,
                    'linked_mission_data_id' => $row['linked_mission_data_id'],
                ];
            }
        }

        foreach (array_chunk($resolvedUnlockMissionRows, 500) as $chunk) {
            MissionUnlockGroupMission::query()->insert($chunk);
        }

        FilterCache::bust(FilterCache::NAMESPACE_MISSIONS);
    }

    private function extractPrerequisites(
        MissionData $missionData,
        mixed $data,
        array $uuidToDataId,
        array &$groupRows,
        array &$missionRows,
        array &$tagRows,
    ): void {
        $prerequisites = $data['Prerequisites'] ?? [];

        if (! is_array($prerequisites)) {
            return;
        }

        foreach ($prerequisites as $groupIndex => $group) {
            if (! is_array($group)) {
                continue;
            }

            $hasMissions = false;

            foreach ($group['RequiredMissions'] ?? [] as $requiredMission) {
                if (! is_array($requiredMission)) {
                    continue;
                }

                $linkedUuid = $this->trimOrNull($requiredMission['UUID'] ?? null);

                if ($linkedUuid === null) {
                    continue;
                }

                $linkedDataId = $uuidToDataId[$linkedUuid] ?? null;

                if ($linkedDataId === null) {
                    continue;
                }

                $missionRows[] = [
                    'mission_data_id' => $missionData->id,
                    'group_index' => (int) $groupIndex,
                    'linked_mission_data_id' => $linkedDataId,
                ];

                $hasMissions = true;
            }

            if (! $hasMissions) {
                continue;
            }

            $requiredCount = $group['RequiredCount'] ?? null;

            $groupRows[] = [
                'mission_data_id' => $missionData->id,
                'group_index' => (int) $groupIndex,
                'required_count' => is_numeric($requiredCount) ? (int) $requiredCount : null,
            ];

            foreach ($group['RequiredTags'] ?? [] as $tag) {
                if (! is_array($tag)) {
                    continue;
                }

                $tagRows[] = [
                    'mission_data_id' => $missionData->id,
                    'group_index' => (int) $groupIndex,
                    'type' => 'required',
                    'tag_uuid' => $this->trimOrNull($tag['UUID'] ?? null),
                    'tag_name' => $this->trimOrNull($tag['Name'] ?? null),
                ];
            }

            foreach ($group['ExcludedTags'] ?? [] as $tag) {
                if (! is_array($tag)) {
                    continue;
                }

                $tagRows[] = [
                    'mission_data_id' => $missionData->id,
                    'group_index' => (int) $groupIndex,
                    'type' => 'excluded',
                    'tag_uuid' => $this->trimOrNull($tag['UUID'] ?? null),
                    'tag_name' => $this->trimOrNull($tag['Name'] ?? null),
                ];
            }
        }
    }

    private function extractUnlocks(
        MissionData $missionData,
        mixed $data,
        array $uuidToDataId,
        array &$groupRows,
        array &$missionRows,
    ): void {
        $completionTags = $data['CompletionTags'] ?? [];

        if (! is_array($completionTags)) {
            return;
        }

        foreach ($completionTags as $tagIndex => $tag) {
            if (! is_array($tag)) {
                continue;
            }

            $tagUuid = $this->trimOrNull($tag['UUID'] ?? null);
            $tagName = $this->trimOrNull($tag['Name'] ?? null);
            $hasMissions = false;

            foreach ($tag['UnlocksMissions'] ?? [] as $unlockedMission) {
                if (! is_array($unlockedMission)) {
                    continue;
                }

                $linkedUuid = $this->trimOrNull($unlockedMission['UUID'] ?? null);

                if ($linkedUuid === null) {
                    continue;
                }

                $linkedDataId = $uuidToDataId[$linkedUuid] ?? null;

                if ($linkedDataId === null) {
                    continue;
                }

                $missionRows[] = [
                    'mission_data_id' => $missionData->id,
                    'group_index' => (int) $tagIndex,
                    'linked_mission_data_id' => $linkedDataId,
                ];

                $hasMissions = true;
            }

            if (! $hasMissions) {
                continue;
            }

            $groupRows[] = [
                'mission_data_id' => $missionData->id,
                'group_index' => (int) $tagIndex,
                'tag_uuid' => $tagUuid,
                'tag_name' => $tagName,
            ];
        }
    }

    private function trimOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
