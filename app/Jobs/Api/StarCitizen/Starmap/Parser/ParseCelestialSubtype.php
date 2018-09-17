<?php
/**
 * User: Keonie
 * Date: 02.09.2018 20:29
 */

namespace App\Jobs\Api\StarCitizen\Starmap\Parser;


use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObjectSubtype;

class ParseCelestialSubtype
{

    public static function getCelestialSubtype($celestialSubtypeData): int {
        $celestialSubtype = CelestialObjectSubtype::updateOrCreate(
            [
                'id'   => $celestialSubtypeData['id'],
                'name' => $celestialSubtypeData['name'],
                'type' => $celestialSubtypeData['type'],
            ]
        );
        return $celestialSubtype->id;
    }
}