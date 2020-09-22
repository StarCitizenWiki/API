<?php

declare(strict_types=1);
/**
 * User: Keonie
 * Date: 04.08.2018 20:12
 */

namespace App\Models\Api\StarCitizen\Starmap\CelestialObject;

use Illuminate\Database\Eloquent\Model;

/**
 * CelestialObjectSubtype Model
 */
class CelestialObjectSubtype extends Model
{
    protected $fillable = [
        'id',
        'name',
        'type',
    ];
}
