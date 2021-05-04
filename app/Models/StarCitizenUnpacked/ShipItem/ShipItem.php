<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\CommodityItem;
use App\Models\StarCitizenUnpacked\ShipItem\QuantumDrive\QuantumDrive;
use App\Models\StarCitizenUnpacked\ShipItem\Shield\Shield;
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
        'version',
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

    protected $with = [
        'item',
        'heatData',
        'powerData',
        'distortionData',
    ];

    public function heatData(): HasOne
    {
        return $this->hasOne(ShipItemHeatData::class, 'ship_item_id');
    }

    public function powerData(): HasOne
    {
        return $this->hasOne(ShipItemPowerData::class, 'ship_item_id');
    }

    public function distortionData(): HasOne
    {
        return $this->hasOne(ShipItemDistortionData::class, 'ship_item_id');
    }

    public function durabilityData(): HasOne
    {
        return $this->hasOne(ShipItemDurabilityData::class, 'ship_item_id');
    }

    /**
     * @return HasOne
     */
    public function itemSpecification(): HasOne
    {
        switch ($this->item->type) {
            case 'Cooler':
                return $this->hasOne(Cooler::class, 'uuid', 'uuid');
            case 'PowerPlant':
                return $this->hasOne(PowerPlant::class, 'uuid', 'uuid');
            case 'QuantumDrive':
                return $this->hasOne(QuantumDrive::class, 'uuid', 'uuid');
            case 'Shield':
                return $this->hasOne(Shield::class, 'uuid', 'uuid');
            case 'WeaponGun':
                return $this->hasOne(Weapon\Weapon::class, 'uuid', 'uuid');
            default:
                throw new ModelNotFoundException();
        }
    }
}
