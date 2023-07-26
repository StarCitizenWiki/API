<?php

declare(strict_types=1);

namespace App\Models\SC\Shop;

use App\Events\ModelUpdating;
use App\Models\SC\Item\Item;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shop extends Model
{
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'name_raw',
            static function (Builder $builder) {
                $builder->where('name_raw', 'NOT LIKE', '%Levski%')
                ->where('name_raw', 'NOT LIKE', '%IAE Expo%');
            }
        );
    }

    protected $table = 'sc_shops';

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
            'sc_shop_item'
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
