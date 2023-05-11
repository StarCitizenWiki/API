<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use App\Models\StarCitizenUnpacked\ShipItem\Shield\ShieldAbsorption;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shield extends Model
{
    use HasFactory;

    protected $table = 'sc_item_shields';

    protected $fillable = [
        'item_uuid',
        'max_shield_health',
        'max_shield_regen',
        'decay_ratio',
        'downed_regen_delay',
        'damage_regen_delay',
        'max_reallocation',
        'reallocation_rate',
    ];

    protected $casts = [
        'max_shield_health' => 'double',
        'max_shield_regen' => 'double',
        'decay_ratio' => 'double',
        'downed_regen_delay' => 'double',
        'damage_regen_delay' => 'double',
        'max_reallocation' => 'double',
        'reallocation_rate' => 'double',
    ];
}
