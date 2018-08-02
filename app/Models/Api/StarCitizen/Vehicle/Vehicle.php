<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;

/**
 * Abstract Vehicle Class
 */
class Vehicle extends HasTranslations
{
    protected $fillable = [
        'cig_id',
        'name',
        'manufacturer_id',
        'production_status_id',
        'production_note_id',
        'vehicle_size_id',
        'vehicle_type_id',
        'length',
        'beam',
        'height',
        'mass',
        'cargo_capacity',
        'min_crew',
        'max_crew',
        'scm_speed',
        'afterburner_speed',
        'pitch_max',
        'yaw_max',
        'roll_max',
        'x_axis_acceleration',
        'y_axis_acceleration',
        'z_axis_acceleration',
        'chassis_id',
    ];

    protected $casts = [
        'length' => 'float',
        'beam' => 'float',
        'height' => 'float',

        'pitch_max' => 'float',
        'yaw_max' => 'float',
        'roll_max' => 'float',

        'x_axis_acceleration' => 'float',
        'y_axis_acceleration' => 'float',
        'z_axis_acceleration' => 'float',
    ];

    protected $with = [
        'foci',
        'manufacturer',
        'productionStatus',
        'productionNote',
        'size',
        'type',
        'translations',
    ];

    protected $hidden = [
        'pivot',
    ];

    protected $perPage = 5;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(VehicleTranslation::class, 'vehicle_id');
    }

    public function changelogs()
    {
        return $this->morphMany('App\Models\Api\ModelChangelog', 'changelog');
    }

    /**
     * The Vehicle Foci
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function foci()
    {
        return $this->belongsToMany('App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus', 'vehicle_vehicle_focus', 'vehicle_id', 'vehicle_focus_id');
    }

    /**
     * The Vehicle Manufacturer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manufacturer()
    {
        return $this->belongsTo('App\Models\Api\StarCitizen\Manufacturer\Manufacturer', 'manufacturer_id', 'cig_id');
    }

    /**
     * The Vehicle Production Status
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionStatus()
    {
        return $this->belongsTo('App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus', 'production_status_id');
    }

    /**
     * The Vehicle Production Note
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionNote()
    {
        return $this->belongsTo('App\Models\Api\StarCitizen\ProductionNote\ProductionNote', 'production_note_id');
    }

    /**
     * The Vehicle Role Type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\Models\Api\StarCitizen\Vehicle\Type\VehicleType', 'vehicle_type_id');
    }

    /**
     * The Vehicle Size
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function size()
    {
        return $this->belongsTo('App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize', 'vehicle_size_id');
    }
}
