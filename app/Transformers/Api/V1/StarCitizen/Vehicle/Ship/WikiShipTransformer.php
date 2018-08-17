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
 * Class Wiki Ship Transformer
 * Flat Array
 */
class WikiShipTransformer extends VehicleTransformer
{
    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship
     *
     * @return array
     */
    public function transform(Ship $ship)
    {
        return [
            'id' => $ship->cig_id,
            'chassis_id' => $ship->chassis_id,
            'name' => $ship->name,
            'length' => $ship->length,
            'beam' => $ship->beam,
            'height' => $ship->height,
            'mass' => $ship->mass,
            'cargo_capacity' => $ship->cargo_capacity,
            'crew_min' => $ship->min_crew,
            'crew_max' => $ship->max_crew,
            'scm_speed' => $ship->scm_speed,
            'afterburner_speed' => $ship->afterburner_speed,
            'pitch' => $ship->pitch_max,
            'yaw' => $ship->yaw_max,
            'roll' => $ship->roll_max,
            'x_axis_acceleration' => $ship->x_axis_acceleration,
            'y_axis_acceleration' => $ship->y_axis_acceleration,
            'z_axis_acceleration' => $ship->z_axis_acceleration,
            'foci' => rtrim(implode(',', $this->getFociTranslations($ship)), ','),
            'production_status' => $this->getProductionStatusTranslations($ship),
            'production_note' => $this->getProductionNoteTranslations($ship),
            'type' => $this->getTypeTranslations($ship),
            'description' => $this->getDescriptionTranslations($ship),
            'size' => $this->getSizeTranslations($ship),
            'manufacturer_code' => $ship->manufacturer->name_short,
            'manufacturer_name' => $ship->manufacturer->name,
        ];
    }
}
