<?php

declare(strict_types=1);

namespace App\Models\SC\Shop;

use App\Events\ModelUpdating;
use App\Models\SC\Item\Item;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ShopItem extends Pivot
{
    use ModelChangelog;

    protected $primaryKey = 'node_uuid';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $table = 'sc_shop_item';

    protected $fillable = [
        'item_id',
        'shop_id',
        'item_uuid',
        'shop_uuid',
        'node_uuid',
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
        'version',
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

    protected $appends = [
        'offsettedPrice',
        'priceRange',
    ];

    protected $with = [
        'rental'
    ];

    public function getOffsettedPriceAttribute()
    {
        if ($this->base_price_offset === null || $this->base_price_offset === 0) {
            return $this->base_price;
        }

        return $this->base_price * (1 + ($this->base_price_offset / 100));
    }

    public function getPriceRangeAttribute(): array
    {
        return [
            'min' => $this->offsettedPrice * (1 - ($this->max_discount / 100)),
            'max' => $this->offsettedPrice * (1 + ($this->max_premium / 100)),
        ];
    }

    public function getPrice1Attribute()
    {
        return round(($this->offsetted_price / 100) * $this->rental->percentage_1);
    }

    public function getPrice3Attribute()
    {
        return round(($this->offsetted_price / 100) * 3 * $this->rental->percentage_3);
    }

    public function getPrice7Attribute()
    {
        return round(($this->offsetted_price / 100) * 7 * $this->rental->percentage_7);
    }

    public function getPrice30Attribute()
    {
        return round(($this->offsetted_price / 100) * 30 * $this->rental->percentage_30);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_uuid', 'uuid');
    }

    public function rental(): HasOne
    {
        return $this->hasOne(ShopItemRental::class, 'item_uuid', 'item_uuid')->withDefault([
            'percentage_1' => 1,
            'percentage_3' => 1,
            'percentage_7' => 1,
            'percentage_30' => 1,
        ]);
    }
}
