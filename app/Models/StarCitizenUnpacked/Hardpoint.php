<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Hardpoint extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_vehicle_hardpoints';

    protected $fillable = [
        'name',
    ];

    protected $hidden = [
        'pivot',
    ];

    protected $perPage = 5;

    /**
     * Vehicles using this hardpoint
     *
     * @return HasManyThrough
     */
    public function vehicles(): HasManyThrough
    {
        return $this->hasManyThrough(
            Vehicle::class,
            VehicleHardpoint::class,
            'vehicle_id',
            'id',
            'hardpoint_id',
            'id',
        );
    }

    public function getNameAttribute($name)
    {
        return $name;
    }
}
