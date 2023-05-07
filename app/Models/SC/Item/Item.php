<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Events\ModelUpdating;
use App\Models\SC\Char\Clothing\Clothing;
use App\Models\SC\ItemSpecification\Cooler;
use App\Models\SC\ItemSpecification\FlightController;
use App\Models\SC\ItemSpecification\FuelIntake;
use App\Models\SC\ItemSpecification\FuelTank;
use App\Models\SC\ItemSpecification\PowerPlant;
use App\Models\SC\ItemSpecification\QuantumDrive\QuantumDrive;
use App\Models\SC\ItemSpecification\SelfDestruct;
use App\Models\SC\ItemSpecification\Thruster;
use App\Models\SC\Shop\Shop;
use App\Models\SC\Shop\ShopItem;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Item extends HasTranslations
{
    use ModelChangelog;
    use HasFactory;

    protected $table = 'sc_items';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'sub_type',
        'manufacturer',
        'size',
        'class_name',
        'version',
    ];

    protected $with = [
        'dimensions',
        'container',
//        'heatData',
//        'distortionData',
//        'powerData',
        'durabilityData',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(
            ItemTranslation::class,
            'item_uuid',
            'uuid'
        );
    }

    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(
            Shop::class,
            'sc_shop_item'
        )
            ->using(ShopItem::class)
            ->as('shop_data')
            ->withPivot(
                'item_uuid',
                'shop_uuid',
                'base_price',
                'base_price',
                'base_price_offset',
                'max_discount',
                'max_premium',
                'inventory',
                'optimal_inventory',
                'max_inventory',
                'auto_restock',
                'auto_consume',
                'refresh_rate',
                'buyable',
                'sellable',
                'rentable',
                'version',
            );
//            ->with(['items' => function ($query) {
//                return $query->where('uuid', $this->uuid);
//            }])
//            ->whereHas('items', function (Builder $query) {
//                return $query->where('uuid', $this->uuid);
//            });
    }
