<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use App\Models\SC\CargoGrid;
use App\Models\SC\CommodityItem;
use App\Models\SC\CounterMeasure;
use App\Models\SC\FuelIntake;
use App\Models\SC\FuelTank;
use App\Models\SC\PersonalInventory;
use App\Models\SC\Radar;
use App\Models\SC\ShipItem\Cooler;
use App\Models\SC\ShipItem\MiningLaser;
use App\Models\SC\ShipItem\PowerPlant;
use App\Models\SC\ShipItem\QuantumDrive\QuantumDrive;
use App\Models\SC\ShipItem\SelfDestruct;
use App\Models\SC\ShipItem\Shield\Shield;
use App\Models\SC\ShipItem\ShipItemDistortionData;
use App\Models\SC\ShipItem\ShipItemDurabilityData;
use App\Models\SC\ShipItem\ShipItemHeatData;
use App\Models\SC\ShipItem\ShipItemPowerData;
use App\Models\SC\ShipItem\Weapon;
use App\Models\SC\Thruster;
use App\Models\SC\Turret;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class VehicleItem extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_vehicle_items';

    protected $fillable = [
        'item_uuid',
        'grade',
        'class',
        'type',
    ];

    protected $with = [
        'item',
    ];



    public function hasSpecification(): bool {
        $supportedTypes = [
            'Cargo',
            'CargoGrid',
            'Cooler',
            'PowerPlant',
            'QuantumDrive',
            'QuantumFuelTank',
            'FuelIntake',
            'Shield',
            'WeaponGun',
            'WeaponMining',
            'WeaponDefensive',
            'MissileLauncher',
            'Missile',
            'Torpedo',
            'MainThruster',
            'ManneuverThruster',
            'SelfDestruct',
            'Radar',
        ];

        return $this->item !== null && $this->item->uuid !== null && in_array($this->item->type, $supportedTypes, true);
    }

    /**
     * @return HasOne
     */
    public function specification(): ?HasOne
    {
        switch ($this->item->type) {
//            case 'Container':
            case 'Cargo':
            case 'CargoGrid':
                return $this->hasOne(CargoGrid::class, 'uuid', 'uuid');
            case 'Cooler':
                return $this->hasOne(Cooler::class, 'uuid', 'uuid');
            case 'PowerPlant':
                return $this->hasOne(PowerPlant::class, 'uuid', 'uuid');
            case 'QuantumDrive':
                return $this->hasOne(QuantumDrive::class, 'uuid', 'uuid');
            case 'FuelTank':
            case 'QuantumFuelTank':
                return $this->hasOne(FuelTank::class, 'uuid', 'uuid');
            case 'FuelIntake':
                return $this->hasOne(FuelIntake::class, 'uuid', 'uuid');
            case 'Shield':
                return $this->hasOne(Shield::class, 'uuid', 'uuid');
//            case 'Turret':
//            case 'TurretBase':
//            case 'ToolArm':
//            case 'MiningArm':
//            case 'WeaponMount':
//                return $this->hasOne(Turret::class, 'uuid', 'uuid');
            case 'WeaponGun':
                return $this->hasOne(Weapon\Weapon::class, 'uuid', 'uuid');
            case 'WeaponMining':
                return $this->hasOne(MiningLaser::class, 'uuid', 'uuid');
            case 'WeaponDefensive':
                return $this->hasOne(CounterMeasure::class, 'uuid', 'uuid');
            case 'MissileLauncher':
                return $this->hasOne(Weapon\MissileRack::class, 'uuid', 'uuid');
            case 'Missile':
            case 'Torpedo':
                return $this->hasOne(Weapon\Missile::class, 'uuid', 'uuid');
            case 'MainThruster':
            case 'ManneuverThruster':
                return $this->hasOne(Thruster::class, 'uuid', 'uuid');
            case 'SelfDestruct':
                return $this->hasOne(SelfDestruct::class, 'uuid', 'uuid');
            case 'Radar':
                return $this->hasOne(Radar::class, 'uuid', 'uuid');
//            case 'PersonalInventory':
//                return $this->hasOne(PersonalInventory::class, 'uuid', 'uuid');
            default:
                throw new ModelNotFoundException();
        }
    }

    public function ports():HasMany
    {
        return $this->item->ports()
            ->where('name', 'NOT LIKE', '%access%')
            ->where('name', 'NOT LIKE', '%hud%');
    }
}
