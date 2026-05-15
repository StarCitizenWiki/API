<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\PledgeStore;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PledgeStoreSkuHistory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'pledge_store_sku_history';

    protected $fillable = [
        'pledge_store_sku_id',
        'native_price',
        'native_discounted',
        'discount_description',
        'stock_available',
        'stock_level',
        'stock_qty',
        'tags',
        'created_at',
    ];

    protected $casts = [
        'native_price' => 'integer',
        'native_discounted' => 'integer',
        'stock_available' => 'boolean',
        'stock_qty' => 'integer',
        'tags' => 'array',
        'created_at' => 'datetime',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $history): void {
            if ($history->created_at === null) {
                $history->created_at = now();
            }
        });
    }

    public function sku(): BelongsTo
    {
        return $this->belongsTo(PledgeStoreSku::class, 'pledge_store_sku_id');
    }
}
