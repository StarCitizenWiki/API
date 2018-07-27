<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle;

use App\Models\Api\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasMultipleVersionsTrait as MultipleVersions;

/**
 * Abstract Vehicle Class
 */
abstract class AbstractVehicle extends HasTranslations
{
    use MultipleVersions;

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
    abstract public function translations();

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
        return $this->belongsToMany('App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus');
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
