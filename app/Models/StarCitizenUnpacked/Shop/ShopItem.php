<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\Shop;

use App\Events\ModelUpdating;
use App\Models\StarCitizenUnpacked\Item;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ShopItem extends Pivot
{
    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $table = 'star_citizen_unpacked_shop_item';

    protected $fillable = [
        'item_id',
        'shop_id',
        'item_uuid',
        'shop_uuid',
        'base_price',
        'base_price_offset',
        'max_discount',
        'max_premium',
        'inventory',
        'optimal_inventory',
        'max_inventory',
        'auto_restock',
        'auto_consume',
        'refresh_rate',
        'buyable',
        'sellable',
        'rentable',
    ];

    protected $casts = [
        'base_price' => 'double',
        'base_price_offset' => 'double',
        'max_discount' => 'double',
        'max_premium' => 'double',
        'inventory' => 'double',
        'optimal_inventory' => 'double',
        'max_inventory' => 'double',
        'auto_restock' => 'bool',
        'auto_consume' => 'bool',
        'refresh_rate' => 'double',
        'buyable' => 'bool',
        'sellable' => 'bool',
        'rentable' => 'bool',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_uuid', 'uuid');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_uuid', 'uuid');
    }
}
