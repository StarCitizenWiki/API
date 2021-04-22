<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Contracts\HasChangelogsInterface;
use App\Events\ModelUpdating;
use App\Models\StarCitizenUnpacked\Shop\Shop;
use App\Models\StarCitizenUnpacked\Shop\ShopItem;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

abstract class CommodityItem extends HasTranslations implements HasChangelogsInterface
{
    use HasModelChangelogTrait;
    use HasFactory;

    protected $with = [
        'item'
    ];

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * @return BelongsTo
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'uuid', 'uuid');
    }

    public function getNameAttribute()
    {
        return $this->item->name;
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
        )
            ->with(['items' => function ($query) {
                return $query->where('uuid', $this->uuid);
            }])
            ->whereHas('items', function (Builder $query) {
                return $query->where('uuid', $this->uuid);
            });
    }
}
