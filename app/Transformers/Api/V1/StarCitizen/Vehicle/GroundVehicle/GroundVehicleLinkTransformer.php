<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 13:59
 */

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Ground Vehicle Link Transformer
 */
class GroundVehicleLinkTransformer extends VehicleTransformer
{
    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle
     *
     * @return array
     */
    public function transform(GroundVehicle $groundVehicle)
    {
        return [
            'name' => $groundVehicle->name,
            'slug' => $groundVehicle->slug,
            'api_url' => app('api.url')->version('v1')->route(
                'api.v1.starcitizen.vehicles.ground-vehicles.show',
                [$groundVehicle->getRouteKey()]
            ),
        ];
    }
}
