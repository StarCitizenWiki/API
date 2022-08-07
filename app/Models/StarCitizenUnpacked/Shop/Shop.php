<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\Shop;

use App\Events\ModelUpdating;
use App\Models\StarCitizenUnpacked\Item;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shop extends Model
{
    use HasFactory;
    use ModelChangelog;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $table = 'star_citizen_unpacked_shops';

    protected $fillable = [
        'uuid',
        'name_raw',
        'name',
        'position',
        'profit_margin',
        'version',
    ];

    protected $casts = [
        'profit_margin' => 'double',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            Item::class,
            'star_citizen_unpacked_shop_item'
        )
            ->using(ShopItem::class)
            ->as('shop_data')
            ->withPivot(
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
                'version',
            );
    }
}
