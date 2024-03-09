<?php

namespace App\Models\SC\ItemSpecification;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HackingChip extends Model
{
    use HasFactory;

    protected $table = 'sc_item_hacking_chips';

    protected $fillable = [
        'item_uuid',
        'max_charges',
        'duration_multiplier',
        'error_chance',
    ];

    protected $casts = [
        'max_charges' => 'double',
        'duration_multiplier' => 'double',
        'error_chance' => 'double',
    ];
}
