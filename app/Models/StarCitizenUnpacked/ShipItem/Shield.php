<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shield extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_shields';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'health',
        'regeneration',
        'downed_delay',
        'damage_delay',
        'min_physical_absorption',
        'max_physical_absorption',
        'min_energy_absorption',
        'max_energy_absorption',
        'min_distortion_absorption',
        'max_distortion_absorption',
        'min_thermal_absorption',
        'max_thermal_absorption',
        'min_biochemical_absorption',
        'max_biochemical_absorption',
        'min_stun_absorption',
        'max_stun_absorption',
    ];

    protected $casts = [
        'health' => 'double',
        'regeneration' => 'double',
        'downed_delay' => 'double',
        'damage_delay' => 'double',
        'min_physical_absorption' => 'double',
        'max_physical_absorption' => 'double',
        'min_energy_absorption' => 'double',
        'max_energy_absorption' => 'double',
        'min_distortion_absorption' => 'double',
        'max_distortion_absorption' => 'double',
        'min_thermal_absorption' => 'double',
        'max_thermal_absorption' => 'double',
        'min_biochemical_absorption' => 'double',
        'max_biochemical_absorption' => 'double',
        'min_stun_absorption' => 'double',
        'max_stun_absorption' => 'double',
    ];
}
