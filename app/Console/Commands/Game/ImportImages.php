<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Jobs\Game\EnrichImages;
use App\Models\Game\Commodity\Commodity;
use App\Models\Game\GameVersion;
use App\Models\Game\Item;
use App\Models\Game\ItemData;
use App\Models\Game\StarmapLocation;
use App\Models\Game\StarmapLocationData;
use App\Models\Game\Vehicle;
use App\Models\Game\VehicleData;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Bus;

class ImportImages extends Command
{
    protected $signature = 'game:import-images {--chunk=500 : Number of items per enrichment job}';

    protected $description = 'Import images from wiki sources for items, vehicles, starmap locations, and commodities';

    public function handle(): int
    {
        $gameVersion = GameVersion::query()
            ->where('is_default', true)
            ->first();

        if ($gameVersion === null) {
            $this->error('No default game version found.');

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');

        $this->info("Importing images for version {$gameVersion->code}...");

        $this->dispatchItemJobs($gameVersion, $chunkSize);
        $this->dispatchVehicleJobs($gameVersion, $chunkSize);
        $this->dispatchLocationJobs($gameVersion, $chunkSize);
        $this->dispatchCommodityJobs($chunkSize);

        $this->info('Image import batches dispatched.');

        return self::SUCCESS;
    }

    private function dispatchItemJobs(GameVersion $gameVersion, int $chunkSize): void
    {
        $rows = ItemData::query()
            ->where('game_version_id', $gameVersion->id)
            ->join('game_items', 'game_item_data.item_id', '=', 'game_items.id')
            ->whereNotNull('game_item_data.name')
            ->pluck('game_item_data.name', 'game_items.id')
            ->unique();

        $idNameMap = $rows->toArray();

        if ($idNameMap === []) {
            $this->info('No items found for image import.');

            return;
        }

        $idUuidMap = Item::query()
            ->whereIn('id', $rows->keys())
            ->pluck('uuid', 'id')
            ->toArray();

        $this->info('Dispatching image import for '.count($idNameMap).' items...');

        $this->dispatchBatch(Item::class, $idNameMap, $idUuidMap, $chunkSize);
    }

    private function dispatchVehicleJobs(GameVersion $gameVersion, int $chunkSize): void
    {
        $rows = VehicleData::query()
            ->where('game_version_id', $gameVersion->id)
            ->join('game_vehicles', 'game_vehicle_data.vehicle_id', '=', 'game_vehicles.id')
            ->pluck('game_vehicle_data.display_name', 'game_vehicles.id')
            ->unique()
            ->filter();

        $idNameMap = $rows->toArray();

        if ($idNameMap === []) {
            $this->info('No vehicles found for image import.');

            return;
        }

        $idUuidMap = Vehicle::query()
            ->whereIn('id', $rows->keys())
            ->pluck('uuid', 'id')
            ->toArray();

        $this->info('Dispatching image import for '.count($idNameMap).' vehicles...');

        $this->dispatchBatch(Vehicle::class, $idNameMap, $idUuidMap, $chunkSize);
    }

    private function dispatchLocationJobs(GameVersion $gameVersion, int $chunkSize): void
    {
        $rows = StarmapLocationData::query()
            ->where('game_version_id', $gameVersion->id)
            ->join('game_starmap_locations', 'game_starmap_location_data.starmap_location_id', '=', 'game_starmap_locations.id')
            ->pluck('game_starmap_location_data.name', 'game_starmap_locations.id')
            ->unique()
            ->filter();

        $idNameMap = $rows->toArray();

        if ($idNameMap === []) {
            $this->info('No starmap locations found for image import.');

            return;
        }

        $idUuidMap = StarmapLocation::query()
            ->whereIn('id', $rows->keys())
            ->pluck('uuid', 'id')
            ->toArray();

        $this->info('Dispatching image import for '.count($idNameMap).' starmap locations...');

        $this->dispatchBatch(StarmapLocation::class, $idNameMap, $idUuidMap, $chunkSize);
    }

    private function dispatchCommodityJobs(int $chunkSize): void
    {
        $rows = Commodity::query()
            ->whereNotNull('name')
            ->pluck('name', 'id')
            ->unique()
            ->filter();

        $idNameMap = $rows->toArray();

        if ($idNameMap === []) {
            $this->info('No commodities found for image import.');

            return;
        }

        $idUuidMap = Commodity::query()
            ->whereIn('id', $rows->keys())
            ->pluck('uuid', 'id')
            ->toArray();

        $this->info('Dispatching image import for '.count($idNameMap).' commodities...');

        $this->dispatchBatch(Commodity::class, $idNameMap, $idUuidMap, $chunkSize);
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<int, string>  $idNameMap
     * @param  array<int, string>  $idUuidMap
     */
    private function dispatchBatch(string $modelClass, array $idNameMap, array $idUuidMap, int $chunkSize): void
    {
        $chunks = collect($idNameMap)->chunk($chunkSize);

        $jobs = $chunks->map(
            fn ($chunk): EnrichImages => new EnrichImages($modelClass, $chunk->toArray(), array_intersect_key($idUuidMap, $chunk->toArray())),
        )->all();

        Bus::batch($jobs)->allowFailures()->dispatch();
    }
}
