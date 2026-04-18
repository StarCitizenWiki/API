<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportMissionData;
use App\Jobs\Game\LinkMissionChains;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Bus;
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

        $missionFiles = collect(Storage::disk('scunpacked')->files('contracts'))
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'))
            ->values();

        if ($missionFiles->isEmpty()) {
            $this->warn('No contract files found in storage/app/api/scunpacked-data/contracts.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Dispatching %d mission import jobs for version %s...', $missionFiles->count(), $gameVersion->code));

        $versionId = $gameVersion->id;

        $jobs = $missionFiles->map(
            static fn (string $path): ImportMissionData => new ImportMissionData($versionId, $path)
        )->all();

        Bus::batch($jobs)
            ->then(static function () use ($versionId): void {
                LinkMissionChains::dispatchSync($versionId);
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
