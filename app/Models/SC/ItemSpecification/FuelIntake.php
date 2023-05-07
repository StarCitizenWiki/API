<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FuelIntake extends Model
{
    use HasFactory;

    protected $table = 'sc_item_fuel_intakes';

    protected $fillable = [
        'item_uuid',
        'fuel_push_rate',
        'minimum_rate',
    ];

    protected $casts = [
        'fuel_push_rate' => 'double',
        'minimum_rate' => 'double',
    ];
}
