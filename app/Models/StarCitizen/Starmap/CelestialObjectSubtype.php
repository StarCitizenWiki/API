<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Model;

/**
 * CelestialObjectSubtype Model
 */
class CelestialObjectSubtype extends Model
{
    protected $table = 'starmap_celestial_object_subtypes';

    protected $fillable = [
        'id',
        'name',
        'type',
    ];
}
