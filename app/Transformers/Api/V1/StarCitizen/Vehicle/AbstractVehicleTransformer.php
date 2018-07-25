<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 24.07.2018
 * Time: 21:23
 */

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\VehicleInterface as Vehicle;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;

/**
 * Class AbstractVehicleTransformer
 */
abstract class AbstractVehicleTransformer extends TransformerAbstract
{
    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\VehicleInterface $vehicle
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
                $translationsArray = [];
                $vehicleFocus->translations->each(
                    function ($translation) use (&$translationsArray) {
                        $translationsArray[$translation->locale_code] = $translation->translation;
                    }
                );
                $fociTranslations[] = $translationsArray;
            }
        );

        return $fociTranslations;
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\VehicleInterface $vehicle
     *
     * @return array
     */
    protected function getProductionStatusTranslations(Vehicle $vehicle)
    {
        return $this->extractFromCollection($vehicle->productionStatus->translations);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\VehicleInterface $vehicle
     *
     * @return array
     */
    protected function getProductionNoteTranslations(Vehicle $vehicle)
    {
        return $this->extractFromCollection($vehicle->productionNote->translations);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\VehicleInterface $vehicle
     *
     * @return array
     */
    protected function getDescriptionTranslations(Vehicle $vehicle)
    {
        return $this->extractFromCollection($vehicle->description);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\VehicleInterface $vehicle
     *
     * @return array
     */
    protected function getTypeTranslations(Vehicle $vehicle)
    {
        return $this->extractFromCollection($vehicle->type->translations);
    }

    /**
     * @param \App\Models\Api\StarCitizen\Vehicle\VehicleInterface $vehicle
     *
     * @return array
     */
    protected function getSizeTranslations(Vehicle $vehicle)
    {
        return $this->extractFromCollection($vehicle->size->translations);
    }

    /**
     * @param \Illuminate\Support\Collection $collection
     *
     * @return array
     */
    private function extractFromCollection(Collection $collection): array
    {
        $translations = [];

        $collection->each(
            function ($vehicleFocusTranslation) use (&$translations) {
                $translations[$vehicleFocusTranslation->locale_code] = $vehicleFocusTranslation->translation;
            }
        );

        return $translations;
    }
}
