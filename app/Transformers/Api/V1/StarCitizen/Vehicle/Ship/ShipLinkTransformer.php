<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 13:59
 */

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Ship Link Transformer
 */
class ShipLinkTransformer extends VehicleTransformer
{
    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship
     *
     * @return array
     */
    public function transform(Ship $ship)
    {
        return [
            'name' => $ship->name,
            'slug' => $ship->slug,
            'api_url' => app('api.url')->version('v1')->route(
                'api.v1.starcitizen.vehicles.ships.show',
                [$ship->getRouteKey()]
            ),
        ];
    }
}
