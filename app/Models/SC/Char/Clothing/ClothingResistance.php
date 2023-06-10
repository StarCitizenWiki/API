<?php

declare(strict_types=1);

namespace App\Models\SC\Char\Clothing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClothingResistance extends Model
{
    use HasFactory;

    protected $table = 'sc_clothing_resistances';

    protected $fillable = [
        'item_uuid',
        'type',
        'multiplier',
        'threshold',
    ];

    protected $casts = [
        'multiplier' => 'double',
        'threshold' => 'double',
    ];
}
