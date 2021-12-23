<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class VehicleHardpoint extends Model
{
    protected $table = 'star_citizen_unpacked_vehicle_hardpoints';

    protected $with = [
        'children'
    ];

    protected $fillable = [
        'vehicle_id',
        'hardpoint_name',
        'parent_hardpoint_id',
        'equipped_vehicle_item_uuid',
        'min_size',
        'max_size',
        'class_name',
    ];

    public $timestamps = false;

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(
            ShipItem::class,
            'uuid',
            'equipped_vehicle_item_uuid',
        );
    }

    /**
     * Retrieve child hardpoints form the same table by joining on the parent_hardpoint_id attribute
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(
            __CLASS__,
            'parent_hardpoint_id',
            'id',
        )
            ->where('vehicle_id', $this->vehicle_id);
    }

    /**
     * This is needed because fractal re-uses relations?
     * While this is exactly the same as above this truly retrieves the children from the DB
     *
     * @return HasMany
     */
    public function children2(): HasMany
    {
        return $this->hasMany(
            __CLASS__,
            'parent_hardpoint_id',
            'id',
        )
            ->where('vehicle_id', $this->vehicle_id);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_hardpoint_id', 'hardpoint_id')
            ->where('vehicle_id', $this->vehicle_id)
            ->withDefault();
    }
}
