<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Actions\Game\SyncItemCraftability;
use App\Jobs\Game\AddBatchJobs;
use App\Jobs\Game\ComputeBespokeItems as ComputeBespokeItemsJob;
use App\Jobs\Game\ComputeItemSetItems as ComputeItemSetItemsJob;
use App\Jobs\Game\ComputeItemVariantGroups as ComputeItemVariantGroupsJob;
use App\Jobs\Game\ImportItemData;
use App\Jobs\Game\ImportStarmapData;
use App\Jobs\Game\ImportVehicleData;
use App\Models\Game\GameVersion;
use App\Models\Game\Manufacturer;
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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:sync-data
                            {--game-version= : Specific game version code}
                            {--skip-items : Skip importing item data}
                            {--skip-vehicles : Skip importing vehicle data}
                            {--skip-starmap : Skip importing starmap data}
                            {--skip-resources : Skip importing resource data}
                            {--skip-compute-item-groups : Skip computing item variant groups and set items}
                            {--skip-compute-bespoke : Skip computing bespoke item flags}
                            {--skip-backfill-shipmatrix-ids : Skip backfilling shipmatrix ids}
                            {--skip-factions : Skip importing faction data}
                            {--skip-missions : Skip importing mission data}';

    /**
     * The console command name aliases.
     *
     * @var array<int, string>
     */
    protected $aliases = ['game:sync'];

    private const BATCH_SIZE = 1000;

    private const LOADER_BATCH_SIZE = 100;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync game labels, manufacturers, entity tags, resource types, and optional game data imports.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $skipItems = (bool) $this->option('skip-items');
        $skipVehicles = (bool) $this->option('skip-vehicles');
        $skipStarmap = (bool) $this->option('skip-starmap');
        $skipResources = (bool) $this->option('skip-resources');
        $skipComputeItemGroups = (bool) $this->option('skip-compute-item-groups');
        $skipComputeBespoke = (bool) $this->option('skip-compute-bespoke');
        $skipBackfillShipmatrixIds = (bool) $this->option('skip-backfill-shipmatrix-ids');
        $skipFactions = (bool) $this->option('skip-factions');
        $skipMissions = (bool) $this->option('skip-missions');
        $shouldImportVersionedData = $this->shouldImportVersionedData($skipItems, $skipVehicles, $skipStarmap, $skipResources, $skipMissions);

        $gameVersion = $this->resolveGameVersion($shouldImportVersionedData);

        if ($gameVersion === null && $shouldImportVersionedData) {
            return self::FAILURE;
        }

        $diskName = $gameVersion?->getStorageDiskName() ?? 'scunpacked';

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

        if (! $skipFactions && Artisan::call('game:import-factions', [
            '--disk' => $diskName,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (! $skipStarmap) {
            $this->dispatchStarmapImport($gameVersion);
        }

        if (! $skipResources && Artisan::call('game:import-resource-data', [
            'version' => $gameVersion->code,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (! $skipItems) {
            $this->dispatchItemImports($gameVersion, $skipComputeItemGroups, ! $skipMissions);
        }

        if (! $skipVehicles) {
            $this->dispatchVehicleImports($gameVersion, $skipBackfillShipmatrixIds, $skipComputeBespoke);
        }

        if ($gameVersion !== null && Artisan::call('game:import-blueprints', [
            'version' => $gameVersion->code,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        if (! $skipItems) {
            app(SyncItemCraftability::class)->execute($gameVersion->id);
        }

        if (! $skipMissions && $skipItems && Artisan::call('game:import-missions', [
            'version' => $gameVersion->code,
        ]) !== self::SUCCESS) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function shouldImportVersionedData(bool $skipItems, bool $skipVehicles, bool $skipStarmap, bool $skipResources, bool $skipMissions): bool
    {
        if (! $skipItems || ! $skipVehicles || ! $skipStarmap || ! $skipResources || ! $skipMissions) {
            return true;
        }

        $versionCode = $this->option('game-version');

        return is_string($versionCode) && $versionCode !== '';
    }

    private function resolveGameVersion(bool $shouldImportVersionedData): ?GameVersion
    {
        if (! $shouldImportVersionedData) {
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

    private function dispatchItemImports(GameVersion $gameVersion, bool $skipComputeItemGroups, bool $dispatchMissionsAfter = false): void
    {
        $itemFiles = collect(Storage::disk($gameVersion->getStorageDiskName())->files('items'))
            ->filter(static fn (string $path): bool => Str::endsWith($path, '.json'))
            ->values();

        if ($itemFiles->isEmpty()) {
            $this->warn('No item files found in scunpacked-data/items.');

            return;
        }

        $diskName = $gameVersion->getStorageDiskName();

        $jobs = $itemFiles->map(static function (string $path) use ($gameVersion, $diskName): ImportItemData {
            return new ImportItemData($gameVersion->id, $path, null, $diskName);
        });

        $hasPostBatchWork = ! $skipComputeItemGroups || $dispatchMissionsAfter;

        $this->dispatchChunkedBatch($jobs, $hasPostBatchWork ? function () use ($gameVersion, $skipComputeItemGroups, $dispatchMissionsAfter): void {
            if (! $skipComputeItemGroups) {
                ComputeItemVariantGroupsJob::dispatch($gameVersion->id);
                ComputeItemSetItemsJob::dispatch($gameVersion->id);
            }

            if ($dispatchMissionsAfter) {
                Artisan::call('game:import-missions', [
                    'version' => $gameVersion->code,
                ]);
            }
        } : null);
    }

    private function dispatchVehicleImports(GameVersion $gameVersion, bool $skipBackfillShipmatrixIds, bool $skipComputeBespoke): void
    {
        $shipFiles = collect(Storage::disk($gameVersion->getStorageDiskName())->files('ships'))
            ->filter(static fn (string $path): bool => Str::endsWith($path, '.json'))
            ->reject(static fn (string $path): bool => Str::endsWith($path, '-raw.json'))
            ->values();

        if ($shipFiles->isEmpty()) {
            $this->warn('No ship files found in scunpacked-data/ships.');

            return;
        }

        $diskName = $gameVersion->getStorageDiskName();

        $jobs = $shipFiles->map(static function (string $path) use ($gameVersion, $diskName): ImportVehicleData {
            return new ImportVehicleData($gameVersion->id, $path, $diskName);
        });

        $hasPostWork = ! $skipBackfillShipmatrixIds || ! $skipComputeBespoke;

        $this->dispatchChunkedBatch($jobs, $hasPostWork ? static function () use ($gameVersion, $skipBackfillShipmatrixIds, $skipComputeBespoke): void {
            if (! $skipBackfillShipmatrixIds) {
                Artisan::call('game:backfill-shipmatrix-ids', [
                    '--game-version' => $gameVersion->code,
                ]);
            }

            if (! $skipComputeBespoke) {
                ComputeBespokeItemsJob::dispatch($gameVersion->id);
            }
        } : null);
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
        $loaderJobChunks = $loaderJobs->chunk(self::LOADER_BATCH_SIZE)->values();
        $firstLoaderChunk = $loaderJobChunks->shift();

        if ($firstLoaderChunk === null) {
            return;
        }

        $pendingBatch = Bus::batch($firstLoaderChunk->values());

        if ($then !== null) {
            $lastLoaderJob = $loaderJobChunks->isEmpty()
                ? $firstLoaderChunk->last()
                : $loaderJobChunks->last()->last();

            if ($lastLoaderJob !== null) {
                $pendingBatch->then($then);
            }
        }

        $batch = $pendingBatch->dispatch();

        $loaderJobChunks->each(static function (Collection $chunk) use ($batch): void {
            $batch->add($chunk->values());
        });
    }
}
