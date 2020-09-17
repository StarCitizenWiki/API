<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Ground Vehicle Link Transformer
 */
class GroundVehicleLinkTransformer extends VehicleTransformer
{
    /**
     * @param GroundVehicle $groundVehicle
     *
     * @return array
     */
    public function transform(GroundVehicle $groundVehicle): array
    {
        return [
            'name' => $groundVehicle->name,
            'slug' => $groundVehicle->slug,
            'api_url' => $this->makeApiUrl(self::VEHICLES_GROUND_VEHICLES_SHOW, $groundVehicle->getRouteKey()),
        ];
    }
}
