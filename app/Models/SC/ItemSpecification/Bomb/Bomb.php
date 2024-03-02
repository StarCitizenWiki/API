<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\Bomb;

use App\Models\SC\CommodityItem;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bomb extends CommodityItem
{
    use HasFactory;
    use HasDescriptionDataTrait;

    protected $table = 'sc_item_bombs';

    protected $with = [
        'damages',
    ];

    protected $fillable = [
        'item_uuid',
        'arm_time',
        'ignite_time',
        'collision_delay_time',
        'explosion_safety_distance',
        'explosion_radius_min',
        'explosion_radius_max',
    ];

    protected $casts = [
        'arm_time' => 'double',
        'ignite_time' => 'double',
        'collision_delay_time' => 'double',
        'explosion_safety_distance' => 'double',
        'explosion_radius_min' => 'double',
        'explosion_radius_max' => 'double',
    ];

    public function damages(): HasMany
    {
        return $this->hasMany(BombDamage::class, 'bomb_id');
    }

    public function getDamageAttribute(): float
    {
        return $this->damages->where('name', 'physical')->reduce(function ($carry, $item) {
            return $carry + $item->damage;
        }, 0);
    }
}
