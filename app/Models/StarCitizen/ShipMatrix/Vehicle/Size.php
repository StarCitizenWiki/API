<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Vehicle Size Model
 */
class Size extends Model
{
    use HasFactory;
    use HasTranslations;
    use VehicleRelations;

    /**
     * @var bool
     */
    public $timestamps = false;

    public array $translatable = ['translation'];

    /**
     * @var array
     */
    protected $fillable = [
        'slug',
        'translation',
    ];

    /**
     * @var string
     */
    protected $table = 'shipmatrix_vehicle_sizes';

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Ships
     */
    public function scopeShip(Builder $query): void
    {
        $query->where('slug', '!=', 'vehicle');
    }

    /**
     * Ground Vehicles
     */
    public function scopeGroundVehicle(Builder $query): void
    {
        $query->where('slug', 'vehicle');
    }
}
