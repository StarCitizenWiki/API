<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PowerPlant extends Model
{
    use HasFactory;

    protected $table = 'sc_item_power_plants';

    protected $fillable = [
        'item_uuid',
        'power_output',
    ];

    protected $casts = [
        'power_output' => 'double',
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
