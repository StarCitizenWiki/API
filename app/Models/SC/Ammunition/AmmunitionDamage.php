<?php

namespace App\Models\SC\Ammunition;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmmunitionDamage extends Model
{
    use HasFactory;

    protected $table = 'sc_ammunition_damages';

    protected $fillable = [
        'ammunition_uuid',
        'type',
        'name',
        'damage',
    ];

    protected $casts = [
        'damage' => 'double',
    ];
}
