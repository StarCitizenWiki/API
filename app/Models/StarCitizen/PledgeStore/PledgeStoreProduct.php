<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\PledgeStore;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
