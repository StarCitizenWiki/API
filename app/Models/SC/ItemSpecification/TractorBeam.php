<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TractorBeam extends Model
{
    use HasFactory;

    protected $table = 'sc_item_tractorbeams';

    protected $fillable = [
        'item_uuid',
        'min_force',
        'max_force',
        'min_distance',
        'max_distance',
        'full_strength_distance',
        'max_angle',
        'max_volume',
        'volume_force_coefficient',
        'tether_break_time',
        'safe_range_value_factor',
    ];

    protected $casts = [
        'min_force' => 'double',
        'max_force' => 'double',
        'min_distance' => 'double',
        'max_distance' => 'double',
        'full_strength_distance' => 'double',
        'max_angle' => 'double',
        'max_volume' => 'double',
        'volume_force_coefficient' => 'double',
        'tether_break_time' => 'double',
        'safe_range_value_factor' => 'double',
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
