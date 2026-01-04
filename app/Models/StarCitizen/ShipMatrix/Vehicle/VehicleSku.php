<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
