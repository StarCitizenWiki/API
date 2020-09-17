<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Ship Link Transformer
 */
class ShipLinkTransformer extends VehicleTransformer
{
    /**
     * @param Ship $ship
     *
     * @return array
     */
    public function transform(Ship $ship): array
    {
        return [
            'name' => $ship->name,
            'slug' => $ship->slug,
            'api_url' => $this->makeApiUrl(self::VEHICLES_SHIPS_SHOW, $ship->getRouteKey()),
        ];
    }
}
