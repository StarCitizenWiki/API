<?php

namespace App\Models\SC\Ammunition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmmunitionPiercability extends Model
{
    use HasFactory;

    protected $table = 'sc_ammunition_piercabilities';

    protected $fillable = [
        'ammunition_uuid',
        'damage_falloff_level_1',
        'damage_falloff_level_2',
        'damage_falloff_level_3',
        'max_penetration_thickness',
    ];

    protected $casts = [
        'damage_falloff_level_1' => 'double',
        'damage_falloff_level_2' => 'double',
        'damage_falloff_level_3' => 'double',
        'max_penetration_thickness' => 'double',
    ];
}
