<?php

namespace App\Models\SC\Ammunition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmmunitionDamageFalloff extends Model
{
    use HasFactory;

    protected $table = 'sc_ammunition_damage_falloffs';

    protected $fillable = [
        'ammunition_uuid',
        'type',
        'physical',
        'energy',
        'distortion',
        'thermal',
        'biochemical',
        'stun',
    ];

    protected $casts = [
        'physical' => 'double',
        'energy' => 'double',
        'distortion' => 'double',
        'thermal' => 'double',
        'biochemical' => 'double',
        'stun' => 'double',
    ];
}
