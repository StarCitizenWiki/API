<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\PledgeStore;

use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PledgeStoreSku extends Model
{
    use HasFactory;

    protected $table = 'pledge_store_skus';

    protected $fillable = [
        'cig_id',
        'name',
        'url',
        'product_id',
        'is_warbond',
        'is_package',
        'native_price',
        'native_discounted',
        'discount_description',
        'stock_available',
        'tags',
        'data',
        'images',
    ];

    protected $casts = [
        'cig_id' => 'integer',
        'product_id' => 'integer',
        'is_warbond' => 'boolean',
        'is_package' => 'boolean',
        'native_price' => 'integer',
        'native_discounted' => 'integer',
        'stock_available' => 'boolean',
        'tags' => 'array',
        'data' => AsCollection::class,
        'images' => 'array',
    ];

    /**
     * Build the full source image URL from the stored media key.
     */
    public function getThumbnailSourceUrl(): ?string
    {
        $key = $this->images['media_key'] ?? null;

        if ($key === null) {
            return null;
        }

        return "https://media.robertsspaceindustries.com/{$key}/source.jpg";
    }

    /**
     * Build a specific image size URL from the stored media key.
     */
    public function getThumbnailUrl(string $size = 'store_small'): ?string
    {
        $key = $this->images['media_key'] ?? null;

        if ($key === null) {
            return null;
        }

        return "https://media.robertsspaceindustries.com/{$key}/{$size}.jpg";
    }

    /**
     * Get a value from the raw data payload using dot notation.
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        if ($this->data === null) {
            return $default;
        }

        return data_get($this->data, $key, $default);
    }

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

    public function ships(): BelongsToMany
    {
        return $this->belongsToMany(
            Vehicle::class,
            'pledge_store_sku_ship',
            'pledge_store_sku_id',
            'shipmatrix_vehicle_id',
        );
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
