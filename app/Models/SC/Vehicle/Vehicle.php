<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use App\Models\SC\CommodityItem;
use App\Models\SC\Item\Item;
use App\Models\SC\ItemSpecification\FlightController;
use App\Models\SC\Manufacturer;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Vehicle extends CommodityItem
{
    use HasFactory;
    use ModelChangelog;

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
        'scm_speed' => 'float',
        'max_speed' => 'float',
        'zero_to_scm' => 'float',
        'zero_to_max' => 'float',
        'scm_to_zero' => 'float',
        'max_to_zero' => 'float',
        'pitch' => 'float',
        'yaw' => 'float',
        'roll' => 'float',
        'acceleration_main' => 'float',
        'acceleration_retro' => 'float',
        'acceleration_vtol' => 'float',
        'acceleration_maneuvering' => 'float',
        'acceleration_g_main' => 'float',
        'acceleration_g_retro' => 'float',
        'acceleration_g_vtol' => 'float',
        'acceleration_g_maneuvering' => 'float',
        'fuel_capacity' => 'float',
        'fuel_intake_rate' => 'float',
        'fuel_usage_main' => 'float',
        'fuel_usage_retro' => 'float',
        'fuel_usage_vtol' => 'float',
        'fuel_usage_maneuvering' => 'float',
        'quantum_speed' => 'float',
        'quantum_spool_time' => 'float',
        'quantum_fuel_capacity' => 'float',
        'quantum_range' => 'float',
        'claim_time' => 'float',
        'expedite_time' => 'float',
        'expedite_cost' => 'float',
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
        );
    }

    public function hardpoints(): HasMany
    {
        return $this->hasMany(
            Hardpoint::class,
            'vehicle_id',
            'id',
        );
    }

    public function flightController(): ?FlightController
    {
        $controller = $this->hasOne(
            Hardpoint::class,
            'vehicle_id',
            'id',
        )->whereRelation('item', function (Builder $query) {
            $query->where('type', 'FlightController');
        })->first();

        return $controller?->item?->specification;
    }

    public function quantumDrives(): HasMany
    {
        return $this->hasMany(
            Hardpoint::class,
            'vehicle_id',
            'id',
        )->whereRelation('item', function (Builder $query) {
            $query->where('type', 'QuantumDrive');
        });
    }

    public function hardpointsWithoutParent(): HasMany
    {
        return $this->hasMany(
            Hardpoint::class,
            'vehicle_id',
            'id',
        )
            ->whereNull('parent_hardpoint_id')
            ->orderBy('hardpoint_name');
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
        return $this->hardpoints()
            ->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->filter(function (Item $item) {
                return $item->exists;
            })
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
        return $this->hardpoints()
            ->whereHas('item.container')
            ->whereHas('item', function (Builder $query) {
                $query->whereIn('type', ['Cargo', 'CargoGrid', 'Container']);
            })
            ->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function ($item) {
                return $item->calculated_scu ?? 0;
            })
            ->sum();
    }

    /**
     * Sum the cargo capacities of all personal inventories
     *
     * @return float Total capacity in SCU
     */
    public function getPersonalInventoryScuAttribute(): float
    {
        $scu = $this->hardpoints()
            ->whereHas('item.container')
            ->where('hardpoint_name', 'LIKE', '%storage%')
            ->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function ($item) {
                return $item->container;
            })
            ->map(function ($item) {
                return $item->calculated_scu ?? 0;
            })
            ->sum();

        return $scu;
    }

    public function getVehicleInventoryScuAttribute(): float
    {
        return $this->item->container->scu ?? 0;
    }

    public function getFuelCapacityAttribute(): float
    {
        return $this->fuelTanks()
            ->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function ($item) {
                return $item->specification;
            })
            ->map(function ($item) {
                return $item->capacity ?? 0;
            })
            ->sum();
    }

    public function getQuantumFuelCapacityAttribute(): float
    {
        return $this->fuelTanks('QuantumFuelTank')
            ->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function ($item) {
                return $item->specification;
            })
            ->map(function ($item) {
                return $item->capacity ?? 0;
            })
            ->sum();
    }

    public function getFuelIntakeRateAttribute(): float
    {
        return $this->fuelIntakes()
            ->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function ($item) {
                return $item->specification;
            })
            ->map(function ($item) {
                return $item->fuel_push_rate ?? 0;
            })
            ->sum();
    }

    public function getFuelUsage(string $type = 'MainThruster'): float
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

        return $query
            ->get()
            ->map(function (Hardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function ($item) {
                return $item->specification;
            })
            ->map(function ($item) {
                return (($item->fuel_burn_per_10k_newton / 1e4) * $item->thrust_capacity) ?? 0;
            })
            ->sum();
    }

    private function fuelTanks(mixed $types = ['FuelTank', 'ExternalFuelTank']): HasMany
    {
        if (is_string($types)) {
            $types = [$types];
        }
        return $this->hardpoints()->whereRelation('item', function (Builder $query) use ($types) {
            $query->whereIn('type', $types);
        });
    }

    private function fuelIntakes(): HasMany
    {
        return $this->hardpoints()->whereRelation('item', function (Builder $query) {
            $query->whereIn('type', [
                'FuelIntake',
            ]);
        });
    }
}
