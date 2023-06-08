<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use App\Events\ModelUpdating;
use App\Models\SC\CommodityItem;
use App\Models\SC\Item\Item;
use App\Models\SC\Item\ItemHeatData;
use App\Models\SC\Item\ItemPort;
use App\Models\SC\Item\ItemPowerData;
use App\Models\SC\ItemSpecification\Armor;
use App\Models\SC\ItemSpecification\FlightController;
use App\Models\SC\ItemSpecification\QuantumDrive\QuantumDrive;
use App\Models\SC\ItemSpecification\Shield;
use App\Models\SC\ItemSpecification\Thruster;
use App\Models\SC\Manufacturer;
use App\Traits\HasDescriptionDataTrait;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Vehicle extends CommodityItem
{
    use HasFactory;
    use ModelChangelog;
    use HasDescriptionDataTrait;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $table = 'sc_vehicles';

    protected $fillable = [
        'item_uuid',

        'shipmatrix_id',
        'class_name',
        'name',
        'career',
        'role',
        'is_ship',
        'size',
        'width',
        'height',
        'length',
        'cargo_capacity',
        'crew',
        'weapon_crew',
        'operations_crew',
        'mass',
        'health',

        'zero_to_scm',
        'zero_to_max',
        'scm_to_zero',
        'max_to_zero',

        'acceleration_main',
        'acceleration_retro',
        'acceleration_vtol',
        'acceleration_maneuvering',

        'acceleration_g_main',
        'acceleration_g_retro',
        'acceleration_g_vtol',
        'acceleration_g_maneuvering',

        'claim_time',
        'expedite_time',
        'expedite_cost',
    ];

    protected $casts = [
        'size' => 'int',
        'width' => 'double',
        'height' => 'double',
        'length' => 'double',
        'ship_matrix_id' => 'int',
        'is_ship' => 'boolean',
        'cargo_capacity' => 'int',
        'crew' => 'int',
        'weapon_crew' => 'int',
        'operations_crew' => 'int',
        'mass' => 'float',
        'health' => 'float',

        'zero_to_scm' => 'float',
        'zero_to_max' => 'float',
        'scm_to_zero' => 'float',
        'max_to_zero' => 'float',

        'acceleration_main' => 'float',
        'acceleration_retro' => 'float',
        'acceleration_vtol' => 'float',
        'acceleration_maneuvering' => 'float',

        'acceleration_g_main' => 'float',
        'acceleration_g_retro' => 'float',
        'acceleration_g_vtol' => 'float',
        'acceleration_g_maneuvering' => 'float',

        'claim_time' => 'float',
        'expedite_time' => 'float',
        'expedite_cost' => 'float',
    ];

    protected $with = [
        'parts',
        'armor',
        'flightController',
        'quantumDrives',
        'shields',
        'thrusters',
    ];

    protected $perPage = 5;

    public function getCleanNameAttribute()
    {
        return trim(str_replace($this->manufacturer->code, '', $this->name));
    }

    /**
     * The Vehicle Manufacturer
     *
     * @return BelongsTo
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::class,
            'shipmatrix_id',
            'id'
        )->withDefault();
    }

    /**
     * Vehicle handling params found on ground vehicles
     * Sadly those are not set on the WheeledController
     *
     * @return HasOne
     */
    public function handling(): HasOne
    {
        return $this->hasOne(VehicleHandling::class, 'vehicle_id', 'id');
    }

    /**
     * Parts of a vehicle that can be destroyed / have max_damage
     *
     * @return HasMany
     */
    public function parts(): HasMany
    {
        return $this->hasMany(
            VehiclePart::class,
            'vehicle_id',
            'id',
        )->orderByDesc('damage_max');
    }

    /**
     * Hardpoints and sub-hardpoints found on a vehicle
     *
     * @return HasMany
     */
    public function hardpoints(): HasMany
    {
        return $this->hasMany(
            Hardpoint::class,
            'vehicle_id',
            'id',
        );
    }

    /**
     * Only root hardpoints i.e. no hardpoints found on other hardpoints
     *
     * @return HasMany
     */
    public function hardpointsWithoutParent(): HasMany
    {
        return $this->hardpoints()
            ->whereNull('parent_hardpoint_id')
            ->orderBy('hardpoint_name');
    }

    /**
     * Items equipped on hardpoints
     *
     * @return HasManyThrough
     */
    public function hardpointItems(): HasManyThrough
    {
        return $this->itemSpec(Item::class);
    }

    /**
     * Items equipped on hardpoints
     *
     * @return HasManyThrough
     */
    public function hardpointItemPortItems(): HasManyThrough
    {
        return $this->itemSpec(ItemPort::class);
    }

    public function flightController(): HasOneThrough
    {
        return $this->itemSpec(FlightController::class, true);
    }

    public function quantumDrives(): HasManyThrough
    {
        return $this->itemSpec(QuantumDrive::class);
    }

    public function shields(): HasManyThrough
    {
        return $this->itemSpec(Shield::class);
    }

    public function thrusters(): HasManyThrough
    {
        return $this->itemSpec(Thruster::class);
    }

    public function armor(): HasOneThrough
    {
        return $this->itemSpec(Armor::class, true);
    }

    public function manufacturer(): HasOneThrough
    {
        return $this->hasOneThrough(
            Manufacturer::class,
            Item::class,
            'uuid',
            'id',
            'item_uuid',
            'manufacturer_id',
        );
    }

    public function getComputedHealthAttribute(): float
    {
        return $this->hardpointItems()
            ->whereHas('durabilityData')
            ->get()
            ->map(function ($item) {
                return $item->durabilityData->health ?? 0;
            })
            ->sum();
    }

    /**
     * Sum the cargo capacities of all cargo grids
     *
     * @return float Total capacity in SCU
     */
    public function getScuAttribute(): float
    {
        $scu = $this->hardpointItems()
            ->whereHas('container')
            ->whereIn('type', ['Cargo', 'CargoGrid', 'Container'])
            ->where('sc_items.class_name', 'NOT LIKE', '%storage%')
            ->where('sc_items.class_name', 'NOT LIKE', '%personal%')
            ->get()
            ->map(function ($item) {
                return $item->container->calculated_scu ?? 0;
            })
            ->sum();

        // Vehicles having a cargo hardpoint that has no cargo grid, e.g. Constellation Aquila
        if ($this->hardpoints()->where('class_name', 'LIKE', '%cargo%')->exists()) {
            $scu += $this->hardpointItemPortItems()
                ->whereRelation('item', 'type', 'CargoGrid')
                ->get()
                ->map(function (ItemPort $item) {
                    return $item->item?->container?->calculated_scu;
                })
                ->sum();
        }

        return empty($scu) ? 0 : $scu;
    }

    /**
     * Sum the cargo capacities of all personal inventories
     * These are boxes, lockers, or other storage means
     *
     * @return float Total capacity in SCU
     */
    public function getPersonalInventoryScuAttribute(): float
    {
        return $this->hardpointItems()
            ->whereHas('container')
            ->where(function (Builder $query) {
                $query->where('hardpoint_name', 'LIKE', '%storage%')
                    ->orWhere('hardpoint_name', 'LIKE', '%personal%')
                    ->orWhere('hardpoint_name', 'LIKE', '%general_cargo%');
            })
            ->get()
            ->map(function ($item) {
                return $item->container->calculated_scu ?? 0;
            })
            ->sum();
    }

    /**
     * Size of the vehicle inventory accessible through the 'I' key, or through the pilot set access
     *
     * @return float
     */
    public function getVehicleInventoryScuAttribute(): float
    {
        return ($this->item?->container?->scu ?? 0) + $this->hardpoints()
            ->whereHas('item.container')
            ->where(function (Builder $query) {
                $query->where('hardpoint_name', 'LIKE', '%access%');
            })
            ->get()
            ->map(function ($item) {
                return $item->item->container->calculated_scu ?? 0;
            })
            ->sum();
    }

    public function getFuelCapacityAttribute(): ?float
    {
        $capacity = $this->fuelTanks()
            ->get()
            ->map(function ($item) {
                return $item?->specification?->capacity ?? 0;
            })
            ->sum();

        return empty($capacity) ? null : $capacity;
    }

    public function getQuantumFuelCapacityAttribute(): ?float
    {
        $capacity = $this->fuelTanks('QuantumFuelTank')
            ->get()
            ->map(function ($item) {
                return $item?->specification?->capacity ?? 0;
            })
            ->sum();

        return empty($capacity) ? null : $capacity;
    }

    public function getFuelIntakeRateAttribute(): ?float
    {
        $rate = $this->fuelIntakes()
            ->get()
            ->map(function ($item) {
                return $item?->specification?->fuel_push_rate ?? 0;
            })
            ->sum();

        return empty($rate) ? null : $rate;
    }

    public function getFuelUsage(string $type = 'MainThruster'): ?float
    {
        $query = $this->hardpoints()->whereRelation('item', function (Builder $query) use ($type) {
            if ($type === 'RetroThruster' || $type === 'VtolThruster') {
                $query->where('type', 'ManneuverThruster');
            } else {
                $query->where('type', $type);
            }
        });

        if ($type === 'RetroThruster') {
            $query->where('class_name', 'LIKE', '%retro%');
        } elseif ($type === 'VtolThruster') {
            $query->where('class_name', 'LIKE', '%vtol%');
        } elseif ($type === 'ManneuverThruster') {
            $query->where('class_name', 'NOT LIKE', '%vtol%')->where('class_name', 'NOT LIKE', '%retro%');
        }

        $usage = $query->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item?->specification;
            })
            ->filter()
            ->map(function ($item) {
                return (($item->fuel_burn_per_10k_newton / 1e4) * $item->thrust_capacity) ?? 0;
            })
            ->sum();

        $usage = round($usage);

        return empty($usage) ? null : $usage;
    }

    public function irData(): HasManyThrough
    {
        return $this->itemSpec(ItemHeatData::class);
    }

    public function getIrEmissionAttribute(): ?float
    {
        $emission = round($this->irData->sum('infrared_emission'));

        return empty($emission) ? null : $emission;
    }

    public function getShieldHpAttribute(): ?float
    {
        $hp = round($this->shields->sum('max_shield_health'));

        return empty($hp) ? null : $hp;
    }

    public function emData(): HasManyThrough
    {
        return $this->itemSpec(ItemPowerData::class);
    }

    public function getEmEmissionAttribute(): array
    {
        $min = round($this->emData->sum('min_electromagnetic_emission'));
        $max = round($this->emData->sum('max_electromagnetic_emission'));

        return [
            'min' => empty($min) ? null : $min,
            'max' => empty($max) ? null : $max,
        ];
    }

    private function fuelTanks(mixed $types = ['FuelTank', 'ExternalFuelTank']): HasManyThrough
    {
        if (is_string($types)) {
            $types = [$types];
        }

        return $this->hardpointItems()->whereIn('type', $types);
    }

    private function fuelIntakes(): HasManyThrough
    {
        return $this->hardpointItems()->where('type', 'FuelIntake');
    }

    public function itemSpec(string $related, bool $one = false, string $key = 'equipped_item_uuid'): HasOneThrough|HasManyThrough
    {
        if ($one === true) {
            return $this->hasOneThrough(
                $related,
                Hardpoint::class,
                'vehicle_id',
                $related === Item::class ? 'uuid' : 'item_uuid',
                'id',
                $key,
            );
        }

        return $this->hasManyThrough(
            $related,
            Hardpoint::class,
            'vehicle_id',
            $related === Item::class ? 'uuid' : 'item_uuid',
            'id',
            $key,
        );
    }
}
