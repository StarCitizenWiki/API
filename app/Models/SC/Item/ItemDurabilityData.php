<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDurabilityData extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_durability_data';

    public $timestamps = false;

    protected $fillable = [
        'health',
        'max_lifetime',
    ];

    protected $casts = [
        'health' => 'double',
        'max_lifetime' => 'double',
    ];
}
