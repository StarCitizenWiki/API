<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportStarmapData;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\select;

class ImportStarmap extends Command implements PromptsForMissingInput
{
    protected $signature = 'game:import-starmap {version : Game version code to import}';

    protected $description = 'Import starmap data for a specific game version';

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

        if (Storage::disk('scunpacked')->missing('starmap.json')) {
            $this->warn('No starmap file found in storage/app/api/scunpacked-data/starmap.json.');

            return self::SUCCESS;
        }

        (new ImportStarmapData($gameVersion->id))->handle();

        $this->info(sprintf(
            'Imported starmap data for version %s.',
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
