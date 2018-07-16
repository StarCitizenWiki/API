<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle;

use Illuminate\Database\Eloquent\Model;

/**
 * Abstract Vehicle Class
 */
abstract class AbstractVehicle extends Model
{
    protected $with = [
        'translations',
        'foci',
        'manufacturer',
        'productionStatus',
        'size',
        'type',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    abstract public function translations();

    /**
     * The Vehicle Foci
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function foci()
    {
        return $this->belongsToMany('App\Models\StarCitizen\Vehicle\Focus\VehicleFocus');
    }

    /**
     * The Vehicle Manufacturer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function manufacturer()
    {
        return $this->belongsTo('App\Models\StarCitizen\Manufacturer\Manufacturer');
    }

    /**
     * The Vehicle Production Status
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productionStatus()
    {
        return $this->belongsTo('App\Models\StarCitizen\ProductionStatus\ProductionStatus');
    }

    /**
     * The Vehicle Role Type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Type\VehicleType');
    }

    /**
     * The Vehicle Size
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function size()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Size\VehicleSize');
    }
}
