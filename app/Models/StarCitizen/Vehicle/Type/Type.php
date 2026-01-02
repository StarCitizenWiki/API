<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Type;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Vehicle Type Model
 */
class Type extends Model
{
    use HasFactory;
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
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(TypeTranslation::class);
    }

    /**
     * Ships
     */
    public function scopeShip(Builder $query): void
    {
        $query->where('slug', '!=', 'ground');
    }

    /**
     * Ground Vehicles
     */
    public function scopeGroundVehicle(Builder $query): void
    {
        $query->where('slug', 'ground');
    }
}
