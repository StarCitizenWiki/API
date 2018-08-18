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
 * Class Ground Vehicle Transformer
 */
class GroundVehicleTransformer extends VehicleTransformer
{
    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle
     *
     * @return array
     */
    public function transform(GroundVehicle $groundVehicle)
    {
        return [
            'id' => $groundVehicle->cig_id,
            'chassis_id' => $groundVehicle->chassis_id,
            'name' => $groundVehicle->name,
            'sizes' => [
                'length' => $groundVehicle->length,
                'beam' => $groundVehicle->beam,
                'height' => $groundVehicle->height,
            ],
            'mass' => $groundVehicle->mass,
            'cargo_capacity' => $groundVehicle->cargo_capacity,
            'crew' => [
                'min' => $groundVehicle->min_crew,
                'max' => $groundVehicle->max_crew,
            ],
            'speed' => [
                'scm' => $groundVehicle->scm_speed,
            ],
            'foci' => $this->getFociTranslations($groundVehicle),
            'production_status' => $this->getProductionStatusTranslations($groundVehicle),
            'production_note' => $this->getProductionNoteTranslations($groundVehicle),
            'type' => $this->getTypeTranslations($groundVehicle),
            'description' => $this->getDescriptionTranslations($groundVehicle),
            'size' => $this->getSizeTranslations($groundVehicle),
            'manufacturer' => [
                'code' => $groundVehicle->manufacturer->name_short,
                'name' => $groundVehicle->manufacturer->name,
            ],
        ];
    }
}
