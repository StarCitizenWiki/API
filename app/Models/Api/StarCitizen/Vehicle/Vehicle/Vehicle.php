<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Vehicle;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus;
use App\Models\Api\StarCitizen\Vehicle\Size\VehicleSize;
use App\Models\Api\StarCitizen\Vehicle\Type\VehicleType;
use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use App\Traits\HasObfuscatedRouteKeyTrait as ObfuscatedRouteKey;

/**
 * Abstract Vehicle Class
 */
class Vehicle extends HasTranslations
{
    use ModelChangelog;

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

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(VehicleTranslation::class);
    }

    /**
     * The Vehicle Foci
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function foci()
    {
        return $this->belongsToMany(VehicleFocus::class, 'vehicle_vehicle_focus');
    }

    /**
     * The Vehicle Manufacturer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * The Vehicle Production Status
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionStatus()
    {
        return $this->belongsTo(ProductionStatus::class, 'production_status_id');
    }

    /**
     * The Vehicle Production Note
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionNote()
    {
        return $this->belongsTo(ProductionNote::class, 'production_note_id');
    }

    /**
     * The Vehicle Role Type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    /**
     * The Vehicle Size
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function size()
    {
        return $this->belongsTo(VehicleSize::class, 'vehicle_size_id');
    }

    /**
     * Use Vehicle Class in Children
     *
     * @return string
     */
    public function getMorphClass()
    {
        return self::class;
    }

    /**
     * Hardcoded to fix Child Problems
     *
     * @return string
     */
    public function getForeignKey()
    {
        return 'vehicle_id';
    }

    /**
     * Key by which the api searches
     *
     * @return string
     */
    public function getRouteKey()
    {
        return urlencode($this->name);
    }
}
