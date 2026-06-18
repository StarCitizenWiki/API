<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix\Vehicle;

use App\Models\Game\VehicleData;
use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Translatable\HasTranslations;

class Vehicle extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['translation'];

    protected $table = 'shipmatrix_vehicles';

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
        'translation',
    ];

    protected $with = [
        'foci',
        'manufacturer',
        'productionStatus',
        'productionNote',
        'size',
        'type',
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

    public function scopeShips(Builder $query): Builder
    {
        return $query->whereHas('size', function (Builder $sizeQuery): void {
            $sizeQuery->ship();
        });
    }

    public function scopeGroundVehicles(Builder $query): Builder
    {
        return $query->whereHas('size', function (Builder $sizeQuery): void {
            $sizeQuery->groundVehicle();
        });
    }

    public function skus(): HasMany
    {
        return $this->hasMany(VehicleSku::class);
    }

    public function pledgeSkus(): BelongsToMany
    {
        return $this->belongsToMany(
            PledgeStoreSku::class,
            'pledge_store_sku_ship',
            'shipmatrix_vehicle_id',
            'pledge_store_sku_id',
        );
    }

    public function foci(): BelongsToMany
    {
        return $this->belongsToMany(Focus::class, 'shipmatrix_vehicle_vehicle_focus');
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
        return $this->belongsToMany(Component::class, 'shipmatrix_vehicle_component')
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

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function getWidthAttribute()
    {
        return $this->beam;
    }

    /**
     * Unpacked Data
     */
    public function sc(): HasOne
    {
        return $this->hasOne(
            VehicleData::class,
            'shipmatrix_id',
            'id',
        )
            ->withoutGlobalScopes()
            ->withDefault();
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
            'shipmatrix_vehicle_loaners',
            'vehicle_id',
            'loaner_id',
            'id',
        )->withPivot('version');
    }

    public function scopeFocus(Builder $query, mixed $value): Builder
    {
        $focusSlugs = is_array($value) ? $value : explode(',', (string) $value);
        $focusSlugs = array_map('strtolower', $focusSlugs);

        return $query->whereHas('foci', function (Builder $q) use ($focusSlugs) {
            $q->whereIn('slug', $focusSlugs);
        });
    }

    public function scopeType(Builder $query, mixed $value): Builder
    {
        return $query->whereHas('type', function (Builder $q) use ($value) {
            $q->where('slug', strtolower((string) $value));
        });
    }

    public function scopeProductionStatus(Builder $query, mixed $value): Builder
    {
        return $query->whereHas('productionStatus', function (Builder $q) use ($value) {
            $q->where('slug', strtolower((string) $value));
        });

    }
}
