<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Type;

use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Builder;

/**
 * Vehicle Type Model
 */
class Type extends HasTranslations
{
    use VehicleRelations;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'slug',
    ];

    /**
     * @var string
     */
    protected $table = 'vehicle_types';

    /**
     * @var array
     */
    protected $with = [
        'translations',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(TypeTranslation::class);
    }

    /**
     * Ships
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeShip(Builder $query)
    {
        $query->where('slug', '!=', 'ground');
    }

    /**
     * Ground Vehicles
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeGroundVehicle(Builder $query)
    {
        $query->where('slug', 'ground');
    }
}
