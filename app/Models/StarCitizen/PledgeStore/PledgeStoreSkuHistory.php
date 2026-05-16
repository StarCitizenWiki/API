<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\PledgeStore;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PledgeStoreSkuHistory extends Model
{
    use HasFactory;

    protected $table = 'pledge_store_sku_history';

    protected $fillable = [
        'pledge_store_sku_id',
        'data',
        'created_at',
    ];

    protected $casts = [
        'data' => AsCollection::class,
    ];

    public function sku(): BelongsTo
    {
        return $this->belongsTo(PledgeStoreSku::class, 'pledge_store_sku_id');
    }

    /**
     * Get a value from the snapshot data using dot notation.
     */
    public function getData(string $key, mixed $default = null): mixed
    {
        if ($this->data === null) {
            return $default;
        }

        return data_get($this->data, $key, $default);
    }
}
