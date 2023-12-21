<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\Missile;

use App\Models\SC\CommodityItem;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Missile extends CommodityItem
{
    use HasFactory;
    use HasDescriptionDataTrait;

    protected $table = 'sc_item_missiles';

    protected $with = [
        'damages',
    ];

    protected $fillable = [
        'item_uuid',
        'signal_type',
        'lock_time',
        'lock_range_max',
        'lock_range_min',
        'lock_angle',
        'tracking_signal_min',
        'speed',
        'fuel_tank_size',
        'explosion_radius_min',
        'explosion_radius_max',
    ];

    protected $casts = [
        'lock_time' => 'double',
        'lock_range_max' => 'double',
        'lock_range_min' => 'double',
        'lock_angle' => 'double',
        'tracking_signal_min' => 'double',
        'speed' => 'double',
        'fuel_tank_size' => 'double',
        'explosion_radius_min' => 'double',
        'explosion_radius_max' => 'double',
    ];

    public function damages(): HasMany
    {
        return $this->hasMany(MissileDamage::class, 'missile_id');
    }

    public function getClusterSizeAttribute(): int
    {
        $cluster = $this->item->ports()->count();
        return $cluster === 0 ? 1 : $cluster;
    }

    public function getDamageAttribute(): float
    {
        return $this->damages->where('name', 'physical')->reduce(function ($carry, $item) {
            return $carry + $item->damage;
        }, 0);
    }
}
