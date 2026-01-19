<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Manufacturer extends Model
{
    use HasFactory;
    use HasTranslations;
    use VehicleRelations;

    protected $table = 'shipmatrix_manufacturers';

    public array $translatable = ['known_for', 'description'];

    protected $fillable = [
        'cig_id',
        'name',
        'name_short',
        'known_for',
        'description',
    ];

    protected $withCount = [
        'ships',
        'vehicles',
    ];

    public function getRouteKeyName(): string
    {
        return 'name_short';
    }
}
