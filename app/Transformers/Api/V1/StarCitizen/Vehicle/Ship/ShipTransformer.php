<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\Ship;

use App\Models\StarCitizen\Vehicle\Ship\Ship;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Ship Transformer
 */
class ShipTransformer extends VehicleTransformer
{
    /**
     * @param Ship $ship
     *
     * @return array
     */
    public function transform(Ship $ship): array
    {
        $this->missingTranslations = [];

        $data = [
            'id' => $ship->cig_id,
            'chassis_id' => $ship->chassis_id,
            'name' => $ship->name,
            'slug' => $ship->slug,
            'sizes' => [
                'length' => $ship->length,
                'beam' => $ship->beam,
                'height' => $ship->height,
            ],
            'mass' => $ship->unpacked->mass ?? $ship->mass,
            'cargo_capacity' => $ship->unpacked->cargo_capacity ?? $ship->cargo_capacity,
            'crew' => [
                'min' => $ship->unpacked->crew ?? $ship->min_crew,
                'max' => $ship->max_crew,
                'weapon' => $ship->unpacked->weapon_crew ?? 0,
                'operation' => $ship->unpacked->operation_crew ?? 0,
            ],
            'health' => $ship->unpacked->health_body ?? 0,
            'speed' => [
                'scm' => $ship->unpacked->scm_speed ?? $ship->scm_speed,
                'afterburner' => $ship->afterburner_speed,
                'max' => $ship->unpacked->max_speed ?? 0,
                'zero_to_scm' => $ship->unpacked->zero_to_scm ?? 0,
                'zero_to_max' => $ship->unpacked->zero_to_max ?? 0,
                'scm_to_zero' => $ship->unpacked->scm_to_zero ?? 0,
                'max_to_zero' => $ship->unpacked->max_to_zero ?? 0,
            ],
            'fuel' => [
                'capacity' => $ship->unpacked->fuel_capacity ?? 0,
                'intake_rate' => $ship->unpacked->fuel_intake_rate ?? 0,
                'usage' => [
                    'main' => $ship->unpacked->fuel_usage_main ?? 0,
                    'retro' => $ship->unpacked->fuel_usage_retro ?? 0,
                    'vtol' => $ship->unpacked->fuel_usage_vtol ?? 0,
                    'maneuvering' => $ship->unpacked->fuel_usage_maneuvering ?? 0,
                ],
            ],
            'quantum' => [
                'quantum_speed' => $ship->unpacked->quantum_speed ?? 0,
                'quantum_spool_time' => $ship->unpacked->quantum_spool_time ?? 0,
                'quantum_fuel_capacity' => $ship->unpacked->quantum_fuel_capacity ?? 0,
                'quantum_range' => $ship->unpacked->quantum_range ?? 0,
            ],
            'agility' => [
                'pitch' => $ship->pitch_max,
                'yaw' => $ship->yaw_max,
                'roll' => $ship->roll_max,
                'acceleration' => [
                    'x_axis' => $ship->x_axis_acceleration,
                    'y_axis' => $ship->y_axis_acceleration,
                    'z_axis' => $ship->z_axis_acceleration,

                    'main' => $ship->unpacked->acceleration_main ?? 0,
                    'retro' => $ship->unpacked->acceleration_retro ?? 0,
                    'vtol' => $ship->unpacked->acceleration_vtol ?? 0,
                    'maneuvering' => $ship->unpacked->acceleration_maneuvering ?? 0,

                    'main_g' => $ship->unpacked->acceleration_g_main ?? 0,
                    'retro_g' => $ship->unpacked->acceleration_g_retro ?? 0,
                    'vtol_g' => $ship->unpacked->acceleration_g_vtol ?? 0,
                    'maneuvering_g' => $ship->unpacked->acceleration_g_maneuvering ?? 0,
                ],
            ],
            'foci' => $this->getFociTranslations($ship),
            'production_status' => $this->getProductionStatusTranslations($ship),
            'production_note' => $this->getProductionNoteTranslations($ship),
            'type' => $this->getTypeTranslations($ship),
            'description' => $this->getDescriptionTranslations($ship),
            'size' => $this->getSizeTranslations($ship),
            'msrp' => $ship->msrp,
            'manufacturer' => [
                'code' => $ship->manufacturer->name_short,
                'name' => $ship->manufacturer->name,
            ],
            'insurance' => [
                'claim_time' => $ship->unpacked->claim_time ?? 0,
                'expedite_time' => $ship->unpacked->expedite_time ?? 0,
                'expedite_cost' => $ship->unpacked->expedite_cost ?? 0,
            ],
            'updated_at' => $ship->updated_at,
            'missing_translations' => $this->missingTranslations,
        ];

        if (optional($ship->unpacked)->quantum_speed !== null) {
            $data['version'] = config('api.sc_data_version');
        }

        return $data;
    }
}
