<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer as TranslationTransformer;
use App\Transformers\Api\V1\StarCitizenUnpacked\Shop\ShopTransformer;
use Illuminate\Support\Collection;

/**
 * Class AbstractVehicleTransformer
 */
class VehicleTransformer extends TranslationTransformer
{
    protected $availableIncludes = [
        'components',
        'shops',
    ];

    /**
     * @param Vehicle $vehicle
     *
     * @return array
     */
    public function transform(Vehicle $vehicle): array
    {
        $this->missingTranslations = [];

        $data = [
            'id' => $vehicle->cig_id,
            'uuid' => $vehicle->unpacked->uuid,
            'chassis_id' => $vehicle->chassis_id,
            'name' => $vehicle->name,
            'slug' => $vehicle->slug,
            'sizes' => [
                'length' => (double)$vehicle->length,
                'beam' => (double)$vehicle->width,
                'height' => (double)$vehicle->height,
            ],
            'mass' => $vehicle->unpacked->mass ?? $vehicle->mass,
            'cargo_capacity' => $vehicle->unpacked->cargo_capacity ?? $vehicle->cargo_capacity,
            'crew' => [
                'min' => $vehicle->unpacked->crew ?? $vehicle->min_crew,
                'max' => $vehicle->max_crew,
                'weapon' => $vehicle->unpacked->weapon_crew ?? 0,
                'operation' => $vehicle->unpacked->operation_crew ?? 0,
            ],
            'health' => $vehicle->unpacked->health_body ?? 0,
            'speed' => [
                'scm' => $vehicle->unpacked->scm_speed ?? $vehicle->scm_speed,
                'afterburner' => $vehicle->afterburner_speed,
                'max' => $vehicle->unpacked->max_speed ?? 0,
                'zero_to_scm' => $vehicle->unpacked->zero_to_scm ?? 0,
                'zero_to_max' => $vehicle->unpacked->zero_to_max ?? 0,
                'scm_to_zero' => $vehicle->unpacked->scm_to_zero ?? 0,
                'max_to_zero' => $vehicle->unpacked->max_to_zero ?? 0,
            ],
            'fuel' => [
                'capacity' => $vehicle->unpacked->fuel_capacity ?? 0,
                'intake_rate' => $vehicle->unpacked->fuel_intake_rate ?? 0,
                'usage' => [
                    'main' => $vehicle->unpacked->fuel_usage_main ?? 0,
                    'retro' => $vehicle->unpacked->fuel_usage_retro ?? 0,
                    'vtol' => $vehicle->unpacked->fuel_usage_vtol ?? 0,
                    'maneuvering' => $vehicle->unpacked->fuel_usage_maneuvering ?? 0,
                ],
            ],
            'quantum' => [
                'quantum_speed' => $vehicle->unpacked->quantum_speed ?? 0,
                'quantum_spool_time' => $vehicle->unpacked->quantum_spool_time ?? 0,
                'quantum_fuel_capacity' => $vehicle->unpacked->quantum_fuel_capacity ?? 0,
                'quantum_range' => $vehicle->unpacked->quantum_range ?? 0,
            ],
            'agility' => [
                'pitch' => $vehicle->pitch_max,
                'yaw' => $vehicle->yaw_max,
                'roll' => $vehicle->roll_max,
                'acceleration' => [
                    'x_axis' => $vehicle->x_axis_acceleration,
                    'y_axis' => $vehicle->y_axis_acceleration,
                    'z_axis' => $vehicle->z_axis_acceleration,

                    'main' => $vehicle->unpacked->acceleration_main ?? 0,
                    'retro' => $vehicle->unpacked->acceleration_retro ?? 0,
                    'vtol' => $vehicle->unpacked->acceleration_vtol ?? 0,
                    'maneuvering' => $vehicle->unpacked->acceleration_maneuvering ?? 0,

                    'main_g' => $vehicle->unpacked->acceleration_g_main ?? 0,
                    'retro_g' => $vehicle->unpacked->acceleration_g_retro ?? 0,
                    'vtol_g' => $vehicle->unpacked->acceleration_g_vtol ?? 0,
                    'maneuvering_g' => $vehicle->unpacked->acceleration_g_maneuvering ?? 0,
                ],
            ],
            'foci' => $this->getFociTranslations($vehicle),
            'production_status' => $this->getProductionStatusTranslations($vehicle),
            'production_note' => $this->getProductionNoteTranslations($vehicle),
            'type' => $this->getTypeTranslations($vehicle),
            'description' => $this->getDescriptionTranslations($vehicle),
            'size' => $this->getSizeTranslations($vehicle),
            'msrp' => $vehicle->msrp,
            'manufacturer' => [
                'code' => $vehicle->manufacturer->name_short,
                'name' => $vehicle->manufacturer->name,
            ],
            'insurance' => [
                'claim_time' => $vehicle->unpacked->claim_time ?? 0,
                'expedite_time' => $vehicle->unpacked->expedite_time ?? 0,
                'expedite_cost' => $vehicle->unpacked->expedite_cost ?? 0,
            ],
            'updated_at' => $vehicle->updated_at,
            'missing_translations' => $this->missingTranslations,
        ];

        if (optional($vehicle->unpacked)->quantum_speed !== null) {
            $data['version'] = config('api.sc_data_version');
        }

        return $data;
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return \League\Fractal\Resource\Collection
     *
     * TODO Wrap by component_class key
     */
    public function includeComponents(Vehicle $vehicle): \League\Fractal\Resource\Collection
    {
        return $this->collection($vehicle->components, new ComponentTransformer());
    }

    /**
     * @param Vehicle $vehicle
     * @return \League\Fractal\Resource\Collection
     */
    public function includeShops($vehicle): \League\Fractal\Resource\Collection
    {
        return $this->collection($vehicle->unpacked->shops, new ShopTransformer());
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array
     */
    protected function getFociTranslations(Vehicle $vehicle): array
    {
        /** @var Collection $foci */
        $foci = $vehicle->foci;
        $fociTranslations = [];

        $foci->each(
            function ($vehicleFocus) use (&$fociTranslations) {
                $fociTranslations[] = $this->getTranslation($vehicleFocus);
            }
        );

        return $fociTranslations;
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getProductionStatusTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionStatus);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getProductionNoteTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionNote);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getDescriptionTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getTypeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->type);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array|string
     */
    protected function getSizeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->size);
    }
}
