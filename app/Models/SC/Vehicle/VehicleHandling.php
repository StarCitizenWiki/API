<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleHandling extends Model
{
    protected $table = 'sc_vehicle_handlings';

    protected $fillable = [
        'vehicle_id',
        'max_speed',
        'reverse_speed',
        'acceleration',
        'deceleration',

        'v0_steer_max',
        'kv_steer_max',
        'vmax_steer_max',
    ];

    protected $casts = [
        'max_speed' => 'double',
        'reverse_speed' => 'double',
        'acceleration' => 'double',
        'deceleration' => 'double',
        'v0_steer_max' => 'double',
        'kv_steer_max' => 'double',
        'vmax_steer_max' => 'double',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function getZeroToMaxAttribute(): float
    {
        return round($this->max_speed / $this->acceleration, 2);
    }

    public function getMaxToZeroAttribute(): float
    {
        return round($this->max_speed / $this->deceleration, 2);
    }
}
