<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipItemPowerData extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_item_power_data';

    public $timestamps = false;

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

    public function shipItem(): BelongsTo
    {
        return $this->belongsTo(ShipItem::class);
    }
}
