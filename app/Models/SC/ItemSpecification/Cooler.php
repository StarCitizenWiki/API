<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cooler extends Model
{
    use HasFactory;

    protected $table = 'sc_item_coolers';

    protected $fillable = [
        'item_uuid',
        'cooling_rate',
        'suppression_ir_factor',
        'suppression_heat_factor',
    ];

    protected $casts = [
        'cooling_rate' => 'double',
        'suppression_ir_factor' => 'double',
        'suppression_heat_factor' => 'double',
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
