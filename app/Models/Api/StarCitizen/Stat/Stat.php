<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Stat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Stats
 */
class Stat extends Model
{
    use HasFactory;

    protected $perPage = 10;

    protected $fillable = [
        'funds',
        'fans',
        'fleet',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
