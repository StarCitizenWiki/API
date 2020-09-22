<?php

declare(strict_types=1);

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
