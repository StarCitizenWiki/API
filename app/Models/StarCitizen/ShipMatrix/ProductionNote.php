<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductionNote extends Model
{
    use HasFactory;
    use HasTranslations;
    use VehicleRelations;

    protected $table = 'shipmatrix_production_notes';

    public array $translatable = ['translation'];

    protected $fillable = [
        'translation',
    ];
}
