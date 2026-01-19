<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Focus extends Model
{
    use HasFactory;
    use HasTranslations;
    use VehicleRelations;

    public $timestamps = false;

    public array $translatable = ['translation'];

    protected $fillable = [
        'slug',
        'translation',
    ];

    protected $table = 'shipmatrix_vehicle_foci';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
