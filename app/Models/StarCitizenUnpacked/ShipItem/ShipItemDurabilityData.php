<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipItemDurabilityData extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_item_durability_data';

    public $timestamps = false;

    protected $fillable = [
        'health',
        'max_lifetime',
    ];

    protected $casts = [
        'health' => 'double',
        'max_lifetime' => 'double',
    ];

    public function shipItem(): BelongsTo
    {
        return $this->belongsTo(ShipItem::class);
    }
}
