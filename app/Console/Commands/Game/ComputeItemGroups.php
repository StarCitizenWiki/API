<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;

class ComputeItemGroups extends Command
{
    protected $signature = 'game:compute-item-groups
                            {--game-version= : Specific game version code (defaults to the current default)}';

    protected $description = 'Compute variant groups and set items for a game version';

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

        ComputeItemVariantGroupsJob::dispatch($gameVersion->id);
        ComputeItemSetItemsJob::dispatch($gameVersion->id);

        $this->info(sprintf('Dispatched compute jobs for version %s.', $gameVersion->code));

        return self::SUCCESS;
    }
}
