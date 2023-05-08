<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\MiningLaser;

use App\Models\SC\CommodityItem;
use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiningLaser extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_mining_lasers';

    protected $fillable = [
        'item_uuid',
        'power_transfer',
        'optimal_range',
        'maximum_range',
        'extraction_throughput',
        'module_slots',
    ];

    protected $casts = [
        'power_transfer' => 'double',
        'optimal_range' => 'double',
        'maximum_range' => 'double',
        'extraction_throughput' => 'double',
        'module_slots' => 'int',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(MiningLaserModifier::class);
    }
}
