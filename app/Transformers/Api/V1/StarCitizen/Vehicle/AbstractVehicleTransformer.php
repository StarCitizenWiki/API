<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 21:23
 */

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;

/**
 * Class AbstractVehicleTransformer
 */
abstract class AbstractVehicleTransformer extends AbstractTranslationTransformer
{

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle
     *
     * @return array
     */
    protected function getFociTranslations(Vehicle $vehicle)
    {
        /** @var \Illuminate\Support\Collection $foci */
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
     * @param \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle
     *
     * @return array
     */
    protected function getProductionStatusTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionStatus);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle
     *
     * @return array
     */
    protected function getProductionNoteTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->productionNote);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle
     *
     * @return array
     */
    protected function getDescriptionTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle
     *
     * @return array
     */
    protected function getTypeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->type);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle $vehicle
     *
     * @return array
     */
    protected function getSizeTranslations(Vehicle $vehicle)
    {
        return $this->getTranslation($vehicle->size);
    }
}
