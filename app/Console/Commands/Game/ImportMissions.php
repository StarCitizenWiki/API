<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportMissionData;
use App\Jobs\Game\LinkMissionChains;
use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocationData;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\select;

class ImportMissions extends Command implements PromptsForMissingInput
{
    protected $signature = 'game:import-missions {version : Game version code to import}';

    protected $description = 'Import missions for a specific game version using queued jobs with chain linking';

    public function handle(): int
    {
        $versionCode = (string) $this->argument('version');

        $gameVersion = GameVersion::query()
            ->where('code', $versionCode)
            ->first();

        if ($gameVersion === null) {
            $this->error(sprintf('Game version "%s" does not exist. Please create it first.', $versionCode));

            return self::FAILURE;
        }

        $disk = Storage::disk($gameVersion->getStorageDiskName());

        $missionFiles = collect($disk->files('contracts'))
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'))
            ->values();

        if ($missionFiles->isEmpty()) {
            $this->warn('No contract files found in scunpacked-data/contracts.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Dispatching %d mission import jobs for version %s...', $missionFiles->count(), $gameVersion->code));

        $versionId = $gameVersion->id;
        $diskName = $gameVersion->getStorageDiskName();

        $jobs = $missionFiles->map(
            static fn (string $path): ImportMissionData => new ImportMissionData($versionId, $path, $diskName)
        )->all();

        Bus::batch($jobs)
            ->then(static function () use ($versionId): void {
                LinkMissionChains::dispatchSync($versionId);
            })
            ->then(static function () use ($versionId): void {
                StarmapLocationData::where('game_version_id', $versionId)
                    ->withCount('missions')
                    ->chunk(200, static function ($locations): void {
                        foreach ($locations as $location) {
                            $location->forceFill(['mission_count' => $location->missions_count])->save();
                        }
                    });

                StarmapLocationData::where('game_version_id', $versionId)
                    ->where('mission_count', '>', 0)
                    ->whereDoesntHave('missions')
                    ->update(['mission_count' => 0]);
            })
            ->then(static function () use ($versionId): void {
                DB::statement('
                    UPDATE game_blueprint_data bd
                    SET unlocking_missions_count = aggregated.cnt
                    FROM (
                        SELECT blueprint_data_id, COUNT(*) AS cnt
                        FROM game_mission_data_blueprint mdb
                        INNER JOIN game_mission_data md ON mdb.mission_data_id = md.id
                        WHERE md.game_version_id = ?
                        GROUP BY blueprint_data_id
                    ) aggregated
                    WHERE bd.id = aggregated.blueprint_data_id
                      AND bd.game_version_id = ?
                ', [$versionId, $versionId]);

                DB::statement('
                    UPDATE game_blueprint_data
                    SET unlocking_missions_count = 0
                    WHERE game_version_id = ?
                      AND unlocking_missions_count != 0
                      AND id NOT IN (
                        SELECT DISTINCT blueprint_data_id FROM game_mission_data_blueprint mdb
                        INNER JOIN game_mission_data md ON mdb.mission_data_id = md.id
                        WHERE md.game_version_id = ?
                      )
                ', [$versionId, $versionId]);
            })
            ->dispatch();

        $this->info(sprintf(
            'Dispatched batch with %d jobs for version %s.',
            $missionFiles->count(),
            $gameVersion->code
        ));

        return self::SUCCESS;
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'version' => function (): string {
                $options = GameVersion::query()
                    ->orderByDesc('released_at')
                    ->orderBy('code')
                    ->pluck('code', 'code')
                    ->toArray();

                if ($options === []) {
                    $this->error('No game versions exist. Please create one before importing.');

                    return '';
                }

                return select(
                    label: 'Select game version to import',
                    options: $options
                );
            },
        ];
    }
}
