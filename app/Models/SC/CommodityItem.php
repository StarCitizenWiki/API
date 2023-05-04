<?php

declare(strict_types=1);

namespace App\Models\SC;

use App\Models\SC\Item\Item;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function getNameAttribute($name)
    {
        return $this->item->name;
    }

    /**
     * @return BelongsToMany
     */
    public function translations(): BelongsToMany
    {
        return $this->item->translations();
    }
//
//    /**
//     * @return HasManyThrough
//     */
//    public function shops(): HasManyThrough
//    {
//        return $this->hasManyThrough(
//            Shop::class,
//            ShopItem::class,
//            'item_uuid',
//            'uuid',
//            'uuid',
//            'shop_uuid'
//        )
//            ->with(['items' => function ($query) {
//                return $query->where('uuid', $this->uuid);
//            }])
//            ->whereHas('items', function (Builder $query) {
//                return $query->where('uuid', $this->uuid);
//            });
//    }
}
