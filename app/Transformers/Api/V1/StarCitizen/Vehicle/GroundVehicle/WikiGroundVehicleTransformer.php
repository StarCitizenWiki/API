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
 * Class Wiki Ground Vehicle Transformer
 * Flat Array
 */
class WikiGroundVehicleTransformer extends VehicleTransformer
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
            'length' => $groundVehicle->length,
            'beam' => $groundVehicle->beam,
            'height' => $groundVehicle->height,
            'mass' => $groundVehicle->mass,
            'cargo_capacity' => $groundVehicle->cargo_capacity,
            'crew_min' => $groundVehicle->min_crew,
            'crew_max' => $groundVehicle->max_crew,
            'scm_speed' => $groundVehicle->scm_speed,
            'foci' => rtrim(implode(',', $this->getFociTranslations($groundVehicle)), ','),
            'production_status' => $this->getProductionStatusTranslations($groundVehicle),
            'production_note' => $this->getProductionNoteTranslations($groundVehicle),
            'type' => $this->getTypeTranslations($groundVehicle),
            'description' => $this->getDescriptionTranslations($groundVehicle),
            'size' => $this->getSizeTranslations($groundVehicle),
            'manufacturer_code' => $groundVehicle->manufacturer->name_short,
            'manufacturer_name' => $groundVehicle->manufacturer->name,
        ];
    }
}
