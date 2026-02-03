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
     * Query shipmatrix vehicles with eager loading.
     *
     * Note: Phase 2 will add whereDoesntHave('sc') for deduplication.
     *
     * @return Collection<int, ShipMatrixVehicle>
     */
    public function getShipMatrixVehicles(): Collection
    {
        return ShipMatrixVehicle::query()
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
