<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Vehicle;

use App\Contracts\HasChangelogsInterface;
use App\Events\ModelUpdating;
use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use App\Models\StarCitizen\Vehicle\Component\Component;
use App\Models\StarCitizen\Vehicle\Focus\Focus;
use App\Models\StarCitizen\Vehicle\Size\Size;
use App\Models\StarCitizen\Vehicle\Type\Type;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Abstract Vehicle Class
 */
class Vehicle extends HasTranslations implements HasChangelogsInterface
{
    use ModelChangelog;

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

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(VehicleTranslation::class);
    }

    /**
     * @return HasMany
     */
    public function skus()
    {
        return $this->hasMany(VehicleSku::class);
    }

    /**
     * @return HasManyThrough
     */
    public function translationChangelogs(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\System\ModelChangelog::class,
            VehicleTranslation::class,
            'vehicle_id',
            'changelog_id'
        )->where('changelog_type', VehicleTranslation::class);
    }

    /**
     * The Vehicle Foci
     *
     * @return BelongsToMany
     */
    public function foci()
    {
        return $this->belongsToMany(Focus::class, 'vehicle_vehicle_focus');
    }

    /**
     * The Vehicle Manufacturer
     *
     * @return BelongsTo
     */
    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    /**
     * The Vehicle Production Status
     *
     * @return BelongsTo
     */
    public function productionStatus(): BelongsTo
    {
        return $this->belongsTo(ProductionStatus::class, 'production_status_id');
    }

    /**
     * The Vehicle Production Note
     *
     * @return BelongsTo
     */
    public function productionNote(): BelongsTo
    {
        return $this->belongsTo(ProductionNote::class, 'production_note_id');
    }

    /**
     * The Vehicle Role Type
     *
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * @return BelongsToMany
     */
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

    /**
     * Get Components keyed by component class
     *
     * @return array
     */
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

    /**
     * Ships
     *
     * @return mixed
     */
    public function ships()
    {
        return $this->size()->ship();
    }

    /**
     * The Vehicle Size
     *
     * @return BelongsTo
     */
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
     *
     * @return HasOne
     */
    public function unpacked(): HasOne
    {
        return $this->hasOne(
            \App\Models\StarCitizenUnpacked\Vehicle::class,
            'shipmatrix_id',
            'id'
        )
            ->withDefault();
    }

    /**
     * Unpacked Data
     *
     * @return HasOne
     */
    public function sc(): HasOne
    {
        return $this->hasOne(
            \App\Models\SC\Vehicle\Vehicle::class,
            'shipmatrix_id',
            'id'
        )
            ->withDefault();
    }


    /**
     * Ground Vehicles
     *
     * @return mixed
     */
    public function groundVehicles()
    {
        return $this->size()->groundVehicle();
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
     * {@inheritdoc}
     */
    public function getRouteKeyName()
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
