<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Events\ModelUpdating;
use App\Models\SC\Char\Clothing\Armor;
use App\Models\SC\Char\Clothing\Clothes;
use App\Models\SC\Char\Grenade;
use App\Models\SC\Char\PersonalWeapon\BarrelAttach;
use App\Models\SC\Char\PersonalWeapon\IronSight;
use App\Models\SC\Char\PersonalWeapon\PersonalWeapon;
use App\Models\SC\Char\PersonalWeapon\PersonalWeaponMagazine;
use App\Models\SC\Food\Food;
use App\Models\SC\ItemSpecification\Bomb\Bomb;
use App\Models\SC\ItemSpecification\Cooler;
use App\Models\SC\ItemSpecification\Emp;
use App\Models\SC\ItemSpecification\FlightController;
use App\Models\SC\ItemSpecification\FuelIntake;
use App\Models\SC\ItemSpecification\FuelTank;
use App\Models\SC\ItemSpecification\MiningLaser;
use App\Models\SC\ItemSpecification\MiningModule;
use App\Models\SC\ItemSpecification\Missile\Missile;
use App\Models\SC\ItemSpecification\PowerPlant;
use App\Models\SC\ItemSpecification\QuantumDrive\QuantumDrive;
use App\Models\SC\ItemSpecification\QuantumInterdictionGenerator;
use App\Models\SC\ItemSpecification\SalvageModifier;
use App\Models\SC\ItemSpecification\SelfDestruct;
use App\Models\SC\ItemSpecification\Shield;
use App\Models\SC\ItemSpecification\Thruster;
use App\Models\SC\ItemSpecification\TractorBeam;
use App\Models\SC\Manufacturer;
use App\Models\SC\Shop\Shop;
use App\Models\SC\Shop\ShopItem;
use App\Models\SC\Vehicle\Vehicle;
use App\Models\SC\Vehicle\VehicleItem;
use App\Models\SC\Vehicle\Weapon\VehicleWeapon;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasDescriptionDataTrait;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends HasTranslations
{
    use HasDescriptionDataTrait;
    use HasFactory;
    use ModelChangelog;

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
        'manufacturer_id',
        'size',
        'class_name',
        'mass',
        'base_id',
        'version',
    ];

    protected $casts = [
        'size' => 'int',
        'mass' => 'double',
    ];

    public function getRouteKey()
    {
        return $this->uuid;
    }

    protected $with = [
        'dimensions',
        'container',
        'manufacturer',
        'defaultTags',
        'requiredTags',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(
            'version',
            static function (Builder $builder) {
                $builder->where('sc_items.version', config('api.sc_data_version'));
            }
        );
    }

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
            'sc_shop_item',
            'item_id'
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
    }

    public function isTurret()
    {
        return in_array($this->type, [
            'BombLauncher',
            'MiningArm',
            'MissileLauncher',
            'ToolArm',
            'Turret',
            'TurretBase',
            'UtilityTurret',
            'WeaponMining',
            'WeaponMount',
        ]);
    }

    public function specification(): ?HasOne
    {
        if ($this->type === null) {
            // Null ref
            return $this->hasOne(__CLASS__, 'id', 'uuid')->withDefault();
        }

        switch (true) {
            case $this->type === 'Food':
            case $this->type === 'Drink':
            case $this->type === 'Bottle':
                return $this->hasOne(Food::class, 'item_uuid', 'uuid')->withDefault();

                /**
                 * Armor
                 */
            case str_contains($this->type, 'Char_Armor'):
                return $this->hasOne(Armor::class, 'uuid', 'uuid')->withDefault();
                /**
                 * Char Clothing
                 */
            case str_contains($this->type, 'Char_Clothing'):
                return $this->hasOne(Clothes::class, 'uuid', 'uuid')->withDefault();

                /**
                 * Personal Weapons
                 */
            case str_contains($this->type, 'WeaponPersonal'):
                if ($this->sub_type === 'Grenade') {
                    return $this->hasOne(Grenade::class, 'item_uuid', 'uuid')->withDefault();
                }

                return $this->hasOne(PersonalWeapon::class, 'uuid', 'uuid')->withDefault();

            case $this->type === 'WeaponAttachment':
                switch ($this->sub_type) {
                    case 'IronSight':
                        return $this->hasOne(IronSight::class, 'item_uuid', 'uuid')->withDefault();

                    case 'Magazine':
                        return $this->hasOne(PersonalWeaponMagazine::class, 'item_uuid', 'uuid')->withDefault();

                    case 'Utility':
                    case 'Barrel':
                    case 'BottomAttachment':
                        return $this->hasOne(BarrelAttach::class, 'uuid', 'uuid')->withDefault();
                }
                break;

                /**
                 * Vehicles
                 */
            case str_contains($this->type, 'Vehicle'):
                return $this->hasOne(Vehicle::class, 'item_uuid', 'uuid')->withDefault();

                /**
                 * Ship Items
                 */
            case $this->type === 'Armor':
                return $this->hasOne(\App\Models\SC\ItemSpecification\Armor::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'Bomb':
                return $this->hasOne(Bomb::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'WeaponDefensive':
            case $this->type === 'WeaponGun':
            case str_contains($this->type, 'WeaponGun'):
                return $this->hasOne(VehicleWeapon::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'Missile':
            case $this->type === 'Torpedo':
                return $this->hasOne(Missile::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'EMP':
                return $this->hasOne(Emp::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'Cooler':
                return $this->hasOne(Cooler::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'QuantumDrive':
            case str_contains($this->type, 'QuantumDrive'):
                return $this->hasOne(QuantumDrive::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'PowerPlant':
                return $this->hasOne(PowerPlant::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'Shield':
            case str_contains($this->type, 'Shield'):
                return $this->hasOne(Shield::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'FuelTank':
            case $this->type === 'ExternalFuelTank':
            case $this->type === 'QuantumFuelTank':
                return $this->hasOne(FuelTank::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'QuantumInterdictionGenerator':
                return $this->hasOne(QuantumInterdictionGenerator::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'FuelIntake':
                return $this->hasOne(FuelIntake::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'MainThruster':
            case $this->type === 'ManneuverThruster':
                return $this->hasOne(Thruster::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'FlightController':
                return $this->hasOne(FlightController::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'SelfDestruct':
                return $this->hasOne(SelfDestruct::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'TowingBeam':
            case $this->type === 'TractorBeam':
                return $this->hasOne(TractorBeam::class, 'item_uuid', 'uuid')->withDefault();

                //
                //            case $this->type === 'Radar':
                //                return $this->hasOne(Radar::class, 'uuid', 'uuid')->withDefault();

            case $this->type === 'WeaponMining':
                return $this->hasOne(MiningLaser::class, 'item_uuid', 'uuid')->withDefault();

            case $this->type === 'MiningModifier':
                return $this->hasOne(MiningModule::class, 'uuid', 'uuid')->withDefault();

            case $this->type === 'SalvageModifier':
                return $this->hasOne(SalvageModifier::class, 'item_uuid', 'uuid')->withDefault();
        }

        return $this->hasOne(__CLASS__, 'id', 'uuid'); //NULL
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

    public function vehicle(): HasOne
    {
        return $this->hasOne(
            Vehicle::class,
            'item_uuid',
            'uuid'
        )->withDefault();
    }

    public function vehicleItem(): HasOne
    {
        return $this->hasOne(
            VehicleItem::class,
            'uuid',
            'uuid'
        )->withDefault();
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
        );
    }

    public function distortionData(): HasOne
    {
        return $this->hasOne(
            ItemDistortionData::class,
            'item_uuid',
            'uuid'
        );
    }

    public function powerData(): HasOne
    {
        return $this->hasOne(
            ItemPowerData::class,
            'item_uuid',
            'uuid'
        );
    }

    public function durabilityData(): HasOne
    {
        return $this->hasOne(
            ItemDurabilityData::class,
            'item_uuid',
            'uuid'
        );
    }

    public function descriptionData(): HasMany
    {
        return $this->hasMany(
            ItemDescriptionData::class,
            'item_uuid',
            'uuid'
        );
    }

    public function manufacturer(): HasOne
    {
        return $this->hasOne(Manufacturer::class, 'id', 'manufacturer_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Tag::class,
            'sc_item_tag',
            'item_id',
            'tag_id'
        )
            ->using(ItemTag::class);
    }

    public function defaultTags(): BelongsToMany
    {
        return $this->tags()->wherePivot('is_required_tag', false);
    }

    public function requiredTags(): BelongsToMany
    {
        return $this->tags()->wherePivot('is_required_tag', true);
    }

    public function interactions(): BelongsToMany
    {
        return $this->belongsToMany(
            Interaction::class,
            'sc_item_interaction',
            'item_id',
            'interaction_id'
        );
    }

    public function baseVariant(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_id', 'id');

    }

    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'base_id', 'id');
    }
}
