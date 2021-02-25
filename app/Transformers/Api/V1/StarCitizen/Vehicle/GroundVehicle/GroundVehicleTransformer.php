<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle\GroundVehicle;

use App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Transformers\Api\V1\StarCitizen\Vehicle\AbstractVehicleTransformer as VehicleTransformer;

/**
 * Class Ground Vehicle Transformer
 */
class GroundVehicleTransformer extends VehicleTransformer
{
    /**
     * @param GroundVehicle $groundVehicle
     *
     * @return array
     */
    public function transform(GroundVehicle $groundVehicle): array
    {
        $this->missingTranslations = [];

        return [
            'id' => $groundVehicle->cig_id,
            'chassis_id' => $groundVehicle->chassis_id,
            'name' => $groundVehicle->name,
            'slug' => $groundVehicle->slug,
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
            'msrp' => $groundVehicle->msrp,
            'manufacturer' => [
                'code' => $groundVehicle->manufacturer->name_short,
                'name' => $groundVehicle->manufacturer->name,
            ],
            'updated_at' => $groundVehicle->updated_at,
            'missing_translations' => $this->missingTranslations,
        ];
    }
}
