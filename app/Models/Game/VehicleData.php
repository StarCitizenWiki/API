<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleData extends Model
{
    protected $table = 'game_vehicle_data';

    protected $fillable = [
        'vehicle_id',
        'game_version_id',
        'manufacturer_id',
        'shipmatrix_id',

        'class_name',
        'name',
        'career',
        'role',

        'is_vehicle',
        'is_gravlev',
        'is_spaceship',

        'size',
        'length',
        'width',
        'height',
        'crew',
        'mass',
        'cargo',

        'insurance_claim_time',
        'insurance_expedited_time',
        'insurance_expedited_cost',

        'shield_face_type',
        'shield_hp',
        'health',

        'quantum_speed',
        'quantum_spool_time',
        'quantum_fuel_capacity',
        'quantum_range',

        'fuel_capacity',
        'fuel_intake_rate',
        'fuel_usage_main',
        'fuel_usage_retro',
        'fuel_usage_vtol',
        'fuel_usage_maneuvering',
        'json',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'game_version_id' => 'integer',
        'manufacturer_id' => 'integer',
        'shipmatrix_id' => 'integer',

        'is_vehicle' => 'boolean',
        'is_gravlev' => 'boolean',
        'is_spaceship' => 'boolean',

        'size' => 'integer',
        'length' => 'double',
        'width' => 'double',
        'height' => 'double',
        'crew' => 'integer',
        'mass' => 'double',
        'cargo' => 'integer',

        'insurance_claim_time' => 'double',
        'insurance_expedited_time' => 'double',
        'insurance_expedited_cost' => 'double',

        'shield_hp' => 'double',
        'health' => 'double',

        'quantum_speed' => 'double',
        'quantum_spool_time' => 'double',
        'quantum_fuel_capacity' => 'double',
        'quantum_range' => 'double',

        'fuel_capacity' => 'double',
        'fuel_intake_rate' => 'double',
        'fuel_usage_main' => 'double',
        'fuel_usage_retro' => 'double',
        'fuel_usage_vtol' => 'double',
        'fuel_usage_maneuvering' => 'double',

        'json' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }

    public function shipMatrixVehicle(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\StarCitizen\Vehicle\Vehicle\Vehicle::class,
            'shipmatrix_id',
            'id'
        )->withDefault();
    }
}
