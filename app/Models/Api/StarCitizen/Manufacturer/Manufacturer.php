<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Manufacturer;

use App\Events\ModelUpdating;
use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;

/**
 * Manufacturer Model
 */
class Manufacturer extends HasTranslations
{
    use VehicleRelations;
    use ModelChangelog;
    use ObfuscatedRouteKey;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
    ];

    protected $fillable = [
        'cig_id',
        'name',
        'name_short',
    ];

    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(ManufacturerTranslation::class);
    }
}
