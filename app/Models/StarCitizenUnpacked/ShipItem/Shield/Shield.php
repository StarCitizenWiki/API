<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Shield;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shield extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_shields';

    protected $with = [
        'absorptions'
    ];

    protected $fillable = [
        'ship_item_id',
        'uuid',
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

    public function absorptions(): HasMany
    {
        return $this->hasMany(ShieldAbsorption::class, 'ship_shield_id', 'id');
    }
}
