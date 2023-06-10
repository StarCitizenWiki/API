<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Armor extends Model
{
    use HasFactory;

    protected $table = 'sc_item_armors';

    protected $fillable = [
        'item_uuid',
        'signal_infrared',
        'signal_electromagnetic',
        'signal_cross_section',
        'damage_physical',
        'damage_energy',
        'damage_distortion',
        'damage_thermal',
        'damage_biochemical',
        'damage_stun',
    ];

    protected $casts = [
        'signal_infrared' => 'double',
        'signal_electromagnetic' => 'double',
        'signal_cross_section' => 'double',
        'damage_physical' => 'double',
        'damage_energy' => 'double',
        'damage_distortion' => 'double',
        'damage_thermal' => 'double',
        'damage_biochemical' => 'double',
        'damage_stun' => 'double',
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
