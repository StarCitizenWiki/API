<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends CommodityItem
{
    use HasFactory;
    use ModelChangelog;

    protected $table = 'star_citizen_unpacked_vehicles';

    protected $fillable = [
        'uuid',

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

        'scm_speed',
        'max_speed',
        'zero_to_scm',
        'zero_to_max',
        'scm_to_zero',
        'max_to_zero',

        'pitch',
        'yaw',
        'roll',

        'acceleration_main',
        'acceleration_retro',
        'acceleration_vtol',
        'acceleration_maneuvering',

        'acceleration_g_main',
        'acceleration_g_retro',
        'acceleration_g_vtol',
        'acceleration_g_maneuvering',

        'fuel_capacity',
        'fuel_intake_rate',
        'fuel_usage_main',
        'fuel_usage_retro',
        'fuel_usage_vtol',
        'fuel_usage_maneuvering',

        'quantum_speed',
        'quantum_spool_time',
        'quantum_fuel_capacity',
        'quantum_range',

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

    public function getNameAttribute($name)
    {
        return $name;
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
            VehicleHardpoint::class,
            'vehicle_id',
            'id',
        );
    }

    public function hardpointsWithoutParent(): HasMany
    {
        return $this->hasMany(
            VehicleHardpoint::class,
            'vehicle_id',
            'id',
        )
            ->whereNull('parent_hardpoint_id')
            ->orderBy('hardpoint_name');
    }

    /**
     * Sum the cargo capacities of all cargo grids
     *
     * @return float Total capacity in SCU
     */
    public function getScuAttribute(): float
    {
        return $this->hardpoints()
            ->whereRelation('item', 'type', 'Cargo')
            ->get()
            ->map(function (VehicleHardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function (ShipItem $item) {
                return $item->specification;
            })
            ->map(function ($item) {
                return $item->scu ?? 0;
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
        return $this->hardpoints()
            ->whereRelation('item', 'type', 'PersonalInventory')
            ->get()
            ->map(function (VehicleHardpoint $hardpoint) {
                return $hardpoint->item;
            })
            ->map(function (ShipItem $item) {
                return $item->specification;
            })
            ->map(function ($item) {
                return $item->scu ?? 0;
            })
            ->sum();
    }
}
