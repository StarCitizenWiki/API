<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Vehicle;

use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\Vehicle\Component;
use App\Models\StarCitizen\Vehicle\Focus\Focus;
use App\Models\StarCitizen\Vehicle\Size\Size;
use App\Models\StarCitizen\Vehicle\Type\Type;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Abstract Vehicle Class
 */
class Vehicle extends HasTranslations
{
    protected $fillable = [
        'cig_id',
        'name',
        'slug',
        'manufacturer_id',
        'production_status_id',
        'production_note_id',
        'size_id',
        'type_id',
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
        'msrp',
        'pledge_url',
        'chassis_id',
        'updated_at',
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

    protected $casts = [
        'cig_id' => 'integer',
        'chassis_id' => 'integer',
        'scm_speed' => 'integer',
        'afterburner_speed' => 'integer',
        'mass' => 'integer',
        'cargo_capacity' => 'integer',
        'min_crew' => 'integer',
        'max_crew' => 'integer',

        'length' => 'float',
        'beam' => 'float',
        'height' => 'float',
        'pitch_max' => 'float',
        'yaw_max' => 'float',
        'roll_max' => 'float',
        'x_axis_acceleration' => 'float',
        'y_axis_acceleration' => 'float',
        'z_axis_acceleration' => 'float',

        'msrp' => 'integer',

        'updated_at' => 'datetime',
    ];

    protected $perPage = 5;

    public function translations(): HasMany
    {
        return $this->hasMany(VehicleTranslation::class);
    }

    public function skus(): HasMany
    {
        return $this->hasMany(VehicleSku::class);
    }

    public function foci(): BelongsToMany
    {
        return $this->belongsToMany(Focus::class, 'vehicle_vehicle_focus');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function productionStatus(): BelongsTo
    {
        return $this->belongsTo(ProductionStatus::class, 'production_status_id');
    }

    public function productionNote(): BelongsTo
    {
        return $this->belongsTo(ProductionNote::class, 'production_note_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(Component::class, 'vehicle_component')
            ->using(VehicleComponent::class)
            ->withPivot(
                [
                    'mounts',
                    'size',
                    'details',
                    'quantity',
                ]
            );
    }

    public function componentsByClass(): array
    {
        $components = $this->components
            ->keyBy('component_class')
            ->keys()
            ->flip()
            ->map(
                function () {
                    return [];
                }
            )->toArray();

        $this->components->each(
            function (Component $component) use (&$components) {
                $components[$component->component_class][] = $component;
            }
        );

        return $components;
    }

    public function ships()
    {
        return $this->size()->ship();
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function getWidthAttribute()
    {
        $unpacked = $this->unpacked->width;
        if ($unpacked !== null && $unpacked > 0) {
            return $unpacked;
        }

        return $this->beam;
    }

    public function getHeightAttribute($height)
    {
        $unpacked = $this->unpacked->height;
        if ($unpacked !== null && $unpacked > 0) {
            return $unpacked;
        }

        return $height;
    }

    public function getLengthAttribute($length)
    {
        $unpacked = $this->unpacked->length;
        if ($unpacked !== null && $unpacked > 0) {
            return $unpacked;
        }

        return $length;
    }

    public function getScWidthAttribute()
    {
        $unpacked = $this->sc->width;
        if ($unpacked !== null && $unpacked > 0) {
            return $unpacked;
        }

        return $this->beam;
    }

    public function getScHeightAttribute($height)
    {
        $unpacked = $this->sc->height;
        if ($unpacked !== null && $unpacked > 0) {
            return $unpacked;
        }

        return $height;
    }

    public function getScLengthAttribute($length)
    {
        $unpacked = $this->sc->length;
        if ($unpacked !== null && $unpacked > 0) {
            return $unpacked;
        }

        return $length;
    }

    /**
     * Unpacked Data
     */
    public function sc(): HasOne
    {
        return $this->hasOne(
            \App\Models\SC\Vehicle\Vehicle::class,
            'shipmatrix_id',
            'id',
        )
            ->withoutGlobalScopes()
            ->withDefault();
    }

    public function groundVehicles()
    {
        return $this->size()->groundVehicle();
    }

    public function getMorphClass(): string
    {
        return self::class;
    }

    public function getForeignKey(): string
    {
        return 'vehicle_id';
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function loaner(): BelongsToMany
    {
        return $this->belongsToMany(
            __CLASS__,
            'vehicle_loaners',
            'vehicle_id',
            'loaner_id',
            'id',
        )->withPivot('version');
    }
}
