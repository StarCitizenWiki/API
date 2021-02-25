<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Models\StarCitizen\Vehicle\Ship\Ship;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait HasModelTranslationsTrait
 */
trait HasVehicleRelationsTrait
{
    /**
     * @return HasMany Ships
     */
    public function ships(): HasMany
    {
        return $this->hasMany(Ship::class);
    }

    /**
     * @return HasMany Ground Vehicles
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(GroundVehicle::class);
    }
}
