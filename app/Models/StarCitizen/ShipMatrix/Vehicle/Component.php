<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;

    /**
     * @var string
     */
    protected $table = 'shipmatrix_vehicle_components';

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
