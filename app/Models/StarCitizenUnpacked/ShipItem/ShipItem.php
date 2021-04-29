<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShipItem extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_items';

    protected $fillable = [
        'uuid',
        'grade',
        'class',
        'type',
        'health',
        'lifetime',
        'power_base',
        'power_draw',
        'thermal_energy_base',
        'thermal_energy_draw',
        'cooling_rate',
    ];

    protected $casts = [
        'health' => 'double',
        'lifetime' => 'double',
        'power_base' => 'double',
        'power_draw' => 'double',
        'thermal_energy_base' => 'double',
        'thermal_energy_draw' => 'double',
        'cooling_rate' => 'double',
    ];

    /**
     * @return HasOne
     */
    public function itemSpecification(): HasOne
    {
        switch ($this->type) {
            case 'Cooler':
                return $this->hasOne(Cooler::class, 'uuid', 'uuid');
            case 'Power Plant':
                return $this->hasOne(PowerPlant::class, 'uuid', 'uuid');
            case 'Quantum Drive':
                return $this->hasOne(QuantumDrive::class, 'uuid', 'uuid');
            case 'Shield Generator':
                return $this->hasOne(Shield::class, 'uuid', 'uuid');
            default:
                throw new ModelNotFoundException();
        }
    }
}
