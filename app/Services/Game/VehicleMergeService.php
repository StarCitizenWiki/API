<?php

declare(strict_types=1);

namespace App\Services\Game;

use App\Models\Game\VehicleData;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle as ShipMatrixVehicle;
use Illuminate\Support\Collection;

class VehicleMergeService
{
    /**
     * Query ingame vehicles with version filtering and eager loading.
     *
     * @return Collection<int, VehicleData>
     */
    public function getIngameVehicles(?string $versionCode = null): Collection
    {
        return VehicleData::query()
            ->forRequestedOrDefaultVersion($versionCode)
            ->with(['vehicle', 'gameVersion', 'manufacturer'])
            ->get();
    }

    /**
     * Query shipmatrix vehicles with eager loading and deduplication.
     *
     * Deduplication strategy: Excludes ShipMatrixVehicle records where a VehicleData
     * record exists with matching shipmatrix_id (via the 'sc' relationship).
     * This ensures ingame vehicle data is prioritized when the same vehicle
     * exists in both sources.
     *
     * The sc() relationship is a HasOne from ShipMatrixVehicle to VehicleData via
     * the shipmatrix_id field. Using whereDoesntHave('sc') filters at the query
     * level for efficient exclusion of duplicates.
     *
     * @return Collection<int, ShipMatrixVehicle>
     */
    public function getShipMatrixVehicles(): Collection
    {
        return ShipMatrixVehicle::query()
            ->whereDoesntHave('sc')
            ->with(['foci', 'manufacturer', 'productionStatus', 'type', 'size'])
            ->get();
    }

    /**
     * Merge ingame and shipmatrix collections into a single dataset.
     *
     * @param  Collection<int, VehicleData>  $ingame
     * @param  Collection<int, ShipMatrixVehicle>  $shipmatrix
     * @return Collection<int, VehicleData|ShipMatrixVehicle>
     */
    public function mergeCollections(Collection $ingame, Collection $shipmatrix): Collection
    {
        return $ingame->concat($shipmatrix);
    }
}
