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
 * Class StatsTransformer
 */
class ShipTransformer extends VehicleTransformer
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
            'sizes' => [
                'length' => $ship->length,
                'beam' => $ship->beam,
                'height' => $ship->height,
            ],
            'mass' => $ship->mass,
            'cargo_capacity' => $ship->cargo_capacity,
            'crew' => [
                'min' => $ship->min_crew,
                'max' => $ship->max_crew,
            ],
            'speed' => [
                'scm' => $ship->scm_speed,
                'afterburner' => $ship->afterburner_speed,
            ],
            'agility' => [
                'pitch' => $ship->pitch_max,
                'yaw' => $ship->yaw_max,
                'roll' => $ship->roll_max,
                'acceleration' => [
                    'x_axis' => $ship->x_axis_acceleration,
                    'y_axis' => $ship->y_axis_acceleration,
                    'z_axis' => $ship->z_axis_acceleration,
                ],
            ],
            'foci' => $this->getFociTranslations($ship),
            'production_status' => $this->getProductionStatusTranslations($ship),
            'production_note' => $this->getProductionNoteTranslations($ship),
            'type' => $this->getTypeTranslations($ship),
            'description' => $this->getDescriptionTranslations($ship),
            'size' => $this->getSizeTranslations($ship),
            'manufacturer' => [
                'code' => $ship->manufacturer->name_short,
                'name' => $ship->manufacturer->name,
            ],
        ];
    }
}
