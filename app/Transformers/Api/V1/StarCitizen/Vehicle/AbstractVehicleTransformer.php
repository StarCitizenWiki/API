<?php declare(strict_types = 1);

namespace App\Transformers\Api\V1\StarCitizen\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use Illuminate\Support\Collection;

/**
 * Class AbstractVehicleTransformer
 */
abstract class AbstractVehicleTransformer extends AbstractTranslationTransformer
{
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
