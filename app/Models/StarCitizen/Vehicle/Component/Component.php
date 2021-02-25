<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Component;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use VehicleRelations;
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'vehicle_components';

    /**
     * @var string[]
     */
    protected $fillable = [
        'type',
        'name',
        'component_size',
        'category',
        'manufacturer',
        'component_class',
    ];
}
