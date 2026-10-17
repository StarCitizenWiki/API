<?php

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportItemData;
use App\Models\Game\GameVersion;
use App\Support\Filters\FilterCache;
use App\Traits\PromptsForGameVersion;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ImportItems extends Command implements PromptsForMissingInput
{
    use PromptsForGameVersion;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:import-items {version : Game version code to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch item import jobs for a specific game version';

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

        $disk = Storage::disk($gameVersion->getStorageDiskName());

        $itemFiles = collect($disk->files('items'))
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'))
            ->values();

        if ($itemFiles->isEmpty()) {
            $this->warn('No item files found in scunpacked-data/items.');

            return self::SUCCESS;
        }

        $this->dispatchJobs($itemFiles, $gameVersion);
        FilterCache::bust(FilterCache::NAMESPACE_ITEMS);

        $this->info(sprintf(
            'Dispatched %d item import jobs for version %s.',
            $itemFiles->count(),
            $gameVersion->code
        ));

        return self::SUCCESS;
    }

    private function dispatchJobs(Collection $itemFiles, GameVersion $gameVersion): void
    {
        $diskName = $gameVersion->getStorageDiskName();
        $versionId = $gameVersion->id;

        $itemFiles->each(static function (string $path) use ($versionId, $diskName): void {
            ImportItemData::dispatch($versionId, $path, null, $diskName);
        });
    }
}
