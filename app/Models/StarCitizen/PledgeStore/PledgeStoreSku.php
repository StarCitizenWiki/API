<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\PledgeStore;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PledgeStoreSku extends Model
{
    use HasFactory;

    protected $table = 'pledge_store_skus';

    protected $fillable = [
        'cig_id',
        'slug',
        'name',
        'title',
        'subtitle',
        'url',
        'product_id',
        'public_type_code',
        'parent_product_slug',
        'parent_product_name',
        'is_warbond',
        'is_package',
        'is_vip',
        'customizable',
        'native_price',
        'native_discounted',
        'discount_description',
        'stock_available',
        'stock_unlimited',
        'stock_back_order',
        'stock_qty',
        'stock_back_order_qty',
        'stock_level',
        'tags',
        'ships',
    ];

    protected $casts = [
        'cig_id' => 'integer',
        'product_id' => 'integer',
        'is_warbond' => 'boolean',
        'is_package' => 'boolean',
        'is_vip' => 'boolean',
        'customizable' => 'boolean',
        'native_price' => 'integer',
        'native_discounted' => 'integer',
        'stock_available' => 'boolean',
        'stock_unlimited' => 'boolean',
        'stock_back_order' => 'boolean',
        'stock_qty' => 'integer',
        'stock_back_order_qty' => 'integer',
        'tags' => 'array',
        'ships' => 'array',
    ];

    /**
     * Tracked fields that trigger a history entry when changed.
     *
     * @return array<string, mixed>
     */
    public function trackedAttributes(): array
    {
        return [
            'native_price' => $this->native_price,
            'native_discounted' => $this->native_discounted,
            'discount_description' => $this->discount_description,
            'stock_available' => $this->stock_available,
            'stock_level' => $this->stock_level,
            'stock_qty' => $this->stock_qty,
            'tags' => $this->tags,
        ];
    }

    public function history(): HasMany
    {
        return $this->hasMany(PledgeStoreSkuHistory::class);
    }

    public function latestHistory(): HasOne
    {
        return $this->hasOne(PledgeStoreSkuHistory::class)->latestOfMany();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PledgeStoreProduct::class, 'product_id', 'cig_id');
    }

    public function scopeStandaloneShips($query)
    {
        return $query->where('product_id', 72);
    }

    public function scopePaints($query)
    {
        return $query->where('product_id', 268);
    }

    public function scopeGear($query)
    {
        return $query->where('product_id', 289);
    }

    public function scopeGamePackages($query)
    {
        return $query->whereIn('product_id', [9, 45, 46]);
    }

    public function scopeAvailable($query)
    {
        return $query->where('stock_available', true);
    }

    public function scopeDiscounted($query)
    {
        return $query->whereNotNull('native_discounted');
    }

    public function scopeWarbond($query)
    {
        return $query->where('is_warbond', true);
    }
}
