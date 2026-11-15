<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use Illuminate\Database\Eloquent\Model;

class VehicleSku extends Model
{
    protected $table = 'shipmatrix_vehicle_skus';

    protected $fillable = [
        'vehicle_id',
        'title',
        'price',
        'available',
        'cig_id',
    ];

    protected $casts = [
        'price' => 'integer',
        'available' => 'boolean',
        'cig_id' => 'integer',
    ];
}
