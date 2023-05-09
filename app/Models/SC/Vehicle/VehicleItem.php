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

    public function ports():HasMany
    {
        return $this->item->ports()
            ->where('name', 'NOT LIKE', '%access%')
            ->where('name', 'NOT LIKE', '%hud%');
    }
}
