<?php

declare(strict_types=1);

namespace App\Models\SC;

use App\Models\SC\Item\Item;
use App\Models\SC\Shop\Shop;
use App\Models\SC\Shop\ShopItem;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

abstract class CommodityItem extends HasTranslations
{
    use HasFactory;

    protected $with = [
        'item'
    ];

    /**
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_uuid', 'uuid');
    }

    public function getNameAttribute()
    {
        return $this->item->name;
    }

    public function getVersionAttribute()
    {
        return $this->item->version;
    }

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->item->translations();
    }

    /**
     * @return HasManyThrough
     */
    public function shops(): HasManyThrough
    {
        return $this->hasManyThrough(
            Shop::class,
            ShopItem::class,
            'item_uuid',
            'uuid',
            'uuid',
            'shop_uuid'
        );
    }
}
