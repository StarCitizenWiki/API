<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemData extends Model
{
    use HasFactory;

    protected $table = 'game_item_data';

    protected $perPage = 50;

    protected $fillable = [
        'item_id',
        'game_version_id',
        'name',
        'class_name',
        'type',
        'sub_type',
        'classification',
        'size',
        'grade',
        'class',
        'manufacturer_id',
        'base_id',
        'data',
    ];

    protected $casts = [
        'size' => 'integer',
        'grade' => 'integer',
        'data' => AsCollection::class,
    ];

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        if ($code !== null) {
            return $query->whereHas('gameVersion', function (Builder $q) use ($code) {
                $q->whereRaw('LOWER(code) = ?', [strtolower($code)]);
            });
        }

        return $query->whereHas('gameVersion', function (Builder $q) {
            $q->where('is_default', true);
        });
    }

    public function getDescriptionDatum(string $name)
    {
        return $this->descriptionData()
            ->where('name', $name)
            ->first()?->value;
    }

    public function getDescriptionTypeAttribute()
    {
        return $this->getDescriptionDatum('Type');
    }

    public function getDescriptionManufacturerAttribute()
    {
        return $this->getDescriptionDatum('Manufacturer');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function descriptionData(): HasMany
    {
        return $this->hasMany(ItemDescriptionData::class, 'item_id');
    }

    public function baseVariant(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_id', 'id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'base_id', 'id');
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }

    public function entityTags(): BelongsToMany
    {
        return $this->belongsToMany(
            EntityTag::class,
            'game_item_data_entity_tag',
            'item_data_id',
            'entity_tag_id'
        );
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return match ($category) {
            'food' => $query->food(),
            'weapon-attachments' => $query->weaponAttachments(),
            'weapons' => $query->personalWeapons(),
            'clothes' => $query->clothes(),
            'armor' => $query->armor(),
            'vehicle-weapons' => $query->vehicleWeapons(),
            'vehicle-items' => $query->vehicleItems(),
            default => $query,
        };
    }

    public function scopeFood(Builder $query): Builder
    {
        return $query->whereIn('type', ['Food', 'Bottle', 'Drink']);
    }

    public function scopeWeaponAttachments(Builder $query): Builder
    {
        return $query->where('type', 'WeaponAttachment');
    }

    public function scopePersonalWeapons(Builder $query): Builder
    {
        return $query->where('type', 'WeaponPersonal');
    }

    public function scopeClothes(Builder $query): Builder
    {
        return $query
            ->where('classification', 'LIKE', 'FPS.Clothing.%')
            ->excludePlaceholderNames();
    }

    public function scopeArmor(Builder $query): Builder
    {
        return $query
            ->where('classification', 'LIKE', 'FPS.Armor.%')
            ->excludePlaceholderNames();
    }

    public function scopeVehicleWeapons(Builder $query): Builder
    {
        return $query->where('type', 'WeaponGun');
    }

    public function scopeVehicleItems(Builder $query): Builder
    {
        return $query
            ->where('class_name', 'NOT LIKE', '%test%')
            ->where('class_name', 'NOT LIKE', '%lowpoly%')
            ->where('class_name', 'NOT LIKE', '%dummy%')
            ->where('class_name', 'NOT LIKE', '%_mm')
            ->where('class_name', 'NOT LIKE', '%s%_idris_m')
            ->where('class_name', 'NOT LIKE', '%s%_turret')
            ->where('class_name', 'NOT LIKE', 'mrck_s05_orig_%')
            ->where('class_name', 'NOT LIKE', 'mrck_s05_behr_quad_s03_a')
            ->whereIn('type', [
                'Arm',
                'Battery',
                'BombLauncher',
                'Cooler',
                'EMP',
                'ExternalFuelTank',
                'FlightController',
                'FuelIntake',
                'FuelTank',
                'JumpDrive',
                'MainThruster',
                'ManneuverThruster',
                'MiningArm',
                'Missile',
                'MissileLauncher',
                'Mount',
                'Paints',
                'PowerPlant',
                'QuantumDrive',
                'QuantumFuelTank',
                'QuantumInterdictionGenerator',
                'Radar',
                'SalvageModifier',
                'SelfDestruct',
                'Shield',
                'ShieldController',
                'ToolArm',
                'TowingBeam',
                'TractorBeam',
                'Turret',
                'TurretBase',
                'UtilityTurret',
                'WeaponDefensive',
                'WeaponGun',
                'WeaponMount',
                'WeaponMining',
                'WheeledController',
            ]);
    }

    public function scopeExcludePlaceholderNames(Builder $query): Builder
    {
        return $query
            ->where($this->table.'.name', 'NOT LIKE', '%PLACEHOLDER%')
            ->where($this->table.'.name', 'NOT LIKE', '%Placeholder%')
            ->where($this->table.'.name', 'NOT LIKE', 'PH -%')
            ->where($this->table.'.name', 'NOT LIKE', '[PH]%')
            ->where($this->table.'.name', 'NOT LIKE', '%- name%');
    }

    public function scopeWithDescriptionValue(Builder $query, string $name, string $value): Builder
    {
        return $query->whereHas('descriptionData', function (Builder $builder) use ($name, $value) {
            $builder->where('name', $name)->where('value', $value);
        });
    }
}
