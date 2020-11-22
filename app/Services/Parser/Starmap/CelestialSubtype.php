<?php

declare(strict_types=1);

namespace App\Services\Parser\Starmap;

use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObjectSubtype;
use Illuminate\Support\Collection;

/**
 * Class ParseCelestialSubtype
 */
class CelestialSubtype
{
    private Collection $rawData;

    public function __construct($rawData)
    {
        $this->rawData = new Collection($rawData);
    }

    /**
     * @return CelestialObjectSubtype
     */
    public function getCelestialSubtype(): ?CelestialObjectSubtype
    {
        if ($this->rawData->isEmpty()) {
            return null;
        }

        return CelestialObjectSubtype::updateOrCreate(
            [
                'id' => $this->rawData->get('id'),
            ],
            [
                'name' => $this->rawData->get('name'),
                'type' => $this->rawData->get('type'),
            ]
        );
    }
}
