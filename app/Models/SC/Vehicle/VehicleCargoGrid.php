<?php

namespace App\Models\SC\Vehicle;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleCargoGrid extends Model
{
    protected $table = 'sc_vehicle_cargo_grids';

    protected $fillable = [
        'vehicle_id',
        'container_uuid',
        'capacity',
        'unit_name',
        'x',
        'y',
        'z',
        'min_x',
        'min_y',
        'min_z',
        'max_x',
        'max_y',
        'max_z',
        'is_open',
        'is_external',
        'is_closed',
    ];

    protected $casts = [
        'capacity' => 'double',
        'x' => 'double',
        'y' => 'double',
        'z' => 'double',
        'min_x' => 'double',
        'min_y' => 'double',
        'min_z' => 'double',
        'max_x' => 'double',
        'max_y' => 'double',
        'max_z' => 'double',
        'is_open' => 'bool',
        'is_external' => 'bool',
        'is_closed' => 'bool',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }
}
