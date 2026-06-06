<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class VehicleData extends Model
{
    use HasFactory;
    use HasGameVersion;

    protected $table = 'game_vehicle_data';

    protected $fillable = [
        'vehicle_id',
        'game_version_id',
        'manufacturer_id',
        'shipmatrix_id',

        'class_name',
        'name',
        'display_name',
        'career',
        'role',

        'is_vehicle',
        'is_gravlev',
        'is_spaceship',

        'is_player_relevant',

        'size',

        'data',

        'uex_purchase_prices',
        'uex_rental_prices',
    ];

    protected $casts = [
        'vehicle_id' => 'integer',
        'game_version_id' => 'integer',
        'manufacturer_id' => 'integer',
        'shipmatrix_id' => 'integer',

        'is_vehicle' => 'boolean',
        'is_gravlev' => 'boolean',
        'is_spaceship' => 'boolean',

        'is_player_relevant' => 'boolean',

        'size' => 'integer',

        'data' => 'array',

        'uex_purchase_prices' => 'array',
        'uex_rental_prices' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function installedItems(): BelongsToMany
    {
        return $this->belongsToMany(
            ItemData::class,
            'game_item_data_vehicle_data',
            'vehicle_data_id',
            'item_data_id'
        );
    }

    public function shipMatrixVehicle(): BelongsTo
    {
        return $this->belongsTo(
            \App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle::class,
            'shipmatrix_id',
            'id',
        )->withDefault();
    }

    /**
     * Get the ItemData for this vehicle, matching the same game version.
     * Returns null if no matching ItemData exists.
     */
    public function itemData(): ?ItemData
    {
        if (! $this->relationLoaded('vehicle') || $this->vehicle === null) {
            return null;
        }

        if (! $this->vehicle->relationLoaded('item') || $this->vehicle->item === null) {
            return null;
        }

        return $this->vehicle->item
            ->data()
            ->where('game_version_id', $this->game_version_id)
            ->first();
    }

    /**
     * Get ItemDescriptionData collection through the vehicle's item.
     * Returns empty collection if no ItemData exists.
     */
    public function itemDescriptionData(): Collection
    {
        $itemData = $this->itemData();

        if ($itemData === null) {
            return collect();
        }

        if (! $itemData->relationLoaded('descriptionData')) {
            return collect();
        }

        return $itemData->descriptionData;
    }

    public function scopePlayerRelevant(Builder $query): Builder
    {
        return $query->where($this->getTable().'.is_player_relevant', true);
    }

    public function scopeForVehicleType(Builder $query, string $vehicleType): Builder
    {
        return match ($vehicleType) {
            'ground-vehicles' => $query->where('is_vehicle', true),
            'gravlev-vehicles' => $query->where('is_gravlev', true),
            default => $query,
        };
    }
}
