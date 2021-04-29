<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuantumDrive extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_quantum_drives';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'fuel_rate',
        'jump_range',
        'standard_speed',
        'standard_cooldown',
        'standard_stage_1_acceleration',
        'standard_stage_2_acceleration',
        'standard_spool_time',
        'spline_speed',
        'spline_cooldown',
        'spline_stage_1_acceleration',
        'spline_stage_2_acceleration',
        'spline_spool_time',
    ];

    protected $casts = [
        'fuel_rate' => 'double',
        'jump_range' => 'double',
        'standard_speed' => 'double',
        'standard_cooldown' => 'double',
        'standard_stage_1_acceleration' => 'double',
        'standard_stage_2_acceleration' => 'double',
        'standard_spool_time' => 'double',
        'spline_speed' => 'double',
        'spline_cooldown' => 'double',
        'spline_stage_1_acceleration' => 'double',
        'spline_stage_2_acceleration' => 'double',
        'spline_spool_time' => 'double',
    ];
}