//
//
//    public function shopsRaw(): BelongsToMany
//    {
//        return $this->belongsToMany(
//            Shop::class,
//            'star_citizen_unpacked_shop_item'
//        )
//            ->using(ShopItem::class)
//            ->as('shop_data')
//            ->withPivot(
//                'item_uuid',
//                'shop_uuid',
//                'base_price',
//                'base_price',
//                'base_price_offset',
//                'max_discount',
//                'max_premium',
//                'inventory',
//                'optimal_inventory',
//                'max_inventory',
//                'auto_restock',
//                'auto_consume',
//                'refresh_rate',
//                'buyable',
//                'sellable',
//                'rentable',
//                'version',
//            );
//    }
//
    public function specification()
    {
        switch (true) {
//            /**
//             * Char Clothing
//             */
//            case Str::contains($this->type, 'Char_Armor'):
//                return $this->hasOne(Clothing::class, 'uuid', 'uuid')->withDefault();
//            /**
//             * Char Clothing
//             */
//            case Str::contains($this->type, 'Char_Clothing'):
//                return $this->hasOne(Clothing::class, 'uuid', 'uuid')->withDefault();
//
//            /**
//             * Personal Weapons
//             */
//            case Str::contains($this->type, 'WeaponPersonal'):
//                return $this->hasOne(WeaponPersonal::class, 'uuid', 'uuid')->withDefault();
//            case Str::contains($this->type, 'WeaponAttachment'):
//                return $this->hasOne(Attachment::class, 'uuid', 'uuid')->withDefault();
//
//            /**
//             * Vehicles
//             */
//            case Str::contains($this->type, 'Vehicle'):
//                return $this->hasOne(Vehicle::class, 'uuid', 'uuid')->withDefault();
//
//            /**
//             * Ship Items
//             */
//            case $this->type === 'WeaponGun':
//            case Str::contains($this->type, 'WeaponGun'):
//                return $this->hasOne(Weapon::class, 'uuid', 'uuid')->withDefault();
//
//            case $this->type === 'Missile':
//            case $this->type === 'Torpedo':
//            case Str::contains($this->type, 'Missile'):
//            case Str::contains($this->type, 'Torpedo'):
//                return $this->hasOne(Missile::class, 'uuid', 'uuid')->withDefault();
//
            case $this->type === 'Cooler':
                return $this->hasOne(Cooler::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'QuantumDrive':
            case Str::contains($this->type, 'QuantumDrive'):
                return $this->hasOne(QuantumDrive::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'PowerPlant':
                return $this->hasOne(PowerPlant::class, 'item_uuid', 'uuid')->withDefault();
//
//            case $this->type === 'Shield':
//            case Str::contains($this->type, 'Shield'):
//                return $this->hasOne(Shield::class, 'uuid', 'uuid')->withDefault();
//
            case $this->type === 'FuelTank':
            case $this->type === 'QuantumFuelTank':
                return $this->hasOne(FuelTank::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'FuelIntake':
                return $this->hasOne(FuelIntake::class, 'item_uuid', 'uuid')->withDefault();
//
//            case $this->type === 'ToolArm':
//            case $this->type === 'Turret':
//            case $this->type === 'TurretBase':
//            case $this->type === 'MiningArm':
//            case $this->type === 'WeaponMount':
//                return $this->hasOne(Turret::class, 'uuid', 'uuid')->withDefault();
//
//            case $this->type === 'WeaponDefensive':
//                return $this->hasOne(CounterMeasure::class, 'uuid', 'uuid')->withDefault();
//
//            case $this->type === 'MissileLauncher':
//                return $this->hasOne(MissileRack::class, 'uuid', 'uuid')->withDefault();

            case $this->type === 'MainThruster':
            case $this->type === 'ManneuverThruster':
                return $this->hasOne(Thruster::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'FlightController':
                return $this->hasOne(FlightController::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'SelfDestruct':
                return $this->hasOne(SelfDestruct::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'FlightController':
                return $this->hasOne(FlightController::class, 'item_uuid', 'uuid')->withDefault();
//
//            case $this->type === 'Radar':
//                return $this->hasOne(Radar::class, 'uuid', 'uuid')->withDefault();
//
//            case $this->type === 'WeaponMining':
//                return $this->hasOne(MiningLaser::class, 'uuid', 'uuid')->withDefault();
//
//            case $this->type === 'Cargo':
//            case $this->type === 'CargoGrid':
//                return $this->hasOne(CargoGrid::class, 'uuid', 'uuid')->withDefault();

            default:
                return $this->hasOne(Clothing::class, 'created_at', 'uuid'); //NULL
        }
    }

    public function dimensions(): HasMany
    {
        return $this->hasMany(
            ItemDimension::class,
            'item_uuid',
            'uuid'
        );
    }

    public function getDimensionAttribute(): ?ItemDimension
    {
        return $this->dimensions()->where('override', 1)->first() ?? $this->getTrueDimensionAttribute();
    }

    public function getTrueDimensionAttribute(): ?ItemDimension
    {
        return $this->dimensions()->where('override', 0)->first() ?? null;
    }

    public function container(): HasOne
    {
        return $this->hasOne(
            ItemContainer::class,
            'item_uuid',
            'uuid'
        )->withDefault();
    }

    public function ports(): HasMany
    {
        return $this->hasMany(
            ItemPort::class,
            'item_uuid',
            'uuid'
        );
    }

    public function heatData(): HasOne
    {
        return $this->hasOne(
            ItemHeatData::class,
            'item_uuid',
            'uuid'
        )->withDefault();
    }

    public function distortionData(): HasOne
    {
        return $this->hasOne(
            ItemDistortionData::class,
            'item_uuid',
            'uuid'
        )->withDefault();
    }

    public function powerData(): HasOne
    {
        return $this->hasOne(
            ItemPowerData::class,
            'item_uuid',
            'uuid'
        )->withDefault();
    }

    public function durabilityData(): HasOne
    {
        return $this->hasOne(
            ItemDurabilityData::class,
            'item_uuid',
            'uuid'
        )->withDefault();
    }
}
