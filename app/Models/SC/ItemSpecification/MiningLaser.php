<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\CommodityItem;
use App\Models\SC\Item\Item;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MiningLaser extends CommodityItem
{
    use HasFactory;
    use HasDescriptionDataTrait;

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

    protected $with = [
        'modifiers'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }

    public function modifiers(): HasManyThrough
    {
        return $this->descriptionData()->whereNotIn(
            'sc_item_description_data.name',
            [
                'Collection Point Radius',
                'Collection Throughput',
                'Extraction Laser Power',
                'Item Type',
                'Manufacturer',
                'Maximum Range',
                'Mining Laser Power',
                'Module Slots',
                'Optimal Range',
                'Size',
            ]
        );
    }

    public function getMiningLaserPowerAttribute()
    {
        return $this->getDescriptionDatum('Mining Laser Power');
    }

    public function getExtractionLaserPowerAttribute()
    {
        return $this->getDescriptionDatum('Extraction Laser Power');
    }
}
