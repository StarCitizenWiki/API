<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuantumInterdictionGenerator extends Model
{
    use HasFactory;

    protected $table = 'sc_item_qigs';

    protected $fillable = [
        'item_uuid',
        'jammer_range',
        'interdiction_range',
        'charge_duration',
        'discharge_duration',
        'cooldown_duration',
    ];

    protected $casts = [
        'jammer_range' => 'double',
        'interdiction_range' => 'double',
        'charge_duration' => 'double',
        'discharge_duration' => 'double',
        'cooldown_duration' => 'double',
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
