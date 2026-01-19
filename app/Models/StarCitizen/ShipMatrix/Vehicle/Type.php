<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Type extends Model
{
    use HasFactory;
    use HasTranslations;
    use VehicleRelations;

    public array $translatable = ['translation'];

    protected $table = 'shipmatrix_vehicle_types';

    protected $fillable = [
        'slug',
        'translation',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeShip(Builder $query): void
    {
        $query->where('slug', '!=', 'ground');
    }

    public function scopeGroundVehicle(Builder $query): void
    {
        $query->where('slug', 'ground');
    }
}
