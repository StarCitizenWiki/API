<?php

declare(strict_types=1);

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;

use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObjectSubtype;

/**
 * Class ParseCelestialSubtype
 */
class ParseCelestialSubtype
{
    /**
     * @param array $celestialSubtypeData
     *
     * @return int
     */
    public static function getCelestialSubtype($celestialSubtypeData): int
    {
        $celestialSubtype = CelestialObjectSubtype::updateOrCreate(
            [
                'id' => $celestialSubtypeData['id'],
                'name' => $celestialSubtypeData['name'],
                'type' => $celestialSubtypeData['type'],
            ]
        );

        return $celestialSubtype->id;
    }
}
