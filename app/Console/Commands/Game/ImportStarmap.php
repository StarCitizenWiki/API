<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportStarmapData;
use App\Models\Game\GameVersion;
use App\Traits\PromptsForGameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Storage;

class ImportStarmap extends Command implements PromptsForMissingInput
{
    use PromptsForGameVersion;

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

        if (Storage::disk($gameVersion->getStorageDiskName())->missing('starmap.json')) {
            $this->warn('No starmap file found in scunpacked-data/starmap.json.');

            return self::SUCCESS;
        }

        new ImportStarmapData($gameVersion->id, 'starmap.json', $gameVersion->getStorageDiskName())->handle();

        $this->info(sprintf(
            'Imported starmap data for version %s.',
            $gameVersion->code
        ));

        return self::SUCCESS;
    }
}
