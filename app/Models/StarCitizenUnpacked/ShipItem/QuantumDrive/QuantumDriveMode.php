<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantumDriveMode extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_quantum_drive_modes';

    protected $fillable = [
        'quantum_drive_id',
        'type',

        'drive_speed',
        'cooldown_time',
        'stage_one_accel_rate',
        'stage_two_accel_rate',
        'engage_speed',
        'interdiction_effect_time',
        'calibration_rate',
        'min_calibration_requirement',
        'max_calibration_requirement',
        'calibration_process_angle_limit',
        'calibration_warning_angle_limit',
        'spool_up_time',
    ];

    protected $casts = [
        'drive_speed' => 'double',
        'cooldown_time' => 'double',
        'stage_one_accel_rate' => 'double',
        'stage_two_accel_rate' => 'double',
        'engage_speed' => 'double',
        'interdiction_effect_time' => 'double',
        'calibration_rate' => 'double',
        'min_calibration_requirement' => 'double',
        'max_calibration_requirement' => 'double',
        'calibration_process_angle_limit' => 'double',
        'calibration_warning_angle_limit' => 'double',
        'spool_up_time' => 'double',
    ];

    public function quantumDrive(): BelongsTo
    {
        return $this->belongsTo(QuantumDrive::class, 'quantum_drive_id');
    }
}
