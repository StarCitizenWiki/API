<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\ShipItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\Pivot;

class VehicleHardpoint extends Pivot
{
    protected $table = 'star_citizen_unpacked_vehicle_hardpoint';

    protected $with = [
        'children'
    ];

    protected $fillable = [
        'vehicle_id',
        'hardpoint_id',
        'parent_hardpoint_id',
        'equipped_vehicle_item_uuid',
        'min_size',
        'max_size',
        'class_name',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function hardpoint(): BelongsTo
    {
        return $this->belongsTo(Hardpoint::class, 'hardpoint_id', 'id');
    }

    public function item(): HasOne
    {
        return $this->hasOne(
            ShipItem::class,
            'uuid',
            'equipped_vehicle_item_uuid',
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            __CLASS__,
            'parent_hardpoint_id',
            'hardpoint_id',
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
