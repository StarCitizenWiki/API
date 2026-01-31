<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportItemPrices as ImportItemPricesJob;
use App\Models\Game\GameVersion;
use Illuminate\Console\Command;

class ImportItemPrices extends Command
{
    protected $signature = 'game:import-item-prices';

    protected $description = 'Import item prices from UEX Corp API for the default game version';

    public function handle(): int
    {
        $gameVersion = GameVersion::query()
            ->where('is_default', true)
            ->first();

        if ($gameVersion === null) {
            $this->error('No default game version found.');

            return self::FAILURE;
        }

        $this->info("Dispatching item price import for version {$gameVersion->code}...");

        ImportItemPricesJob::dispatch($gameVersion->id);

        $this->info('Job dispatched successfully.');

        return self::SUCCESS;
    }
}
