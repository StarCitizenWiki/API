<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuantumDrive extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_quantum_drives';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'quantum_fuel_requirement',
        'jump_range',
        'disconnect_range',

        'pre_ramp_up_thermal_energy_draw',
        'ramp_up_thermal_energy_draw',
        'in_flight_thermal_energy_draw',
        'ramp_down_thermal_energy_draw',
        'post_ramp_down_thermal_energy_draw',
    ];

    protected $casts = [
        'quantum_fuel_requirement' => 'double',
        'disconnect_range' => 'double',

        'pre_ramp_up_thermal_energy_draw' => 'double',
        'ramp_up_thermal_energy_draw' => 'double',
        'in_flight_thermal_energy_draw' => 'double',
        'ramp_down_thermal_energy_draw' => 'double',
        'post_ramp_down_thermal_energy_draw' => 'double',
    ];

    public function modes(): HasMany
    {
        return $this->hasMany(QuantumDriveMode::class);
    }
}
