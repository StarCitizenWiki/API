<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MiningLaser extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_mining_lasers';

    protected $fillable = [
        'ship_item_id',
        'uuid',
        'hit_type',
        'energy_rate',
        'full_damage_range',
        'zero_damage_range',
        'heat_per_second',
        'damage',

        'modifier_resistance',
        'modifier_instability',
        'modifier_charge_window_size',
        'modifier_charge_window_rate',
        'modifier_shatter_damage',
        'modifier_catastrophic_window_rate',

        'consumable_slots',
    ];

    protected $casts = [
        'energy_rate' => 'double',
        'full_damage_range' => 'double',
        'zero_damage_range' => 'double',
        'heat_per_second' => 'double',

        'modifier_resistance' => 'double',
        'modifier_instability' => 'double',
        'modifier_charge_window_size' => 'double',
        'modifier_charge_window_rate' => 'double',
        'modifier_shatter_damage' => 'double',
        'modifier_catastrophic_window_rate' => 'double',
    ];
}
