<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ComputeBespokeItems as ComputeBespokeItemsJob;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;

class ComputeBespokeItems extends Command
{
    protected $signature = 'game:compute-bespoke-items
                            {--game-version= : Specific game version code (defaults to the current default)}';

    protected $description = 'Compute is_bespoke flags and bespoke_vehicle_tags for items based on vehicle loadout analysis';

    public function handle(): int
    {
        $versionCode = $this->option('game-version');

        $gameVersion = GameVersion::query()
            ->requestedOrDefault(is_string($versionCode) && $versionCode !== '' ? $versionCode : null)
            ->first();

        if ($gameVersion === null) {
            if (is_string($versionCode) && $versionCode !== '') {
                $this->error(sprintf('Game version "%s" does not exist.', $versionCode));
            } else {
                $this->error('No default game version exists.');
            }

            return self::FAILURE;
        }

        ComputeBespokeItemsJob::dispatch($gameVersion->id);

        $this->info(sprintf('Dispatched compute bespoke items job for version %s.', $gameVersion->code));

        return self::SUCCESS;
    }
}
