<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ComputeItemBaseIds as ComputeItemBaseIdsJob;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;

class ComputeItemBaseIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:compute-item-base-ids
                            {--game-version= : Specific game version code (defaults to the current default)}
                            {--dry-run : Preview changes without updating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute base_id values for item variants';

    /**
     * Execute the console command.
     */
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

        $dryRun = (bool) $this->option('dry-run');

        ComputeItemBaseIdsJob::dispatch($gameVersion->id, $dryRun);

        $message = $dryRun
            ? 'Dispatched dry-run item base id compute job for version %s.'
            : 'Dispatched item base id compute job for version %s.';

        $this->info(sprintf($message, $gameVersion->code));

        return self::SUCCESS;
    }
}
