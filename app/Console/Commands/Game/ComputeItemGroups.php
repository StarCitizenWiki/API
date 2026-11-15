<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGradeLinks as ComputeItemVariantGradeLinksJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Traits\PromptsForGameVersion;
use Illuminate\Console\Command;

class ComputeItemGroups extends Command
{
    use PromptsForGameVersion;

    protected $signature = 'game:compute-item-groups
                            {--game-version= : Specific game version code (defaults to the current default)}';

    protected $description = 'Compute variant groups and set items for a game version';

    public function handle(): int
    {
        $gameVersion = $this->resolveGameVersionFromOptionOrArgument();

        if ($gameVersion === null) {
            return self::FAILURE;
        }

        ComputeItemVariantGroupsJob::dispatch($gameVersion->id);
        ComputeItemSetItemsJob::dispatch($gameVersion->id);
        ComputeItemVariantGradeLinksJob::dispatch($gameVersion->id);

        $this->info(sprintf('Dispatched compute jobs for version %s.', $gameVersion->code));

        return self::SUCCESS;
    }
}
