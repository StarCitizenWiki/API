<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemPowerData extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_power_data';

    protected $fillable = [
        'power_base',
        'power_draw',
        'throttleable',
        'overclockable',
        'overclock_threshold_min',
        'overclock_threshold_max',
        'overclock_performance',
        'overpower_performance',
        'power_to_em',
        'decay_rate_em',
    ];

    protected $casts = [
        'power_base' => 'double',
        'power_draw' => 'double',
        'throttleable' => 'boolean',
        'overclockable' => 'boolean',
        'overclock_threshold_min' => 'double',
        'overclock_threshold_max' => 'double',
        'overclock_performance' => 'double',
        'overpower_performance' => 'double',
        'power_to_em' => 'double',
        'decay_rate_em' => 'double',
    ];
}
