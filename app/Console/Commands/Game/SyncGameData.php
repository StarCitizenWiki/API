<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\ComputeItemBaseIds as ComputeItemBaseIdsJob;
use App\Jobs\Game\ImportItemData;
use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use function Laravel\Prompts\select;

class SyncGameData extends Command
{
    private const BATCH_SIZE = 1000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:sync-data
                            {--game-version= : Specific game version code}
                            {--skip-items : Skip importing item data}
                            {--skip-vehicles : Skip importing vehicle data}
                            {--skip-compute-item-base-ids : Skip computing item base ids}
                            {--skip-backfill-shipmatrix-ids : Skip backfilling shipmatrix ids}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync game manufacturers, entity tags, and optional data imports.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $skipItems = (bool) $this->option('skip-items');
        $skipVehicles = (bool) $this->option('skip-vehicles');
        $skipComputeBaseIds = (bool) $this->option('skip-compute-item-base-ids');
        $skipBackfillShipmatrixIds = (bool) $this->option('skip-backfill-shipmatrix-ids');

        $gameVersion = $this->resolveGameVersion($skipItems, $skipVehicles);

        if ($gameVersion === null && (! $skipItems || ! $skipVehicles)) {
            return self::FAILURE;
        }

        if (Artisan::call('game:import-manufacturers') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (Artisan::call('game:import-tags') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (! $skipItems && $gameVersion !== null) {
            $this->dispatchItemImports($gameVersion, $skipComputeBaseIds);
        }

        if (! $skipVehicles && $gameVersion !== null) {
            $this->dispatchVehicleImports($gameVersion, $skipBackfillShipmatrixIds);
        }

        return self::SUCCESS;
    }

    private function resolveGameVersion(bool $skipItems, bool $skipVehicles): ?GameVersion
    {
        if ($skipItems && $skipVehicles) {
            return null;
        }

        $versionCode = $this->option('game-version');

        if (! is_string($versionCode) || $versionCode === '') {
            $options = GameVersion::query()
                ->orderByDesc('released_at')
                ->orderBy('code')
                ->pluck('code', 'code')
                ->toArray();

            if ($options === []) {
                $this->error('No game versions exist. Please create one before syncing.');

                return null;
            }

            $versionCode = select(
                label: 'Select game version to import',
                options: $options
            );
        }

        $gameVersion = GameVersion::query()
            ->where('code', $versionCode)
            ->first();

        if ($gameVersion === null) {
            $this->error(sprintf('Game version "%s" does not exist. Please create it first.', $versionCode));
        }

        return $gameVersion;
    }

    private function dispatchItemImports(GameVersion $gameVersion, bool $skipComputeBaseIds): void
    {
        $itemFiles = collect(Storage::disk('scunpacked')->files('items'))
            ->filter(static fn (string $path): bool => Str::endsWith($path, '.json'))
            ->values();

        if ($itemFiles->isEmpty()) {
            $this->warn('No item files found in storage/app/api/scunpacked-data/items.');

            return;
        }

        $jobs = $itemFiles->map(static function (string $path) use ($gameVersion): ImportItemData {
            return new ImportItemData($gameVersion->id, $path);
        });

        $this->dispatchChunkedBatch($jobs, $skipComputeBaseIds ? null : function () use ($gameVersion): void {
            ComputeItemBaseIdsJob::dispatch($gameVersion->id, false);
        });
    }

    private function dispatchVehicleImports(GameVersion $gameVersion, bool $skipBackfillShipmatrixIds): void
    {
        $shipFiles = collect(Storage::disk('scunpacked')->files('ships'))
            ->filter(static fn (string $path): bool => Str::endsWith($path, '.json'))
            ->reject(static fn (string $path): bool => Str::endsWith($path, '-raw.json'))
            ->values();

        if ($shipFiles->isEmpty()) {
            $this->warn('No ship files found in storage/app/api/scunpacked-data/ships.');

            return;
        }

        $jobs = $shipFiles->map(static function (string $path) use ($gameVersion): ImportVehicleData {
            return new ImportVehicleData($gameVersion->id, $path);
        });

        $this->dispatchChunkedBatch($jobs, $skipBackfillShipmatrixIds ? null : function () use ($gameVersion): void {
            Artisan::call('game:backfill-shipmatrix-ids', [
                '--game-version' => $gameVersion->code,
            ]);
        });
    }

    /**
     * @param  \Illuminate\Support\Collection<int, mixed>  $jobs
     */
    private function dispatchChunkedBatch(Collection $jobs, ?Closure $then): void
    {
        $jobChunks = $jobs->chunk(self::BATCH_SIZE)->values();
        $firstChunk = $jobChunks->shift();

        if ($firstChunk === null) {
            return;
        }

        $pendingBatch = Bus::batch($firstChunk->values());

        if ($then !== null) {
            $pendingBatch->then($then);
        }

        $batch = $pendingBatch->dispatch();

        $jobChunks->each(static function (Collection $chunk) use ($batch): void {
            $batch->add($chunk->values());
        });
    }
}
