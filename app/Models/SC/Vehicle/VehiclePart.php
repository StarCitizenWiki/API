<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class VehiclePart extends Model
{
    protected $table = 'sc_vehicle_parts';

    protected $fillable = [
        'vehicle_id',
        'name',
        'damage_max',
        'parent_id',
    ];

    protected $casts = [
        'damage_max' => 'double',
    ];

    protected $with = [
        'children',
    ];

    /**
     * Generates a display name for a part
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        $cleaned = strtolower(Str::replace('_', ' ', $this->name));

        foreach (['left', 'right', 'tail', 'top', 'bottom', 'front', 'rear'] as $separator) {
            $parts = explode(' ' . $separator, $cleaned);

            if (count($parts) > 1) {
                $first = array_shift($parts);
                array_unshift($parts, $separator);
                $cleaned = sprintf('%s (%s)', trim($first), ltrim(implode($parts)));
                break;
            }
        }

        return Str::ucfirst($cleaned);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(__CLASS__, 'parent_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_id', 'id');
    }
}
