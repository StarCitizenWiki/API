<?php

declare(strict_types=1);

namespace App\Jobs\Game;

use App\Models\Game\Mission\MissionChain;
use App\Models\Game\Mission\MissionData;
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

        $chainRows = [];

        MissionData::query()
            ->where('game_version_id', $this->gameVersionId)
            ->chunkById(500, function (Collection $missionDataRecords) use ($uuidToDataId, &$chainRows): void {
                foreach ($missionDataRecords as $missionData) {
                    $data = $missionData->data;

                    if ($data === null) {
                        continue;
                    }

                    $this->extractUnlocks($missionData, $data, $uuidToDataId, $chainRows);
                    $this->extractPrerequisites($missionData, $data, $uuidToDataId, $chainRows);
                }
            });

        $missionDataIds = array_unique(array_column($chainRows, 'mission_data_id'));

        if ($missionDataIds !== []) {
            MissionChain::query()->whereIn('mission_data_id', $missionDataIds)->delete();
        }

        if ($chainRows !== []) {
            foreach (array_chunk($chainRows, 500) as $chunk) {
                MissionChain::query()->insert($chunk);
            }
        }

        FilterCache::bust(FilterCache::NAMESPACE_MISSIONS);
    }

    private function extractUnlocks(MissionData $missionData, mixed $data, array $uuidToDataId, array &$chainRows): void
    {
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

                $chainRows[] = [
                    'mission_data_id' => $missionData->id,
                    'linked_mission_data_id' => $linkedDataId,
                    'chain_type' => 'unlock',
                    'group_index' => (int) $tagIndex,
                    'tag_uuid' => $tagUuid,
                    'tag_name' => $tagName,
                ];
            }
        }
    }

    private function extractPrerequisites(MissionData $missionData, mixed $data, array $uuidToDataId, array &$chainRows): void
    {
        $prerequisites = $data['Prerequisites'] ?? [];

        if (! is_array($prerequisites)) {
            return;
        }

        foreach ($prerequisites as $groupIndex => $group) {
            if (! is_array($group)) {
                continue;
            }

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

                $chainRows[] = [
                    'mission_data_id' => $missionData->id,
                    'linked_mission_data_id' => $linkedDataId,
                    'chain_type' => 'prerequisite',
                    'group_index' => (int) $groupIndex,
                    'tag_uuid' => null,
                    'tag_name' => null,
                ];
            }
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
