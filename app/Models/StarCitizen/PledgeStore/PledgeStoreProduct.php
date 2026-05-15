<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\PledgeStore;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PledgeStoreProduct extends Model
{
    use HasFactory;

    protected $table = 'pledge_store_products';

    protected $fillable = [
        'cig_id',
        'title',
    ];

    protected $casts = [
        'cig_id' => 'integer',
    ];

    public function skus(): HasMany
    {
        return $this->hasMany(PledgeStoreSku::class, 'product_id', 'cig_id');
    }
}
