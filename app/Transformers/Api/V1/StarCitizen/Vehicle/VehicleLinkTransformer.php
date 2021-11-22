<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Ship\Ship;
use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;

/**
 * Class Ship Link Transformer
 */
class VehicleLinkTransformer extends VehicleTransformer
{
    /**
     * @param Ship $vehicle
     *
     * @return array
     */
    public function transform(Vehicle $vehicle): array
    {
        return [
            'name' => $vehicle->name,
            'slug' => $vehicle->slug,
            'api_url' => $this->makeApiUrl(self::VEHICLES_SHOW, $vehicle->getRouteKey()),
        ];
    }
}
