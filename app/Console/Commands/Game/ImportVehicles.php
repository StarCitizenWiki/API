<?php

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\select;

class ImportVehicles extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:import-vehicles {version : Game version code to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch vehicle import jobs for a specific game version';

    /**
     * Execute the console command.
     */
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

        $shipFiles = collect(Storage::disk('scunpacked')->files('ships'))
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'))
            ->reject(static fn (string $path): bool => str_ends_with($path, '-raw.json'))
            ->values();

        if ($shipFiles->isEmpty()) {
            $this->warn('No ship files found in storage/app/api/scunpacked-data/ships.');

            return self::SUCCESS;
        }

        $this->dispatchJobs($shipFiles, $gameVersion->id);

        $this->info(sprintf(
            'Dispatched %d vehicle import jobs for version %s.',
            $shipFiles->count(),
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

    private function dispatchJobs(Collection $shipFiles, int $gameVersionId): void
    {
        $shipFiles->each(static function (string $path) use ($gameVersionId): void {
            ImportVehicleData::dispatch($gameVersionId, $path);
        });
    }
}
