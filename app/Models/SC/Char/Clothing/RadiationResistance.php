<?php

namespace App\Models\SC\Char\Clothing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadiationResistance extends Model
{
    use HasFactory;

    protected $table = 'sc_clothing_radiation_resistances';

    protected $fillable = [
        'item_uuid',
        'maximum_radiation_capacity',
        'radiation_dissipation_rate',
    ];

    protected $casts = [
        'maximum_radiation_capacity' => 'double',
        'radiation_dissipation_rate' => 'double',
    ];
}
