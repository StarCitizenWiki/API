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

        preg_match(
            '/((left|right|tail|top|bottom|front|mid_|lower|upper|back|rear)_?)+/',
            strtolower($this->name),
            $matches
        );

        if (isset($matches[0]) && $matches[0] !== strtolower($this->name)) {
            $name = trim(str_replace('_', ' ', str_replace($matches[0], '', strtolower($this->name))));
            $position = trim(str_replace('_', ' ', $matches[0]));

            return Str::ucfirst(sprintf('%s (%s)', $name, $position));
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
