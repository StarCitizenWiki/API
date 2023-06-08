<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiclePart extends Model
{
    protected $table = 'sc_vehicle_parts';

    protected $fillable = [
        'vehicle_id',
        'name',
        'damage_max',
        'parent',
    ];

    protected $casts = [
        'damage_max' => 'double',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }
}
