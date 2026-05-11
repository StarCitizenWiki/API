<?php

declare(strict_types=1);

namespace App\Console\Commands\Game\Resources;

use App\Models\Game\GameVersion;
use App\Models\Game\StarmapLocationData;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\select;

class ImportResourceData extends Command implements PromptsForMissingInput
{
    protected $signature = 'game:import-resource-data {version : Game version code to import}';

    protected $description = 'Import commodities, resources, and resource locations for a specific game version';

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

        $diskName = $gameVersion->getStorageDiskName();

        if (Artisan::call('game:import-commodities', ['--disk' => $diskName]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (Artisan::call('game:import-resources', [
            'version' => $gameVersion->code,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $hasStarmapData = StarmapLocationData::query()
            ->where('game_version_id', $gameVersion->id)
            ->exists();

        if (! $hasStarmapData) {
            $this->error('No starmap data found for this version. Please import starmap data first.');

            return self::FAILURE;
        }

        if (Artisan::call('game:import-resource-locations', [
            'version' => $gameVersion->code,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

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
