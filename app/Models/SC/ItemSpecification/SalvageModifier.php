<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalvageModifier extends Model
{
    use HasFactory;

    protected $table = 'sc_item_salvage_modifiers';

    protected $fillable = [
        'item_uuid',
        'salvage_speed_multiplier',
        'radius_multiplier',
        'extraction_efficiency',
    ];

    protected $casts = [
        'salvage_speed_multiplier' => 'double',
        'radius_multiplier' => 'double',
        'extraction_efficiency' => 'double',
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
