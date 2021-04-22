<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\CharArmor;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CharArmor extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_char_armor';

    protected $fillable = [
        'uuid',
        'armor_type',
        'temp_resistance_min',
        'temp_resistance_max',
        'resistance_physical_multiplier',
        'resistance_physical_threshold',
        'resistance_energy_multiplier',
        'resistance_energy_threshold',
        'resistance_distortion_multiplier',
        'resistance_distortion_threshold',
        'resistance_thermal_multiplier',
        'resistance_thermal_threshold',
        'resistance_biochemical_multiplier',
        'resistance_biochemical_threshold',
        'resistance_stun_multiplier',
        'resistance_stun_threshold',
    ];

    protected $casts = [
        'temp_resistance_min' => 'double',
        'temp_resistance_max' => 'double',
        'resistance_physical_multiplier' => 'double',
        'resistance_physical_threshold' => 'double',
        'resistance_energy_multiplier' => 'double',
        'resistance_energy_threshold' => 'double',
        'resistance_distortion_multiplier' => 'double',
        'resistance_distortion_threshold' => 'double',
        'resistance_thermal_multiplier' => 'double',
        'resistance_thermal_threshold' => 'double',
        'resistance_biochemical_multiplier' => 'double',
        'resistance_biochemical_threshold' => 'double',
        'resistance_stun_multiplier' => 'double',
        'resistance_stun_threshold' => 'double',
    ];

    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(
            CharArmorAttachment::class,
            'star_citizen_unpacked_char_armor_attachment',
        );
    }
}
