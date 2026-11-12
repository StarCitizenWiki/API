<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\AddBatchJobs;
use App\Jobs\Game\ComputeBespokeItems as ComputeBespokeItemsJob;
use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGradeLinks as ComputeItemVariantGradeLinksJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Jobs\Game\ImportItemData;
use App\Jobs\Game\ImportStarmapData;
use App\Jobs\Game\ImportVehicleData;
use App\Jobs\Game\SyncVehicleInstalledItems;
use App\Models\Game\GameVersion;
use App\Models\Game\ItemData;
use App\Models\Game\Manufacturer;
use Closure;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

use function Laravel\Prompts\select;

class SyncGameData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:sync-data {--game-version= : Specific game version code}';

    /**
     * The console command name aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['game:sync'];

    private const int BATCH_SIZE = 1000;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync game labels, manufacturers, entity tags, resource types, and game data imports, then import UEX prices.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $gameVersion = $this->resolveGameVersion();

        if ($gameVersion === null) {
            return self::FAILURE;
        }

        $diskName = $gameVersion->getStorageDiskName();

        if (Artisan::call('game:import-labels') !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (Artisan::call('game:import-manufacturers', ['--disk' => $diskName]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        Manufacturer::query()->updateOrCreate([
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ], [
            'name' => 'Unknown',
            'code' => 'UNKN',
        ]);

        if (Artisan::call('game:import-tags', ['--disk' => $diskName]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (Artisan::call('game:import-factions', ['--disk' => $diskName]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->dispatchStarmapImport($gameVersion);

        if (Artisan::call('game:import-resource-data', [
            'version' => $gameVersion->code,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (Artisan::call('game:import-blueprints', [
            'version' => $gameVersion->code,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        // Items and vehicles are imported as one batch so they run in parallel,
        // and finalization only runs once both are fully committed.
        $jobs = $this->collectItemJobs($gameVersion)->concat($this->collectVehicleJobs($gameVersion));

        if ($jobs->isEmpty()) {
            return self::finalizeSync($gameVersion);
        }

        $this->dispatchChunkedBatch($jobs, static function (Batch $batch) use ($gameVersion): void {
            if (! $batch->cancelled()) {
                self::finalizeSync($gameVersion);
            }
        });

        return self::SUCCESS;
    }

    /**
     * Run every step that depends on items and vehicles already being imported.
     */
    private static function finalizeSync(GameVersion $gameVersion): int
    {
        SyncVehicleInstalledItems::dispatch($gameVersion->id);
        ComputeItemVariantGroupsJob::dispatch($gameVersion->id);
        ComputeItemSetItemsJob::dispatch($gameVersion->id);
        ComputeItemVariantGradeLinksJob::dispatch($gameVersion->id);

        $status = self::callFinalizeCommand('game:backfill-shipmatrix-ids', ['--game-version' => $gameVersion->code], $gameVersion);

        ComputeBespokeItemsJob::dispatch($gameVersion->id);

        self::syncItemCraftability($gameVersion->id);

        $missionsStatus = self::callFinalizeCommand('game:import-missions', ['version' => $gameVersion->code], $gameVersion);
        $pricesStatus = self::callFinalizeCommand('game:import-item-prices', [], $gameVersion);

        return $status === self::SUCCESS && $missionsStatus === self::SUCCESS && $pricesStatus === self::SUCCESS
            ? self::SUCCESS : self::FAILURE;
    }

    private static function callFinalizeCommand(string $command, array $arguments, GameVersion $gameVersion): int
    {
        $status = Artisan::call($command, $arguments);

        if ($status !== self::SUCCESS) {
            Log::error('Game sync finalize command failed.', [
                'command' => $command,
                'exit_code' => $status,
                'game_version' => $gameVersion->code,
            ]);
        }

        return $status;
    }

    /**
     * Recompute {@see ItemData::$is_craftable} for the given game version,
     * writing only rows whose value actually changes.
     */
    private static function syncItemCraftability(int $gameVersionId): void
    {
        // Flip to true: items that have a blueprint but are currently flagged as not craftable.
        ItemData::where('game_version_id', $gameVersionId)
            ->whereHas('craftingBlueprints')
            ->where('is_craftable', false)
            ->chunkById(5000, fn ($items) => ItemData::whereIn('id', $items->pluck('id'))
                ->update(['is_craftable' => true]));

        // Flip to false: items without a blueprint but currently flagged as craftable.
        ItemData::where('game_version_id', $gameVersionId)
            ->whereDoesntHave('craftingBlueprints')
            ->where('is_craftable', true)
            ->chunkById(5000, fn ($items) => ItemData::whereIn('id', $items->pluck('id'))
                ->update(['is_craftable' => false]));
    }

    private function resolveGameVersion(): ?GameVersion
    {
        $versionCode = $this->option('game-version');

        if (! is_string($versionCode) || $versionCode === '') {
            $options = GameVersion::query()
                ->orderByRaw('released_at DESC NULLS FIRST')
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

    /**
     * @return Collection<int, ImportItemData>
     */
    private function collectItemJobs(GameVersion $gameVersion): Collection
    {
        $itemFiles = collect(Storage::disk($gameVersion->getStorageDiskName())->files('items'))
            ->filter(static fn (string $path): bool => Str::endsWith($path, '.json'))
            ->values();

        if ($itemFiles->isEmpty()) {
            $this->warn('No item files found in scunpacked-data/items.');

            return collect();
        }

        $diskName = $gameVersion->getStorageDiskName();

        return $itemFiles->map(static fn (string $path): ImportItemData => new ImportItemData($gameVersion->id, $path, null, $diskName));
    }

    /**
     * @return Collection<int, ImportVehicleData>
     */
    private function collectVehicleJobs(GameVersion $gameVersion): Collection
    {
        $shipFiles = collect(Storage::disk($gameVersion->getStorageDiskName())->files('ships'))
            ->filter(static fn (string $path): bool => Str::endsWith($path, '.json'))
            ->reject(static fn (string $path): bool => Str::endsWith($path, '-raw.json'))
            ->values();

        if ($shipFiles->isEmpty()) {
            $this->warn('No ship files found in scunpacked-data/ships.');

            return collect();
        }

        $diskName = $gameVersion->getStorageDiskName();

        return $shipFiles->map(static fn (string $path): ImportVehicleData => new ImportVehicleData($gameVersion->id, $path, $diskName));
    }

    private function dispatchStarmapImport(GameVersion $gameVersion): void
    {
        $diskName = $gameVersion->getStorageDiskName();

        if (Storage::disk($diskName)->missing('starmap.json')) {
            $this->warn('No starmap file found in scunpacked-data/starmap.json.');

            return;
        }

        new ImportStarmapData($gameVersion->id, 'starmap.json', $diskName)->handle();
    }

    /**
     * @param  Collection<int, mixed>  $jobs
     */
    private function dispatchChunkedBatch(Collection $jobs, ?Closure $then): void
    {
        if ($jobs->isEmpty()) {
            return;
        }

        $jobChunks = $jobs->chunk(self::BATCH_SIZE)->values();
        $loaderJobs = $jobChunks->map(static fn (Collection $chunk): AddBatchJobs => new AddBatchJobs($chunk));
        $pendingBatch = Bus::batch($loaderJobs);

        if ($then !== null) {
            $pendingBatch->then($then);
        }

        $pendingBatch->catch(static function (Batch $batch, Throwable $exception): void {
            Log::error('Failed game sync batch; finalization skipped.', [
                'batch_id' => $batch->id,
                'exception' => $exception,
            ]);
        });

        $pendingBatch->dispatch();
    }
}
