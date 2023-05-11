<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelTank extends Model
{
    use HasFactory;

    protected $table = 'sc_item_fuel_tanks';

    protected $fillable = [
        'item_uuid',
        'fill_rate',
        'drain_rate',
        'capacity',
    ];

    protected $casts = [
        'fill_rate' => 'double',
        'drain_rate' => 'double',
        'capacity' => 'double',
    ];
}
