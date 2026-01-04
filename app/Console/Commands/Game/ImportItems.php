<?php

namespace App\Console\Commands\Game;

use App\Jobs\Game\ImportItemData;
use App\Models\Game\GameVersion;
use App\Support\Filters\FilterCache;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\select;

class ImportItems extends Command implements PromptsForMissingInput
{
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

        $itemFiles = collect(Storage::disk('scunpacked')->files('items'))
            ->filter(static fn (string $path): bool => str_ends_with($path, '.json'))
            ->values();

        if ($itemFiles->isEmpty()) {
            $this->warn('No item files found in storage/app/api/scunpacked-data/items.');

            return self::SUCCESS;
        }

        $this->dispatchJobs($itemFiles, $gameVersion->id);
        FilterCache::bust(FilterCache::NAMESPACE_ITEMS);

        $this->info(sprintf(
            'Dispatched %d item import jobs for version %s.',
            $itemFiles->count(),
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

    private function dispatchJobs(Collection $itemFiles, int $gameVersionId): void
    {
        $itemFiles->each(static function (string $path) use ($gameVersionId): void {
            ImportItemData::dispatch($gameVersionId, $path);
        });
    }
}
