<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Thruster extends Model
{
    use HasFactory;

    protected $table = 'sc_item_thrusters';

    protected $fillable = [
        'item_uuid',
        'thrust_capacity',
        'min_health_thrust_multiplier',
        'fuel_burn_per_10k_newton',
        'type',
    ];

    protected $casts = [
        'thrust_capacity' => 'double',
        'min_health_thrust_multiplier' => 'double',
        'fuel_burn_per_10k_newton' => 'double',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }
}
