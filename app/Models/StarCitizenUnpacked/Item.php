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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends HasTranslations implements HasChangelogsInterface
{
    use HasFactory;
    use HasModelChangelogTrait;

    protected $table = 'star_citizen_unpacked_items';

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'sub_type',
        'manufacturer',
        'size',
        'version',
    ];

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(
            ItemTranslation::class,
            'item_uuid',
            'uuid'
        );
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(
            Shop::class,
            'star_citizen_unpacked_shop_item'
        )
            ->using(ShopItem::class)
            ->as('shop_data')
            ->withPivot(
                'item_uuid',
                'shop_uuid',
                'base_price',
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
            )
            ->with(['items' => function ($query) {
                return $query->where('uuid', $this->uuid);
            }])
            ->whereHas('items', function (Builder $query) {
                return $query->where('uuid', $this->uuid);
            });
    }
}
