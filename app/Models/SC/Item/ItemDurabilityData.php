<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemDurabilityData extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_durability_data';

    protected $fillable = [
        'health',
        'max_lifetime',
        'repairable',
        'salvageable',
    ];

    protected $casts = [
        'health' => 'double',
        'max_lifetime' => 'double',
        'repairable' => 'boolean',
        'salvageable' => 'boolean',
    ];
}
