<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemHeatData extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_heat_data';

    public $timestamps = false;

    protected $fillable = [
        'temperature_to_ir',
        'overpower_heat',
        'overclock_threshold_min',
        'overclock_threshold_max',
        'thermal_energy_base',
        'thermal_energy_draw',
        'thermal_conductivity',
        'specific_heat_capacity',
        'mass',
        'surface_area',
        'start_cooling_temperature',
        'max_cooling_rate',
        'max_temperature',
        'min_temperature',
        'overheat_temperature',
        'recovery_temperature',
        'misfire_min_temperature',
        'misfire_max_temperature',
    ];

    protected $casts = [
        'temperature_to_ir' => 'double',
        'overpower_heat' => 'double',
        'overclock_threshold_min' => 'double',
        'overclock_threshold_max' => 'double',
        'thermal_energy_base' => 'double',
        'thermal_energy_draw' => 'double',
        'thermal_conductivity' => 'double',
        'specific_heat_capacity' => 'double',
        'mass' => 'double',
        'surface_area' => 'double',
        'start_cooling_temperature' => 'double',
        'max_cooling_rate' => 'double',
        'max_temperature' => 'double',
        'min_temperature' => 'double',
        'overheat_temperature' => 'double',
        'recovery_temperature' => 'double',
        'misfire_min_temperature' => 'double',
        'misfire_max_temperature' => 'double',
    ];
}
